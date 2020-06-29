<?php

namespace App\Scorpio;

use App\Entity\Mission;
use App\Entity\MissionSensor;
use App\Traits\LoggerTrait;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

class SubscriptionManager implements LoggerAwareInterface
{
    use LoggerTrait;

    /** @var Client */
    private $client;

    /** @var RouterInterface */
    private $router;

    /** @var array */
    private $options;

    public function __construct(Client $client, RouterInterface $router, array $subscriptionManagerOptions)
    {
        $this->client = $client;
        $this->router = $router;
        $this->options = $subscriptionManagerOptions;
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
        } catch (ExceptionInterface $exception) {
            $this->debug(sprintf('Client exception: %s', $exception->getMessage()));
        }

        return null;
    }

    public function createSubscription(Mission $mission): ?string
    {
        $this->debug(sprintf('Creating subscription for mission %s', $mission->getId()));
        if (null === $mission->getId() || $mission->getMissionSensors()->isEmpty()) {
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
        $subscriptionId = $this->getSubscriptionId($mission);
        $response = $this->client->delete('/ngsi-ld/v1/subscriptions/'.urlencode($subscriptionId));

        return Response::HTTP_NO_CONTENT === $response->getStatusCode();
    }

    private function buildSubscriptionPayload(Mission $mission): array
    {
        // Make sure that notifications are sent correctly inside docker world.
        if (isset($this->options['router']['context'])) {
            $context = $this->router->getContext();
            if (isset($this->options['router']['context']['schema'])) {
                $context->setScheme($this->options['router']['context']['schema']);
            }
            if (isset($this->options['router']['context']['host'])) {
                $context->setHost($this->options['router']['context']['host']);
            }
            if (!empty($this->options['router']['context']['port'])) {
                if ('https' === $context->getScheme()) {
                    $context->setHttpsPort($this->options['router']['context']['port']);
                } else {
                    $context->setHttpPort($this->options['router']['context']['port']);
                }
            }
            if (isset($this->options['router']['context']['base_url'])) {
                $context->setBaseUrl($this->options['router']['context']['base_url']);
            }
        }

        $endpoint = $this->router->generate('mission_subscription_notify', [
            'id' => $mission->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $description = sprintf('Subscription for mission %s', $mission->getId());

        return [
            'type' => 'Subscription',
            'description' => $description,
            'entities' => $mission->getMissionSensors()->map(static function (MissionSensor $missionSensor) {
                $sensor = $missionSensor->getSensor();

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
