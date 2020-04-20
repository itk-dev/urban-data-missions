<?php

namespace App\Repository;

use App\Entity\MissionSensorWarning;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method MissionSensorWarning|null find($id, $lockMode = null, $lockVersion = null)
 * @method MissionSensorWarning|null findOneBy(array $criteria, array $orderBy = null)
 * @method MissionSensorWarning[]    findAll()
 * @method MissionSensorWarning[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MissionSensorWarningRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MissionSensorWarning::class);
    }
}
