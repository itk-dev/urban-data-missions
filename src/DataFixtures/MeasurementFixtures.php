<?php

namespace App\DataFixtures;

use App\Scorpio\MeasurementManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class MeasurementFixtures extends AbstractFixture implements FixtureGroupInterface
{
    /** @var MeasurementManager */
    private $measurementManager;

    public function __construct(MeasurementManager $measurementManager)
    {
        $this->measurementManager = $measurementManager;
    }

    public function load(ObjectManager $manager)
    {
        $measurements = [
            'fixture:device:001' => [
                'temperature' => 42,
                'humidity' => 87,
            ],
        ];

        foreach ($measurements as $device => $sensor) {
            foreach ($sensor as $type => $value) {
                $this->measurementManager->deleteMeasurement($device, $type);
                $this->measurementManager->createMeasurement($device, $type, $value);
                $this->writeln([
                    sprintf('Measurement: %s %s', $device, $type),
                    sprintf('Url:         %s', $this->measurementManager->getMeasurementUrl($device, $type)),
                ]);
            }
        }
    }

    public static function getGroups(): array
    {
        return ['measurement'];
    }
}
