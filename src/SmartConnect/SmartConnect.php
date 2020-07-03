<?php

namespace App\SmartConnect;

use App\Scorpio\Client;
use DateTimeInterface;
use Symfony\Component\HttpFoundation\Response;

class SmartConnect
{
    /** @var Client */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Get stream observation for a sensor in a platform if any.
     */
    public function getObservation(string $platform, string $sensor): ?array
    {
        $platformId = $this->getId($platform, 'platform');
        $sensorId = $this->getId($sensor, $platform);

        $platform = $this->client->getEntity($platformId);
        if (null === $platform) {
            return null;
        }

        $sensor = $this->client->getEntity($sensorId);
        if (null === $sensor) {
            return null;
        }

        // Get stream for the sensor.
        $response = $this->client->getEntities([
            'type' => 'http://purl.org/iot/ontology/iot-stream#IotStream',
            'q' => 'http://purl.org/iot/ontology/iot-stream#generatedBy=='.$sensorId,
        ]);
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            return null;
        }
        $streams = $response->toArray();
        $stream = $streams[0] ?? null;
        if (null === $stream) {
            return null;
        }

        $response = $this->client->getEntities([
            'type' => 'http://purl.org/iot/ontology/iot-stream#StreamObservation',
            'q' => 'http://purl.org/iot/ontology/iot-stream#belongsTo=='.$stream['id'],
        ]);
        $observations = $response->toArray();

        return $observations[0] ?? null;
    }

    public function getValue(array $observation)
    {
        if (!isset($observation['http://www.w3.org/ns/sosa/hasSimpleResult']['value'])) {
            return null;
        }

        [$value, $type] = explode('^^', $observation['http://www.w3.org/ns/sosa/hasSimpleResult']['value']);
        [$_, $type] = explode('#', $type);

        switch ($type) {
            case 'integer':
                return (int) $value;
            case 'float':
                return (float) $value;
            default:
                return $value;
        }
    }

    /**
     * Create stream observation for a sensor in a platform.
     *
     * @param $value
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function createObservation(string $platform, string $sensor, $value, DateTimeInterface $time): array
    {
        $platformId = $this->getId($platform, 'platform');
        $sensorId = $this->getId($sensor, $platform);

        $data = $this->getData('platform', [
            'id' => $platformId,
            'http://empty-ns/devices/name' => [
                'value' => 'Platform '.$platform,
            ], ]);
        $platform = $this->client->ensureEntity($data);

        $data = $this->getData('sensor', [
            'id' => $sensorId,
            'http://empty-ns/devices/name' => [
                'value' => 'Sensor '.$sensor,
            ],
            'http://www.w3.org/ns/sosa/isHostedBy' => [
                'object' => $platformId,
            ], ]);
        $sensor = $this->client->ensureEntity($data);

        // Check if stream for sensor exists.
        $response = $this->client->getEntities([
            'type' => 'http://purl.org/iot/ontology/iot-stream#IotStream',
            'q' => 'http://purl.org/iot/ontology/iot-stream#generatedBy=='.$sensorId,
        ]);
        $streams = $response->toArray();
        $stream = $streams[0] ?? null;
        if (null === $stream) {
            $streamId = $this->getId($sensor['id'], 'stream');
            $data = $this->getData('stream', [
                'id' => $streamId,
                'http://purl.org/iot/ontology/iot-stream#generatedBy' => [
                    'object' => $sensor['id'],
                ],
            ]);
            unset($data['http://www.agtinternational.com/ontologies/RichIoTStream/property']);
            $stream = $this->client->ensureEntity($data);
        }

        // Check if observation for stream exists.
        $response = $this->client->getEntities([
            'type' => 'http://purl.org/iot/ontology/iot-stream#StreamObservation',
            'q' => 'http://purl.org/iot/ontology/iot-stream#belongsTo=='.$stream['id'],
        ]);
        $observations = $response->toArray();
        $observation = $observations[0] ?? null;
        if (null === $observation) {
            $observationId = $this->getId($stream['id'], 'observation');
            $data = $this->getData('observation', [
                'id' => $observationId,
                'http://purl.org/iot/ontology/iot-stream#belongsTo' => [
                    'object' => $stream['id'],
                ],
                'http://www.w3.org/ns/sosa/hasSimpleResult' => [
                    'value' => $this->getValueAndType($value),
                ],
                'http://www.w3.org/ns/sosa/resultTime' => [
                    'value' => $time->format(DateTimeInterface::ATOM),
                ],
            ]);
            $observation = $this->client->ensureEntity($data);
        }
        // Set observation data.
        $data = $this->getData('observation', [
            'id' => $observation['id'],
            'http://purl.org/iot/ontology/iot-stream#belongsTo' => [
                'object' => $stream['id'],
            ],
            'http://www.w3.org/ns/sosa/hasSimpleResult' => [
                'value' => $this->getValueAndType($value),
            ],
            'http://www.w3.org/ns/sosa/resultTime' => [
                'value' => $time->format(DateTimeInterface::ATOM),
            ],
        ]);

        return $this->client->ensureEntity($data);
    }

    private function getValueAndType($value)
    {
        return $value.'^^http://www.w3.org/2001/XMLSchema#'.gettype($value);
    }

    private function getData(string $name, array $defaults = []): array
    {
        $data = json_decode(file_get_contents(__DIR__.'/templates/'.$name.'.json'), true);

        return array_replace_recursive($data, $defaults);
    }

    private const ID_PREFIX = 'urn:ngsi-ld:';

    private function getId(string $id, string $prefix = null): string
    {
        if (0 === strpos($id, self::ID_PREFIX)) {
            $id = substr($id, strlen(self::ID_PREFIX));
        }

        return self::ID_PREFIX.(null === $prefix ? '' : $prefix.':').$id;
    }
}
