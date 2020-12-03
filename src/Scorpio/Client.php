<?php

namespace App\Scorpio;

use App\Traits\LoggerTrait;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class Client implements LoggerAwareInterface
{
    public const ENTITY_TYPE_PLATFORM = 'http://www.w3.org/ns/sosa/Platform';
    public const ENTITY_TYPE_SENSOR = 'http://www.w3.org/ns/sosa/Sensor';
    public const ENTITY_TYPE_STREAM = 'http://purl.org/iot/ontology/iot-stream#IotStream';
    public const ENTITY_TYPE_STREAM_OBSERVATION = 'http://purl.org/iot/ontology/iot-stream#StreamObservation';

    public const ENTITY_ATTRIBUTE_GENERATED_BY = 'http://purl.org/iot/ontology/iot-stream#generatedBy';
    public const ENTITY_ATTRIBUTE_BELONGS_TO = 'http://purl.org/iot/ontology/iot-stream#belongsTo';
    public const ENTITY_ATTRIBUTE_BELONGS_TO_SHORT = 'belongsTo';
    public const ENTITY_ATTRIBUTE_HAS_SIMPLE_RESULT = 'http://www.w3.org/ns/sosa/hasSimpleResult';
    public const ENTITY_ATTRIBUTE_RESULT_TIME = 'http://www.w3.org/ns/sosa/resultTime';

    public static function getTypedValue($value)
    {
        [$value, $type] = explode('^^', $value);
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

    public static function getEntityTypes(): array
    {
        return [
            self::ENTITY_TYPE_PLATFORM,
            self::ENTITY_TYPE_SENSOR,
            self::ENTITY_TYPE_STREAM,
            self::ENTITY_TYPE_STREAM_OBSERVATION,
        ];
    }

    use LoggerTrait;

    /** @var HttpClientInterface */
    private $httpClient;

    /** @var string */
    private $ngsiLdBrokerUrl;

    /** @var array */
    private $ngsiLdBrokerOptions;

    public function __construct(HttpClientInterface $httpClient, string $ngsiLdBrokerUrl, array $ngsiLdBrokerOptions)
    {
        $this->httpClient = $httpClient;
        $this->ngsiLdBrokerUrl = $ngsiLdBrokerUrl;
        $this->ngsiLdBrokerOptions = $ngsiLdBrokerOptions;
    }

    public function ensureEntity(array $data): array
    {
        $id = $data['id'];
        $response = $this->post('/ngsi-ld/v1/entities/', [
            'json' => $data,
        ]);

        if (Response::HTTP_CONFLICT === $response->getStatusCode()) {
            unset($data['id'], $data['type']);
            $response = $this->patch('/ngsi-ld/v1/entities/'.urlencode($id).'/attrs', [
                'json' => $data,
            ]);
        }

        return $this->getEntity($id);
    }

    public function getEntity(string $id): ?array
    {
        $path = '/ngsi-ld/v1/entities/'.urlencode($id);

        $response = $this->get($path);

        return Response::HTTP_OK === $response->getStatusCode() ? $response->toArray() : null;
    }

    public function getEntities(array $query)
    {
        $path = '/ngsi-ld/v1/entities/';

        return $this->get($path, [
            'query' => $query,
        ]);
    }

    public function ensureSubscription(array $data): ?array
    {
        $id = $data['id'];
        $response = $this->post('/ngsi-ld/v1/subscriptions/', [
            'json' => $data,
        ]);

        if (Response::HTTP_CONFLICT === $response->getStatusCode()) {
            unset($data['id'], $data['type']);
            $response = $this->patch('/ngsi-ld/v1/subscriptions/'.urlencode($id), [
                'json' => $data,
            ]);
        }

        return $this->getSubscription($id);
    }

    public function getSubscription(string $id): ?array
    {
        $path = '/ngsi-ld/v1/subscriptions/'.urlencode($id);

        $response = $this->get($path);

        return Response::HTTP_OK === $response->getStatusCode() ? $response->toArray() : null;
    }

    public function get(string $path, array $options = []): ResponseInterface
    {
        return $this->request('GET', $path, $options);
    }

    public function getUrl(string $path, array $options = []): string
    {
        return $this->ngsiLdBrokerUrl.$path;
    }

    public function post(string $path, array $options = []): ResponseInterface
    {
        return $this->request('POST', $path, $options);
    }

    public function patch(string $path, array $options = []): ResponseInterface
    {
        return $this->request('PATCH', $path, $options);
    }

    public function delete(string $path, array $options = []): ResponseInterface
    {
        return $this->request('DELETE', $path, $options);
    }

    protected function request(string $method, string $path, array $options = []): ResponseInterface
    {
        if (in_array($method, ['POST', 'PATCH'])) {
            if (!isset($options['headers']['content-type'])) {
                $options['headers']['content-type'] = 'application/ld+json';
            }
        }

        if ($this->ngsiLdBrokerOptions['no_verify'] ?? false) {
            $options += [
                'verify_peer' => false,
                'verify_host' => false,
            ];
        }

        return $this->httpClient->request($method, $path, $options + [
            'base_uri' => $this->ngsiLdBrokerUrl,
        ]);
    }
}
