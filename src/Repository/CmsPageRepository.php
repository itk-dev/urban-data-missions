<?php

namespace App\Repository;

use App\Entity\CmsPage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method CmsPage|null find($id, $lockMode = null, $lockVersion = null)
 * @method CmsPage|null findOneBy(array $criteria, array $orderBy = null)
 * @method CmsPage[]    findAll()
 * @method CmsPage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CmsPageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CmsPage::class);
    }
}
