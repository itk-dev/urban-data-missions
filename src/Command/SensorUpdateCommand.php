<?php

namespace App\Command;

use App\Scorpio\SensorManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SensorUpdateCommand extends Command
{
    protected static $defaultName = 'app:sensor:update';

    /** @var SensorManager */
    private $sensorManager;

    public function __construct(SensorManager $sensorManager)
    {
        parent::__construct();
        $this->sensorManager = $sensorManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Updates the local list of sensors');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($output->isVerbose()) {
            $logger = new ConsoleLogger($output);
            $this->sensorManager->setLogger($logger);
        }

        $sensors = $this->sensorManager->updatePlatformSensors();

        if ($io->isVerbose()) {
            foreach ($sensors as $sensor) {
                $io->definitionList(
                    ['id' => $sensor->getId()],
                    ['name' => $sensor->getName()],
                    ['type' => $sensor->getType()]
                );
            }
        }

        return static::SUCCESS;
    }
}
