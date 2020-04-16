<?php

namespace App\Export;

use App\Entity\Mission;
use App\Repository\MeasurementRepository;
use App\Repository\MissionLogEntryRepository;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use DateTimeImmutable;

class MissionExport
{
    /** @var MissionLogEntryRepository */
    private $logEntryRepository;

    /** @var MeasurementRepository */
    private $measurementRepository;

    public function __construct(MissionLogEntryRepository $logEntryRepository, MeasurementRepository $measurementRepository)
    {
        $this->logEntryRepository = $logEntryRepository;
        $this->measurementRepository = $measurementRepository;
    }

    private const EMPTY_VALUE = '';

    public function exportLogEntries(Mission $mission, string $format)
    {
        $entries = $this->logEntryRepository->findByMission($mission, ['loggedAt' => 'ASC']);
        $filename = $this->getFilename($mission, $format);

        $sensorNames = $mission->getMissionSensorNames();

        $writer = WriterEntityFactory::createWriter($format);
        $writer->openToBrowser($filename);
        $writer->addRow(WriterEntityFactory::createRowFromArray([
            'logged at',
            'type',
            'content',
            'sensor id',
            'sensor name',
            'sensor value',
        ]));
        foreach ($entries as $entry) {
            $measurement = $entry->getMeasurement();
            $writer->addRow(
                WriterEntityFactory::createRowFromArray([
                    $entry->getLoggedAt()->format(DateTimeImmutable::ATOM),
                    $entry->getType(),
                    $entry->getContent(),
                    $measurement ? $measurement->getSensor()->getId() : static::EMPTY_VALUE,
                    $measurement ? $sensorNames[$measurement->getSensor()->getId()] : static::EMPTY_VALUE,
                    $measurement ? $measurement->getValue() : static::EMPTY_VALUE,
                ])
            );
        }
        $writer->close();
        exit;
    }

    public function exportMeasurements(Mission $mission, string $format)
    {
        $measurements = $this->measurementRepository->findByMission($mission, ['measuredAt' => 'ASC']);
        $filename = $this->getFilename($mission, $format);

        $sensorNames = $mission->getMissionSensorNames();

        $writer = WriterEntityFactory::createWriter($format);
        $writer->openToBrowser($filename);
        $writer->addRow(WriterEntityFactory::createRowFromArray([
            'measured at',
            'sensor type',
            'sensor id',
            'sensor name',
            'sensor value',
        ]));
        foreach ($measurements as $measurement) {
            $writer->addRow(
                WriterEntityFactory::createRowFromArray([
                    $measurement->getMeasuredAt()->format(DateTimeImmutable::ATOM),
                    $measurement->getSensor()->getType(),
                    $measurement->getSensor()->getId(),
                    $sensorNames[$measurement->getSensor()->getId()],
                    $measurement->getValue(),
                ])
            );
        }
        $writer->close();
        exit;
    }

    private function getFilename(Mission $mission, string $format)
    {
        return sprintf('mission-%s-data-%s.%s',
            $mission->getTitle(),
            (new DateTimeImmutable())->format(DateTimeImmutable::ATOM),
            $format
        );
    }
}
