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
            ->setTitle('The first experiment')
            ->setSensors(['urn:ngsi-ld:testunit:123']);
        $manager->persist($experiment);

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['experiment'];
    }
}
