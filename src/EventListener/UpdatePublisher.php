<?php

namespace App\EventListener;

use App\Entity\Experiment;
use App\Entity\ExperimentLogEntry;
use App\Entity\Measurement;
use App\Traits\LoggerTrait;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Mercure\PublisherInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Serializer\SerializerInterface;

class UpdatePublisher implements LoggerAwareInterface
{
    use LoggerTrait;

    /** @var PublisherInterface */
    private $publisher;

    /** @var SerializerInterface */
    private $serializer;

    public function __construct(PublisherInterface $publisher, SerializerInterface $serializer)
    {
        $this->publisher = $publisher;
        $this->serializer = $serializer;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof Measurement) {
            $measurement = $entity;
            $data = $this->serializer->serialize([
                'measurement' => $measurement,
            ], 'json', ['groups' => ['experiment_read', 'measurement_read']]);

            $this->publishExperimentUpdate($measurement->getExperiment(), $data);
        } elseif ($entity instanceof ExperimentLogEntry) {
            $logEntry = $entity;
            $data = $this->serializer->serialize([
                'log_entry' => $logEntry,
            ], 'json', ['groups' => ['experiment_read', 'experiment_log_entry_read']]);

            $this->publishExperimentUpdate($logEntry->getExperiment(), $data);
        }
    }

    private function publishExperimentUpdate(Experiment $experiment, string $data)
    {
        $update = new Update(
            'experiment:'.$experiment->getId(),
            $data
        );

        $this->publish($update);
    }

    private function publish(Update $update)
    {
        $this->debug(sprintf('Update: %s -> %s', json_encode($update->getTargets()), $update->getData()));

        return $this->publisher->__invoke($update);
    }
}
