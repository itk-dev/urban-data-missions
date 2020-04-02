<?php

namespace App\Repository;

use App\Entity\MissionTheme;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method MissionTheme|null find($id, $lockMode = null, $lockVersion = null)
 * @method MissionTheme|null findOneBy(array $criteria, array $orderBy = null)
 * @method MissionTheme[]    findAll()
 * @method MissionTheme[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MissionThemeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MissionTheme::class);
    }
}
