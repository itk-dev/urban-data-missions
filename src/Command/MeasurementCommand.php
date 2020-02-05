<?php

namespace App\Command;

use App\Scorpio\MeasurementManager;
use DateTimeImmutable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class MeasurementCommand extends Command
{
    protected static $defaultName = 'app:measurement';

    /** @var MeasurementManager */
    private $measurementManager;

    public function __construct(MeasurementManager $sensorManager)
    {
        parent::__construct();
        $this->measurementManager = $sensorManager;
    }

    protected function configure()
    {
        $this
            ->addArgument('action', InputArgument::REQUIRED, '"create", "update" or "delete"')
            ->addArgument('sensor', InputArgument::REQUIRED, 'Sensor id')
            ->addArgument('type', InputArgument::OPTIONAL, 'The measurement type, e.g. "temperature"')
            ->addArgument('value', InputArgument::OPTIONAL, 'The measurement value, e.g. 42')
            ->addOption('measured-at', null, InputOption::VALUE_REQUIRED, 'The time of measurement');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
        $action = $input->getArgument('action');
        $sensor = $input->getArgument('sensor');
        $type = $input->getArgument('type');
        $value = $input->getArgument('value');
        if (is_int($value)) {
            $value = (int) $value;
        } elseif (is_numeric($value)) {
            $value = (float) $value;
        }
        $measuredAt = $input->getOption('measured-at') ? new DateTimeImmutable($input->getOption('measured-at')) : null;

        $logger = new ConsoleLogger($output);
        $this->measurementManager->setLogger($logger);

        switch ($action) {
            case 'create':
                $status = $this->measurementManager->createMeasurement($sensor, $type, $value, $measuredAt);
                break;

            case 'delete':
                $status = $this->measurementManager->deleteMeasurement($sensor);
                break;

            case 'update':
            default:
            $status = $this->measurementManager->updateMeasurement($sensor, $type, $value, $measuredAt);
                break;
        }

        $logger->info(sprintf('status: %s', $status ? 'ok' : 'error'));

        return 0;
    }
}
