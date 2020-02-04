<?php

namespace App\Scorpio;

use App\Traits\LoggerTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class Client implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    use LoggerTrait;

    /** @var HttpClientInterface */
    private $httpClient;

    /** @var string */
    private $ngsiLdBrokerUrl;

    public function __construct(HttpClientInterface $httpClient, string $ngsiLdBrokerUrl)
    {
        $this->httpClient = $httpClient;
        $this->ngsiLdBrokerUrl = $ngsiLdBrokerUrl;
    }

    public function query(array $query)
    {
        $path = '/ngsi-ld/v1/entities/';

        return $this->get($path, [
            'query' => $query,
        ]);
    }

    public function get(string $path, array $options = []): ResponseInterface
    {
        return $this->request('GET', $path, $options);
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

        return $this->httpClient->request($method, $path, $options + [
            'base_uri' => $this->ngsiLdBrokerUrl,
        ]);
    }
}
