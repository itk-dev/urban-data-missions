<?php

namespace App\EventListener;

use App\Entity\Measurement;
use App\Entity\Mission;
use App\Entity\MissionLogEntry;
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
            ], 'jsonld', ['groups' => ['mission_read', 'measurement_read']]);

            $this->publishMissionUpdate($measurement->getMission(), $data);
        } elseif ($entity instanceof MissionLogEntry) {
            $logEntry = $entity;
            $data = $this->serializer->serialize([
                'log_entry' => $logEntry,
            ], 'jsonld', ['groups' => ['mission_read', 'mission_log_entry_read']]);

            $this->publishMissionUpdate($logEntry->getMission(), $data);
        }
    }

    private function publishMissionUpdate(Mission $mission, string $data)
    {
        $update = new Update(
            'mission:'.$mission->getId(),
            $data
        );

        $this->publish($update);
    }

    private function publish(Update $update)
    {
        $this->debug(sprintf('Update: %s -> %s', json_encode($update->getTopics()), $update->getData()));

        return $this->publisher->__invoke($update);
    }
}
