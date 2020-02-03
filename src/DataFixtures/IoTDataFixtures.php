<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class IoTDataFixtures extends Fixture implements FixtureGroupInterface
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

    public function load(ObjectManager $manager)
    {
        $output = new ConsoleOutput();

        $payload = json_decode('
{
    "id": "urn:ngsi-ld:testunit:123",
    "type": "AirQualityObserved",
    "dateObserved": {
    "type": "Property",
        "value": {
        "@type": "DateTime",
            "@value": "2018-08-07T12:00:00Z"
        }
    },
    "NO2": {
    "type": "Property",
        "value": 22,
        "unitCode": "GP",
        "accuracy": {
        "type": "Property",
            "value": 0.95
        }
    },
    "refPointOfInterest": {
    "type": "Relationship",
        "object": "urn:ngsi-ld:PointOfInterest:RZ:MainSquare"
    },
    "@context": [
    "https://schema.lab.fiware.org/ld/context"
]
}
', true);
        $url = $this->ngsiLdBrokerUrl.'/ngsi-ld/v1/entities/'.$payload['id'];
        $request = $this->httpClient->request('DELETE', $url, [
            'headers' => [
                'content-type' => 'application/ld+json',
            ],
        ]);

        $output->writeln([
                    $payload['id'],
                    $url,
                    $request->getStatusCode(),
                ]);

        $url = $this->ngsiLdBrokerUrl.'/ngsi-ld/v1/entities/';
        $request = $this->httpClient->request('POST', $url, [
                'headers' => [
                    'content-type' => 'application/ld+json',
                ],
                'json' => $payload,
            ]);

        $output->writeln([
                    $payload['id'],
                    $url,
                    $request->getStatusCode(),
                ]);

        $url = $this->ngsiLdBrokerUrl.'/ngsi-ld/v1/entities/'.$payload['id'].'/attrs';
        $request = $this->httpClient->request('PATCH', $url, [
                'headers' => [
                    'content-type' => 'application/ld+json',
                ],
                                'json' => json_decode('
{
    "dateObserved": {
        "type": "Property",
        "value": {
            "@type": "DateTime",
            "@value": "2018-08-07T12:00:02Z"
        }
    },
    "NO2": {
        "type": "Property",
        "value": 22,
        "unitCode": "GP",
        "accuracy": {
            "type": "Property",
            "value": 0.89
        }
    },
    "@context": [
        "https://schema.lab.fiware.org/ld/context"
    ]
}
', true),
                ]);

        $output->writeln([
                    $payload['id'],
                    $url,
                    $request->getStatusCode(),
                ]);

        $output->writeln($this->ngsiLdBrokerUrl.'/ngsi-ld/v1/entities/'.$payload['id']);
    }

    public static function getGroups(): array
    {
        return ['iot-data'];
    }
}
