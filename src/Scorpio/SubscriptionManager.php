<?php

namespace App\Scorpio;

use App\Entity\Experiment;
use App\Traits\LoggerTrait;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class SubscriptionManager implements LoggerAwareInterface
{
    use LoggerTrait;

    /** @var Client */
    private $client;

    /** @var RouterInterface */
    private $router;

    public function __construct(Client $client, RouterInterface $router)
    {
        $this->client = $client;
        $this->router = $router;
    }

    public function createSubscription(Experiment $experiment)
    {
        $subscriptionId = 'urn:ngsi-ld:Subscription:experiment:'.$experiment->getId();

        $endpoint = $this->router->generate('experiment_subscription_notify', [
            'id' => $experiment->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $payload = [
            'type' => 'Subscription',
            'id' => $subscriptionId,
            'description' => $subscriptionId,
            'entities' => [
                array_map(static function ($sensor) {
                    return [
                        // Apparently, using 'id' => $sensor breaks something ...
                        'idPattern' => $sensor,
                        'type' => 'https://uri.fiware.org/ns/data-models#temperature',
                    ];
                }, $experiment->getSensors()),
            ],
//                'watchedAttributes' => ['https://uri.fiware.org/ns/data-models#temperature'],
            'notification' => [
//                    'attributes' => ['https://uri.fiware.org/ns/data-models#temperature'],
                'format' => 'normalized',
                'endpoint' => [
                    'uri' => $endpoint,
                    'accept' => 'application/json',
                ],
            ],
        ];

        $this->info(sprintf('%s: %s', __METHOD__, $endpoint));
        $response = $this->client->post('/ngsi-ld/v1/subscriptions/', [
            'json' => $payload,
        ]);

        return Response::HTTP_CREATED === $response->getStatusCode() ? $response->getContent() : null;
    }

    public function updateSubscription(Experiment $experiment)
    {
        $this->info(__METHOD__);
//        throw new \RuntimeException(__METHOD__);
    }
}
