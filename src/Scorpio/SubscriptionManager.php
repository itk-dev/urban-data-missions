<?php

namespace App\Scorpio;

use App\Entity\Experiment;
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

    public function ensureSubscription(Experiment $experiment)
    {
        $subscription = $this->getSubscription($experiment);

        return null === $subscription
            ? $this->createSubscription($experiment)
            : $this->updateSubscription($experiment);
    }

    public function getSubscription(Experiment $experiment): ?array
    {
        try {
            $response = $this->client->get('/ngsi-ld/v1/subscriptions/'.$this->getSubscriptionId($experiment));

            return $response->toArray();
        } catch (ClientException $exception) {
            $this->debug(sprintf('Client exception: %s', $exception->getMessage()));
        }

        return null;
    }

    public function createSubscription(Experiment $experiment): ?string
    {
        $this->debug(sprintf('Creating subscription for experiment %s', $experiment->getId()));
        if (null === $experiment->getId() || $experiment->getSensors()->isEmpty()) {
            return null;
        }

        $payload = $this->buildSubscriptionPayload($experiment);
        $payload['id'] = $this->getSubscriptionId($experiment);

        $response = $this->client->post('/ngsi-ld/v1/subscriptions/', [
            'json' => $payload,
        ]);

        if (Response::HTTP_CREATED === $response->getStatusCode()) {
            return $response->getContent();
        }

        $this->error(sprintf('Error creating subscription for experiment %s', $experiment->getId()), [
            'experiment' => $experiment,
            'response' => [
                'status_code' => $response->getStatusCode(),
                'content' => $response->getContent(),
            ],
        ]);

        return null;
    }

    public function updateSubscription(Experiment $experiment): bool
    {
        $this->debug(sprintf('Updating subscription for experiment %s', $experiment->getId()));

        $payload = $this->buildSubscriptionPayload($experiment);

        $subscriptionId = $this->getSubscriptionId($experiment);
        $response = $this->client->patch('/ngsi-ld/v1/subscriptions/'.urlencode($subscriptionId),
            [
                'json' => $payload,
            ]
        );

        if (Response::HTTP_NO_CONTENT === $response->getStatusCode()) {
            return true;
        }

        $this->error(sprintf('Error updating subscription for experiment %s: status code: %d', $experiment->getId(), $response->getStatusCode()), [
            'experiment' => $experiment,
            'response' => [
                'status_code' => $response->getStatusCode(),
                // 'content' => $response->getContent(),
            ],
        ]);

        return false;
    }

    private function buildSubscriptionPayload(Experiment $experiment): array
    {
        $endpoint = $this->router->generate('experiment_subscription_notify', [
            'id' => $experiment->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $description = sprintf('Subscription for experiment %s', $experiment->getId());

        return [
            'type' => 'Subscription',
            'description' => $description,
            'entities' => $experiment->getSensors()->map(static function (Sensor $sensor) {
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

    private function getSubscriptionId(Experiment $experiment): string
    {
        return 'urn:ngsi-ld:Subscription:experiment:'.$experiment->getId();
    }
}
