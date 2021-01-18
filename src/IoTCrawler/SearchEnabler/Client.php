<?php

namespace App\IoTCrawler\SearchEnabler;

use GraphQL\Client as GraphQLClient;
use GraphQL\Query;
use GraphQL\RawObject;
use GraphQL\Results;
use GraphQL\Variable;
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

    public function runQuery(Query $query, bool $resultsAsArray = false, array $variables = []): Results
    {
        $client = new GraphQLClient($this->options['url'].'/graphql');

        return $client->runQuery($query, $resultsAsArray, $variables);
    }

    public function getStreamsBySensorId(string $sensorId)
    {
        $query = (new Query('streams'))
            ->setVariables([
                new Variable('sensorId', 'String', true),
            ])
            ->setArguments([
                'generatedBy' => new RawObject('{id: $sensorId}'),
            ])
            ->setSelectionSet([
                'id',
                (new Query('generatedBy'))
                    ->setSelectionSet([
                        'id',
                        (new Query('observes'))
                            ->setSelectionSet(['id']),
                    ]),
            ]);

        return $this->getData($query, ['sensorId' => $sensorId]);
    }

    public function getSensorsByObservesType(string $alternativeType)
    {
        $query = (new Query('sensors'))
            ->setVariables([
                new Variable('alternativeType', 'String', true),
            ])
            ->setArguments([
                'observes' => new RawObject('{alternativeType: $alternativeType}'),
            ])
            ->setSelectionSet([
                'id',
                (new Query('observes'))
                    ->setSelectionSet([
                        'id',
                        'alternativeType',
                    ]),
            ]);

        return $this->getData($query, ['alternativeType' => $alternativeType]);
    }

    public function getObservableProperties()
    {
        $query = (new Query('observableProperties'))
            ->setSelectionSet([
                'id',
                'alternativeType',
            ]);

        return $this->getData($query);
    }

    private function getData(Query $query, array $variables = [])
    {
        return $this->runQuery($query, false, $variables)->getData();
    }

    private function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['url']);
    }
}
