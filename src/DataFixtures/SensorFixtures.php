<?php

namespace App\DataFixtures;

use App\Scorpio\SensorManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SensorFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
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
    }

    public static function getGroups(): array
    {
        return ['sensor'];
    }

    public function getDependencies()
    {
        return [IoTDataFixtures::class];
    }
}
