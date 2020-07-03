<?php

namespace App\EventListener;

use App\Entity\Mission;
use App\Entity\MissionLogEntry;
use App\Entity\MissionSensor;
use App\Scorpio\SubscriptionManager;
use App\Traits\LoggerTrait;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Psr\Log\LoggerAwareInterface;

class MissionListener implements LoggerAwareInterface
{
    use LoggerTrait;

    /** @var SubscriptionManager */
    private $subscriptionManager;

    public function __construct(SubscriptionManager $subscriptionManager)
    {
        $this->subscriptionManager = $subscriptionManager;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        $entity = $args->getEntity();

        if ($entity instanceof Mission) {
            $logEntry = (new MissionLogEntry())
                ->setMission($entity)
                ->setLoggedAt($entity->getCreatedAt())
                ->setType(MissionLogEntry::TYPE_SYSTEM)
                ->setContent('Mission started');

            $em->persist($logEntry);
        }
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        $entities = array_merge(
            $uow->getScheduledEntityInsertions(),
            $uow->getScheduledEntityUpdates()
        );

        $missions = array_filter($entities, static function ($entity) {
            return $entity instanceof Mission;
        });
        $affectedMissions = array_map(
            static function (MissionSensor $missionSensor) {
                return $missionSensor->getMission();
            },
            array_filter($entities, static function ($entity) {
                return $entity instanceof MissionSensor;
            })
        );

        $allMissions = array_unique(array_merge($missions, $affectedMissions));

        foreach ($allMissions as $mission) {
            $this->ensureSubscription($mission);
            $uow->persist($mission);

            $metadata = $em->getClassMetadata(get_class($mission));
            $uow->recomputeSingleEntityChangeSet($metadata, $mission);
        }
    }

    private function ensureSubscription(Mission $mission)
    {
        if (null === $mission->getId()) {
            // @TODO: Handle creating subscription on new mission (before persisting)?
            return;
        }
        $subscription = $this->subscriptionManager->ensureSubscription($mission);

        $mission->setSubscription($subscription);
    }
}
