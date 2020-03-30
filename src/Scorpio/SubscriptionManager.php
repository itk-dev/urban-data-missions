<?php

namespace App\Scorpio;

use App\Entity\Mission;
use App\Entity\Sensor;
use App\Traits\LoggerTrait;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
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

    public function ensureSubscription(Mission $mission)
    {
        $subscription = $this->getSubscription($mission);

        return null === $subscription
            ? $this->createSubscription($mission)
            : $this->updateSubscription($mission);
    }

    public function getSubscription(Mission $mission): ?array
    {
        try {
            $response = $this->client->get('/ngsi-ld/v1/subscriptions/'.$this->getSubscriptionId($mission));

            return $response->toArray();
        } catch (ClientException $exception) {
            $this->debug(sprintf('Client exception: %s', $exception->getMessage()));
        }

        return null;
    }

    public function createSubscription(Mission $mission): ?string
    {
        $this->debug(sprintf('Creating subscription for mission %s', $mission->getId()));
        if (null === $mission->getId() || $mission->getSensors()->isEmpty()) {
            return null;
        }

        $payload = $this->buildSubscriptionPayload($mission);
        $payload['id'] = $this->getSubscriptionId($mission);

        $response = $this->client->post('/ngsi-ld/v1/subscriptions/', [
            'json' => $payload,
        ]);

        if (Response::HTTP_CREATED === $response->getStatusCode()) {
            return $response->getContent();
        }

        $this->error(sprintf('Error creating subscription for mission %s', $mission->getId()), [
            'mission' => $mission,
            'response' => [
                'status_code' => $response->getStatusCode(),
                'content' => $response->getContent(),
            ],
        ]);

        return null;
    }

    public function updateSubscription(Mission $mission): bool
    {
        $this->debug(sprintf('Updating subscription for mission %s', $mission->getId()));

        $payload = $this->buildSubscriptionPayload($mission);

        $subscriptionId = $this->getSubscriptionId($mission);
        $response = $this->client->patch('/ngsi-ld/v1/subscriptions/'.urlencode($subscriptionId),
            [
                'json' => $payload,
            ]
        );

        if (Response::HTTP_NO_CONTENT === $response->getStatusCode()) {
            return true;
        }

        $this->error(sprintf('Error updating subscription for mission %s: status code: %d', $mission->getId(), $response->getStatusCode()), [
            'mission' => $mission,
            'response' => [
                'status_code' => $response->getStatusCode(),
                // 'content' => $response->getContent(),
            ],
        ]);

        return false;
    }

    public function deleteSubscription(Mission $mission)
    {
        $description = sprintf('Deleting subscription for mission %s', $mission->getId());
        $subscriptionId = $this->getSubscriptionId($mission);
        $response = $this->client->delete('/ngsi-ld/v1/subscriptions/'.urlencode($subscriptionId));

        $description = sprintf('Deleting subscription for mission %s: response: %d', $mission->getId(), $response->getStatusCode());

        return Response::HTTP_NO_CONTENT === $response->getStatusCode();
    }

    private function buildSubscriptionPayload(Mission $mission): array
    {
        $endpoint = $this->router->generate('mission_subscription_notify', [
            'id' => $mission->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $description = sprintf('Subscription for mission %s', $mission->getId());

        return [
            'type' => 'Subscription',
            'description' => $description,
            'entities' => $mission->getSensors()->map(static function (Sensor $sensor) {
                return [
                    // Apparently, using 'id' => $sensor breaks something ...
                    'idPattern' => $sensor->getId(),
                    'type' => $sensor->getType(),
                ];
            })->toArray(),
            // 'watchedAttributes' => ['https://uri.fiware.org/ns/data-models#temperature'],
            'notification' => [
                // 'attributes' => ['https://uri.fiware.org/ns/data-models#temperature'],
                'format' => 'normalized',
                'endpoint' => [
                    'uri' => $endpoint,
                    'accept' => 'application/json',
                ],
            ],
        ];
    }

    private function getSubscriptionId(Mission $mission): string
    {
        return 'urn:ngsi-ld:Subscription:mission:'.$mission->getId();
    }
}
