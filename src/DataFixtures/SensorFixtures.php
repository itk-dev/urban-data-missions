<?php

namespace App\DataFixtures;

use App\Scorpio\SensorManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SensorFixtures extends AbstractFixture implements FixtureGroupInterface, DependentFixtureInterface
{
    /** @var SensorManager */
    private $sensorManager;

    public function __construct(SensorManager $sensorManager)
    {
        $this->sensorManager = $sensorManager;
    }

    public function load(ObjectManager $manager)
    {
        $this->sensorManager->updateSensors();

        $sensors = $this->sensorManager->getSensors();
        foreach ($sensors as $sensor) {
            $this->writeln(sprintf('Sensor: %s', $sensor->getId()));
            $this->addReference('sensor:'.$sensor->getId(), $sensor);
        }
    }

    public static function getGroups(): array
    {
        return ['sensor'];
    }

    public function getDependencies()
    {
        return [MeasurementFixtures::class];
    }
}
