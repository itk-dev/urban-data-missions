<?php

namespace App\Command;

use App\Scorpio\SensorManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class SensorCommand extends Command
{
    protected static $defaultName = 'app:sensor';

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
            ->addArgument('action', InputArgument::REQUIRED, '"update"');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
        $action = $input->getArgument('action');

        $logger = new ConsoleLogger($output);
        $this->sensorManager->setLogger($logger);

        switch ($action) {
            case 'update':
            default:
            $this->sensorManager->updateSensors();
                break;
        }

        return 0;
    }
}
