<?php

namespace App\Repository;

use App\Entity\Mission;
use App\Entity\MissionSensor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method MissionSensor|null find($id, $lockMode = null, $lockVersion = null)
 * @method MissionSensor|null findOneBy(array $criteria, array $orderBy = null)
 * @method MissionSensor[]    findAll()
 * @method MissionSensor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method MissionSensor[]    findByMission(Mission $mission, array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MissionSensorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MissionSensor::class);
    }
}
