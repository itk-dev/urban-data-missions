<?php

namespace App\Scorpio;

use App\Traits\LoggerTrait;
use DateTimeImmutable;
use DateTimeInterface;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\HttpFoundation\Response;

class MeasurementManager implements LoggerAwareInterface
{
    use LoggerTrait;

    /** @var Client */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function getMeasurementUrl(string $sensor, string $type)
    {
        return $this->client->getUrl('/ngsi-ld/v1/entities/'.urlencode($this->getEntityId($sensor, $type)));
    }

    private function getEntityId(string $sensor, string $type): string
    {
        return $sensor.':'.$type;
    }

    public function getMeasurement(string $sensor, string $type)
    {
        $path = '/ngsi-ld/v1/entities/'.urlencode($this->getEntityId($sensor, $type));
        $response = $this->client->get($path);

        return Response::HTTP_OK === $response->getStatusCode() ? $response->toArray() : null;
    }

    public function createMeasurement(string $sensor, string $type, $value, ?DateTimeInterface $measuredAt = null)
    {
        $path = '/ngsi-ld/v1/entities/';
        $payload = [
            'id' => $this->getEntityId($sensor, $type),
            'type' => $type,
            'dateObserved' => [
                'type' => 'Property',
                'value' => [
                    '@type' => 'DateTime',
                    '@value' => ($measuredAt ?? new DateTimeImmutable())->format(DateTimeInterface::ATOM),
                ],
            ],
            $type => [
                'type' => 'Property',
                'value' => $value,
            ],
            '@context' => [
                'https://schema.lab.fiware.org/ld/context',
            ],
        ];

        $this->info(sprintf('Creating measurement %s for %s', $type, $sensor));
        $response = $this->client->post($path, [
            'json' => $payload,
        ]);

        return Response::HTTP_CREATED === $response->getStatusCode();
    }

    public function updateMeasurement(string $sensor, string $type, $value, ?DateTimeInterface $measuredAt = null)
    {
        $path = '/ngsi-ld/v1/entities/'.urlencode($this->getEntityId($sensor, $type)).'/attrs';
        $payload = [
            'dateObserved' => [
                'type' => 'Property',
                'value' => [
                    '@type' => 'DateTime',
                    '@value' => $measuredAt->format(DateTimeInterface::ATOM),
                ],
            ],
            $type => [
                'type' => 'Property',
                'value' => $value,
            ],
            '@context' => [
                'https://schema.lab.fiware.org/ld/context',
            ],
        ];

        $this->info(sprintf('Updating measurement %s.%s; value: %f; measured at %s', $sensor, $type, $value, $measuredAt->format(DateTimeImmutable::ATOM)));
        $response = $this->client->patch($path, [
            'json' => $payload,
        ]);

        return Response::HTTP_NO_CONTENT === $response->getStatusCode();
    }

    public function deleteMeasurement(string $sensor, string $type)
    {
        $path = '/ngsi-ld/v1/entities/'.urlencode($this->getEntityId($sensor, $type));

        $this->info('Deleting measurement');
        $response = $this->client->delete($path);

        return Response::HTTP_NO_CONTENT === $response->getStatusCode();
    }

    public function getValue(array $measurement)
    {
        // @TODO Make this hack more robust!
        return $measurement[$measurement['type'] ?? null]['value'] ?? null;
    }
}
