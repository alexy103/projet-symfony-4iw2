<?php

namespace App\Repository;

use App\Entity\Excuse;
use App\Entity\ExcuseComment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExcuseComment>
 */
class ExcuseCommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExcuseComment::class);
    }

    /**
     * @return ExcuseComment[]
     */
    public function findForExcuse(Excuse $excuse): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.excuse = :excuse')
            ->setParameter('excuse', $excuse)
            ->leftJoin('c.author', 'a')->addSelect('a')
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
