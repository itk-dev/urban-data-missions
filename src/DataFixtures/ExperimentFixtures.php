<?php

namespace App\DataFixtures;

use App\Entity\Experiment;
use App\Scorpio\SensorManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ExperimentFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    /** @var SensorManager */
    private $sensorManager;

    public function __construct(SensorManager $sensorManager)
    {
        $this->sensorManager = $sensorManager;
    }

    public function load(ObjectManager $manager)
    {
        $experiments = [
            [
                'title' => 'The first experiment',
                'sensors' => [
                    ['id' => 'fixture:sensor:001:humidity'],
                ],
            ],

            [
                'title' => 'Another experiment',
                'sensors' => [
                    ['id' => 'fixture:sensor:001:temperature'],
                ],
            ],

            [
                'title' => 'Third time\'s a charm',
                'sensors' => [
                    ['id' => 'fixture:sensor:001:temperature'],
                    ['id' => 'fixture:sensor:001:humidity'],
                ],
            ],
        ];

        foreach ($experiments as $data) {
            $experiment = (new Experiment())
                ->setTitle($data['title']);
            $manager->persist($experiment);
            $manager->flush();

            // Persist again to set up subscriptions.
            foreach ($data['sensors'] as $sensor) {
                $experiment->addSensor($this->sensorManager->getSensor($sensor['id']));
            }
            $manager->persist($experiment);
        }
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['experiment'];
    }

    public function getDependencies()
    {
        return [SensorFixtures::class];
    }
}
