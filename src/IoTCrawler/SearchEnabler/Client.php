<?php

namespace App\IoTCrawler\SearchEnabler;

use GraphQL\Query;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Client
{
    /** @var HttpClientInterface */
    private $httpClient;

    /** @var array */
    private $options;

    public function __construct(HttpClientInterface $httpClient, array $searchEnablerOptions)
    {
        $this->httpClient = $httpClient;
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($searchEnablerOptions);
    }

    public function query(Query $query, array $options = []): array
    {
        $response = $this->httpClient->request('POST', 'graphql', $options + [
                'json' => [
                    'query' => (string) $query,
                ],
                'base_uri' => $this->options['url'],
            ]);

        return $response->toArray();
    }

    private function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['url']);
    }
}
