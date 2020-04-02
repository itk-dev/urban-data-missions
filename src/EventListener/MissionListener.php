<?php

namespace App\EventListener;

use App\Entity\Mission;
use App\Entity\MissionLogEntry;
use App\Scorpio\SubscriptionManager;
use App\Traits\LoggerTrait;
use DateTime;
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

    public function prePersist(LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        $entity = $args->getEntity();

        if ($entity instanceof Mission) {
            $logEntry = (new MissionLogEntry())
                ->setMission($entity)
                ->setLoggedAt(new DateTime())
                ->setType(MissionLogEntry::TYPE_SYSTEM)
                ->setContent('Mission started');

            $em->persist($logEntry);
        }
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        $newMissions = array_filter($uow->getScheduledEntityInsertions(), static function ($entity) {
            return $entity instanceof Mission;
        });
        $updatedMissions = array_filter($uow->getScheduledEntityUpdates(), static function ($entity) {
            return $entity instanceof Mission;
        });
        $allMissions = array_merge($newMissions, $updatedMissions);

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
        $this->subscriptionManager->ensureSubscription($mission);
    }
}
