<?php

namespace App\EventListener;

use App\Entity\Measurement;
use App\Entity\MissionLogEntry;
use App\Repository\SensorWarningRepository;
use App\Traits\LoggerTrait;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;

class MeasurementListener implements LoggerAwareInterface
{
    use LoggerTrait;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var SensorWarningRepository */
    private $sensorWarningRepository;

    public function __construct(EntityManagerInterface $entityManager, SensorWarningRepository $sensorWarningRepository)
    {
        $this->entityManager = $entityManager;
        $this->sensorWarningRepository = $sensorWarningRepository;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof Measurement) {
            $measurement = $entity;

            $mission = $measurement->getMission();
            $sensor = $measurement->getSensor();
            $warnings = $this->sensorWarningRepository->findBy([
                'mission' => $mission,
                'sensor' => $sensor,
            ]);

            foreach ($warnings as $warning) {
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
