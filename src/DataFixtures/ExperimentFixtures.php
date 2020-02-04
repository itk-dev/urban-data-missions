<?php

namespace App\DataFixtures;

use App\Entity\Experiment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class ExperimentFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager)
    {
        $experiment = (new Experiment())
            ->setTitle('The first experiment');
        $manager->persist($experiment);
        $manager->flush();

        // Persist again to set up subscriptions.
        $experiment
            ->setSensors([
                'fixture:sensor:001:humidity',
                'fixture:sensor:001:temperature',
            ]);
        $manager->persist($experiment);
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['experiment'];
    }
}
