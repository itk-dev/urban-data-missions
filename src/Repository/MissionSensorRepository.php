<?php

namespace App\Repository;

use App\Entity\MissionSensor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method MissionSensor|null find($id, $lockMode = null, $lockVersion = null)
 * @method MissionSensor|null findOneBy(array $criteria, array $orderBy = null)
 * @method MissionSensor[]    findAll()
 * @method MissionSensor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MissionSensorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MissionSensor::class);
    }

    // /**
    //  * @return MissionSensor[] Returns an array of MissionSensor objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MissionSensor
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
