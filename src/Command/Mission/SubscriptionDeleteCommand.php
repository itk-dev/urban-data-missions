<?php

namespace App\Command\Mission;

use App\Repository\MissionRepository;
use App\Scorpio\SubscriptionManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class SubscriptionDeleteCommand extends Command
{
    protected static $defaultName = 'app:mission:subscription:delete';

    /** @var MissionRepository */
    private $repository;

    /** @var SubscriptionManager */
    private $subscriptionManager;

    public function __construct(MissionRepository $repository, SubscriptionManager $subscriptionManager)
    {
        parent::__construct();
        $this->repository = $repository;
        $this->subscriptionManager = $subscriptionManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Delete subscription for all or select missions')
            ->addArgument('mission', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Mission(s) to update');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = new ConsoleLogger($output);
        $this->subscriptionManager->setLogger($logger);

        $ids = $input->getArgument('mission');

        $missions = empty($ids) ? $this->repository->findAll() : $this->repository->findBy(['id' => $ids]);

        foreach ($missions as $mission) {
            $this->subscriptionManager->deleteSubscription($mission);
        }

        return 0;
    }
}
