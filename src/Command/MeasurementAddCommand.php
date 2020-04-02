<?php

namespace App\Command;

use App\Scorpio\MeasurementManager;
use DateTimeImmutable;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class MeasurementAddCommand extends Command
{
    protected static $defaultName = 'app:measurement:add';

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
            ->addArgument('sensor', InputArgument::REQUIRED, 'Sensor id')
            ->addArgument('type', InputArgument::REQUIRED, 'The measurement type, e.g. "temperature"')
            ->addArgument('min', InputArgument::REQUIRED, 'The min value, e.g. -42')
            ->addArgument('max', InputArgument::REQUIRED, 'The max value, e.g. 87')
            ->addArgument('step', InputArgument::OPTIONAL, 'Max change in value when generating updated values', 1)
            ->addOption('measured-at', null, InputOption::VALUE_REQUIRED, 'The time of measurement');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
        $sensor = $input->getArgument('sensor');
        $type = $input->getArgument('type');
        $min = $this->getNumber($input->getArgument('min'));
        $max = $this->getNumber($input->getArgument('max'));
        $step = $this->getNumber($input->getArgument('step'));
        $measuredAt = new DateTimeImmutable($input->getOption('measured-at') ?? 'now');

        $logger = new ConsoleLogger($output);
        $this->measurementManager->setLogger($logger);

        $existingMeasurement = $this->measurementManager->getMeasurement($sensor, $type);

        if (null !== $existingMeasurement) {
            $value = $this->measurementManager->getValue($existingMeasurement);
            $delta = $this->getRandomValue(-$step, $step);

            $newValue = $value + $delta;
            $newValue = min($max, $newValue);
            $newValue = max($min, $newValue);
            $status = $this->measurementManager->updateMeasurement($sensor, $type, $newValue, $measuredAt);
            if ($status) {
                $logger->info(sprintf('%s %s updated; value: %f -> %f', $sensor, $type, $value, $newValue));
            } else {
                $logger->info('Error updating measurement. Sad but true!');
            }
        } else {
            $value = $this->getRandomValue($min, $max);
            $status = $this->measurementManager->createMeasurement($sensor, $type, $value, $measuredAt);
            if ($status) {
                $logger->info(sprintf('%s %s created; value: %f', $sensor, $type, $value));
            } else {
                $logger->info('Error creating measurement. Sad but true!');
            }
        }

        $logger->info(sprintf('status: %s', $status ? 'ok' : 'error'));

        return 0;
    }

    private function getRandomValue($min, $max, $scale = 1000000)
    {
        $floatScale = is_int($min) && is_int($max) ? 1 : $scale;

        return random_int($min * $floatScale, $max * $floatScale) / $floatScale;
    }

    private function getNumber(string $value)
    {
        if (preg_match('/^-?\d+$/', $value)) {
            return (int) $value;
        } elseif (preg_match('/^-?\d+\.\d+$/', $value)) {
            return (float) $value;
        }

        throw new RuntimeException(sprintf('Numeric value expected; found: %s', json_encode($value)));
    }
}
