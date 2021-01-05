<?php

namespace App\Command;

use App\Repository\SensorRepository;
use App\Scorpio\SensorManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SensorSearchCommand extends Command
{
    protected static $defaultName = 'app:sensor:search';

    /**
     * @var SensorManager
     */
    private $sensorRepository;

    public function __construct(SensorRepository $sensorRepository)
    {
        parent::__construct();
        $this->sensorRepository = $sensorRepository;
    }

    protected function configure()
    {
        $this
            ->addArgument('query', InputArgument::REQUIRED, 'The search query')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $query = $input->getArgument('query');

        $sensors = $this->sensorRepository->search($query);

        foreach ($sensors as $sensor) {
            $io->definitionList(
                ['id' => $sensor->getId()],
                ['name' => $sensor->getName()],
                ['type' => $sensor->getType()]
            );
        }

        return self::SUCCESS;
    }
}
