<?php

namespace App\Command\SmartConnect;

use App\SmartConnect\SmartConnect;
use DateTimeImmutable;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ObservationCreateCommand extends Command
{
    protected static $defaultName = 'app:smart-connect:observation-create';

    /** @var SmartConnect */
    private $smartConnect;

    /** @var SerializerInterface */
    private $serializer;

    public function __construct(SmartConnect $smartConnect, SerializerInterface $serializer)
    {
        parent::__construct();
        $this->smartConnect = $smartConnect;
        $this->serializer = $serializer;
    }

    protected function configure()
    {
        parent::configure();
        $this
            ->addArgument('platform', InputArgument::REQUIRED, 'The platform id')
            ->addArgument('sensor', InputArgument::REQUIRED, 'The sensor id')
            ->addArgument('min', InputArgument::REQUIRED, 'The min value, e.g. -42')
            ->addArgument('max', InputArgument::REQUIRED, 'The max value, e.g. 87')
            ->addArgument('step', InputArgument::OPTIONAL, 'Max change in value when generating updated values', 1)
            ->addOption('time', null, InputOption::VALUE_REQUIRED, 'The time of measurement')
            ->addOption('value', null, InputOption::VALUE_REQUIRED, 'The value')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $platform = $input->getArgument('platform');
        $sensor = $input->getArgument('sensor');
        $min = $this->getNumber($input->getArgument('min'));
        $max = $this->getNumber($input->getArgument('max'));
        $step = $this->getNumber($input->getArgument('step'));
        $value = $input->getOption('value');
        try {
            $time = new DateTimeImmutable($input->getOption('time') ?? 'now');
        } catch (Exception $exception) {
            throw new RuntimeException(sprintf('Invalid time: %s', $input->getOption('time')));
        }

        $logger = new ConsoleLogger($output);

        if (null !== $value) {
            $value = $this->getNumber($value);
        } else {
            $observation = $this->smartConnect->getObservation($platform, $sensor);
            if (null !== $observation) {
                $value = $this->smartConnect->getValue($observation);
                $delta = $this->getRandomValue(-$step, $step);
                $newValue = $value + $delta;
                $newValue = min($max, $newValue);
                $newValue = max($min, $newValue);

                $logger->info(sprintf('Value: %f -> %f', $value, $newValue));
                $value = $newValue;
            } else {
                $value = $this->getRandomValue($min, $max);
                $logger->info(sprintf('Value: %f', $value));
            }
        }

        $observation = $this->smartConnect->createObservation($platform, $sensor, $value, $time);

        $output->writeln($this->serializer->serialize($observation, 'json'));

        return self::SUCCESS;
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
