<?php

namespace App\Scorpio;

use App\Entity\Mission;
use App\Entity\MissionSensor;
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

    /** @var array */
    private $options;

    public function __construct(Client $client, RouterInterface $router, array $subscriptionManagerOptions)
    {
        $this->client = $client;
        $this->router = $router;
        $this->options = $subscriptionManagerOptions;
    }

    /**
     * Ensure that a subscription exists in the broker.
     */
    public function ensureSubscription(Mission $mission): ?array
    {
        $enabledMissionSensors = $mission->getMissionSensors()->filter(static function (MissionSensor $missionSensor) {
            return $missionSensor->getEnabled();
        });

        if ($enabledMissionSensors->isEmpty()) {
            $this->deleteSubscription($mission);

            return null;
        } else {
            $payload = $this->buildSubscriptionPayload($mission);

            return $this->client->ensureSubscription($payload);
        }
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

        // @see https://www.etsi.org/deliver/etsi_gs/CIM/001_099/009/01.01.01_60/gs_CIM009v010101p.pdf
        return [
            'type' => 'Subscription',
            'id' => $this->getSubscriptionId($mission),
            'description' => $description,
            'entities' => $mission->getMissionSensors()
                ->filter(static function (MissionSensor $missionSensor) {
                    return $missionSensor->getEnabled()
                        // We want to subscribe on stream observations.
                        && $missionSensor->getSensor()->getStreamObservationId();
                })
                ->map(static function (MissionSensor $missionSensor) {
                    return [
                        'type' => Client::ENTITY_TYPE_STREAM_OBSERVATION,
                        'id' => $missionSensor->getSensor()->getStreamObservationId(),
                    ];
                })
                ->toArray(),
            'notification' => [
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
        return 'urn:ngsi-ld:urban-data-missions:mission:'.$mission->getId();
    }
}
