<?php

namespace App\DataFixtures;

use App\Scorpio\MeasurementManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Console\Output\ConsoleOutput;

class IoTDataFixtures extends Fixture implements FixtureGroupInterface
{
    /** @var MeasurementManager */
    private $measurementManager;

    public function __construct(MeasurementManager $measurementManager)
    {
        $this->measurementManager = $measurementManager;
    }

    public function load(ObjectManager $manager)
    {
        $output = new ConsoleOutput();

        $sensors = [
            'sensor:001' => [
                'temperature' => 42,
                'humidity' => 87,
            ],
        ];

        foreach ($sensors as $name => $sensor) {
            foreach ($sensor as $type => $value) {
                $sensorId = 'fixture:'.$name.':'.$type;
                $this->measurementManager->deleteMeasurement($sensorId);
                $this->measurementManager->createMeasurement($sensorId, $type, $value);
                $output->writeln($this->measurementManager->getMeasurementUrl($sensorId));
            }
        }
    }

    public static function getGroups(): array
    {
        return ['iot-data'];
    }
}
