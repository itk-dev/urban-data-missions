<?php

namespace App\EventListener;

use App\Entity\ExperimentLogEntry;
use App\Entity\Measurement;
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

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof Measurement) {
            $measurement = $entity;

            $experiment = $measurement->getExperiment();
            $sensor = $measurement->getSensor();
            $warnings = $this->sensorWarningRepository->findBy([
                'experiment' => $experiment,
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
                    $this->debug($content);
                    $logEntry = (new ExperimentLogEntry())
                        ->setExperiment($measurement->getExperiment())
                        ->setSensor($sensor)
                        ->setLoggedAt($measurement->getMeasuredAt())
                        ->setType(ExperimentLogEntry::TYPE_ALERT)
                        ->setContent($content);
                    $this->entityManager->persist($logEntry);
                }
            }
            $this->entityManager->flush();
        }
    }
}
