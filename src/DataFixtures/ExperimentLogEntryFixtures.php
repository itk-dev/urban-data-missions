<?php

namespace App\DataFixtures;

use App\Entity\ExperimentLogEntry;
use App\Scorpio\SensorManager;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ExperimentLogEntryFixtures extends AbstractFixture implements FixtureGroupInterface, DependentFixtureInterface
{
    /** @var SensorManager */
    private $sensorManager;

    public function __construct(SensorManager $sensorManager)
    {
        $this->sensorManager = $sensorManager;
    }

    public function load(ObjectManager $manager)
    {
        $items = [
            [
                'experiment' => 'experiment:The first experiment',
                'entries' => [
                    [
                        'loggedAt' => new DateTimeImmutable('2020-03-23T00:00:00'),
                        'content' => 'Experiment started',
                        'type' => 'system',
                    ],
                    [
                        'loggedAt' => new DateTimeImmutable('2020-03-23T12:01:04'),
                        'content' => 'Something happened!',
                        'type' => 'user',
                        'sensor' => 'sensor:fixture:device:001:temperature',
                    ],
                ],
            ],

            [
                'experiment' => 'experiment:Another experiment',
                'entries' => [],
            ],

            [
                'experiment' => 'experiment:Third time\'s a charm',
                'entries' => [
                    [
                        'loggedAt' => new DateTimeImmutable('2020-03-23T01:02:00'),
                        'content' => 'Experiment started',
                        'type' => 'system',
                    ],
                    [
                        'loggedAt' => new DateTimeImmutable('2020-03-23T12:01:04'),
                        'content' => 'Something happened!',
                        'type' => 'user',
                        'sensor' => 'sensor:fixture:device:001:temperature',
                    ],
                ],
            ],
        ];

        foreach ($items as $item) {
            $experiment = $this->getReference($item['experiment']);
            foreach ($item['entries'] as $data) {
                $data['experiment'] = $experiment;
                if (isset($data['sensor'])) {
                    $data['sensor'] = $this->getReference($data['sensor']);
                }
                $entry = $this->buildEntity(ExperimentLogEntry::class, $data);
                $manager->persist($entry);
            }
            $manager->flush();
        }
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['experiment'];
    }

    public function getDependencies()
    {
        return [ExperimentFixtures::class];
    }
}
