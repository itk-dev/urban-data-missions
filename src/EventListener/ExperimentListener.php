<?php

namespace App\EventListener;

use App\Entity\Experiment;
use App\Scorpio\SubscriptionManager;
use Doctrine\ORM\Event\OnFlushEventArgs;

class ExperimentListener
{
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

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
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
            $subscription = $this->subscriptionManager->createSubscription($experiment);
            $experiment->setSubscription($subscription);
        } else {
            $this->subscriptionManager->updateSubscription($experiment);
        }
    }
}
