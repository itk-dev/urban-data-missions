<?php

namespace App\Broker;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class SensorManager
{
    /** @var HttpClientInterface */
    private $httpClient;

    /** @var string */
    private $ngsiLdBrokerUrl;

    public function __construct(HttpClientInterface $httpClient, string $ngsiLdBrokerUrl)
    {
        $this->httpClient = $httpClient;
        $this->ngsiLdBrokerUrl = $ngsiLdBrokerUrl;
    }

    public function getSensors()
    {
        $response = $this->httpClient->request('GET', '/ngsi-ld/v1/entities/', [
            'base_uri' => $this->ngsiLdBrokerUrl,
            'query' => ['type' => 'https://uri.fiware.org/ns/data-models#AirQualityObserved'],
        ]);

        return array_column($response->toArray(), 'id');
    }
}
