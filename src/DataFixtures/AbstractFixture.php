<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\PropertyAccess\PropertyAccess;

abstract class AbstractFixture extends Fixture
{
    /** @var ConsoleOutput */
    protected $output;

    /** @var PropertyAccess */
    protected $accessor;

    protected function buildEntity(string $class, array $values)
    {
        if (null === $this->accessor) {
            $this->accessor = PropertyAccess::createPropertyAccessor();
        }

        $entity = new $class();
        foreach ($values as $property => $value) {
            $this->accessor->setValue($entity, $property, $value);
        }

        return $entity;
    }

    protected function write($messages, bool $newline = false, int $options = ConsoleOutput::OUTPUT_NORMAL)
    {
        if (null === $this->output) {
            $this->output = new ConsoleOutput();
        }

        $this->output->write($messages, $newline, $options);
    }

    protected function writeln($messages, int $options = ConsoleOutput::OUTPUT_NORMAL)
    {
        $this->write($messages, true, $options);
    }
}
