<?php

namespace App\Command\Experiment;

use App\Repository\ExperimentRepository;
use App\Scorpio\SubscriptionManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class SubscriptionUpdateCommand extends Command
{
    protected static $defaultName = 'app:experiment:subscription-update';

    /** @var ExperimentRepository */
    private $repository;

    /** @var SubscriptionManager */
    private $subscriptionManager;

    public function __construct(ExperimentRepository $repository, SubscriptionManager $subscriptionManager)
    {
        parent::__construct();
        $this->repository = $repository;
        $this->subscriptionManager = $subscriptionManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Update subscription for all or select experiments')
            ->addArgument('experiment', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Experiment(s) to update');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = new ConsoleLogger($output);
        $this->subscriptionManager->setLogger($logger);

        $ids = $input->getArgument('experiment');

        $experiments = empty($ids) ? $this->repository->findAll() : $this->repository->findBy(['id' => $ids]);

        foreach ($experiments as $experiment) {
            $this->subscriptionManager->ensureSubscription($experiment);
        }

        return 0;
    }
}
