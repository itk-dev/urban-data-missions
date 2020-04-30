<?php

namespace App\EventListener;

use App\Entity\Measurement;
use App\Entity\MissionLogEntry;
use App\Repository\MissionSensorRepository;
use App\Traits\LoggerTrait;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;

class MeasurementListener implements LoggerAwareInterface
{
    use LoggerTrait;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var MissionSensorRepository */
    private $missionSensorRepository;

    public function __construct(EntityManagerInterface $entityManager, MissionSensorRepository $missionSensorRepository)
    {
        $this->entityManager = $entityManager;
        $this->missionSensorRepository = $missionSensorRepository;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof Measurement) {
            $measurement = $entity;

            $mission = $measurement->getMission();
            $sensor = $measurement->getSensor();
            $missionSensors = $this->missionSensorRepository->findBy([
                'mission' => $mission,
                'sensor' => $sensor,
            ]);
            foreach ($missionSensors as $missionSensor) {
                foreach ($missionSensor->getSensorWarnings() as $warning) {
                    [$min, $max] = [$warning->getMin() ?? -INF, $warning->getMax() ?? INF];
                    $value = $entity->getValue();
                    $this->debug(sprintf('Checking sensor %s; value: %f', $sensor->getId(), $value));
                    if ($value < $min || $max < $value) {
                        $content = strtr(
                            $warning->getMessage() ?? 'Measured value %value% outside expected range [%min%, %max%]',
                            [
                                '%value%' => sprintf('%f', $value),
                                '%min%' => sprintf('%f', $min),
                                '%max%' => sprintf('%f', $max),
                            ]
                        );
                        $logEntry = (new MissionLogEntry())
                            ->setMission($measurement->getMission())
                            ->setMeasurement($measurement)
                            ->setLoggedAt($measurement->getMeasuredAt())
                            ->setType(MissionLogEntry::TYPE_ALERT)
                            ->setContent($content);
                        $this->entityManager->persist($logEntry);
                    }
                }
                $this->entityManager->flush();
            }
        }
    }
}
