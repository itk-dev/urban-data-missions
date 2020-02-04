<?php

namespace App\Scorpio;

use Symfony\Component\HttpFoundation\Response;

class SensorManager
{
    /** @var Client */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function getSensors()
    {
        $queries = [
            [
                'type' => 'https://uri.fiware.org/ns/data-models#humidity',
            ],
            [
                'type' => 'https://uri.fiware.org/ns/data-models#temperature',
            ],
        ];

        $result = [];
        foreach ($queries as $query) {
            $response = $this->client->query($query);
            if (Response::HTTP_OK === $response->getStatusCode()) {
                $result[] = $response->toArray();
            }
        }

        return array_column(array_merge(...$result), 'id');
    }
}
