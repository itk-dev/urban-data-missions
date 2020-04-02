<?php

namespace App\Repository;

use App\Entity\MissionLogEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method MissionLogEntry|null find($id, $lockMode = null, $lockVersion = null)
 * @method MissionLogEntry|null findOneBy(array $criteria, array $orderBy = null)
 * @method MissionLogEntry[]    findAll()
 * @method MissionLogEntry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MissionLogEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MissionLogEntry::class);
    }
}
