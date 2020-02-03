<?php

namespace App\Experiment;

use App\Entity\Experiment;

class ExperimentManager
{
    public function updateSubscription(Experiment $experiment, string $ngsiLdBrokerUrl)
    {
        $subscription = $experiment->getSubscription();
        if (null === $subscription) {
            // Create subscription.
        } else {
            // Update subscription.
        }
    }
}
