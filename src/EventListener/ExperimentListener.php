<?php

namespace App\EventListener;

use App\Entity\Experiment;
use App\Scorpio\SubscriptionManager;
use App\Traits\LoggerTrait;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Psr\Log\LoggerAwareInterface;

class ExperimentListener implements LoggerAwareInterface
{
    use LoggerTrait;

    /** @var SubscriptionManager */
    private $subscriptionManager;

    public function __construct(SubscriptionManager $subscriptionManager)
    {
        $this->subscriptionManager = $subscriptionManager;
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        $entities = array_merge(
            $uow->getScheduledEntityInsertions(),
            $uow->getScheduledEntityUpdates()
        );

        foreach ($entities as $entity) {
            if ($entity instanceof Experiment) {
                $this->ensureSubscription($entity);
            }
        }
    }

    private function ensureSubscription(Experiment $experiment)
    {
        if (null === $experiment->getId()) {
            // @TODO: Handle creating subscription on new experiment (before persisting)?
            return;
        }
        $subscription = $experiment->getSubscription();
        if (null === $subscription) {
            $this->debug(sprintf('Creating subscription for experiment %s', $experiment->getId()));
            $subscription = $this->subscriptionManager->createSubscription($experiment);
            $experiment->setSubscription($subscription);
        } else {
            $this->debug(sprintf('Updating subscription for experiment %s', $experiment->getId()));
            $this->subscriptionManager->updateSubscription($experiment);
        }
    }
}
