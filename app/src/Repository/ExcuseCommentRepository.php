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

    /**
     * @param int[] $excuseIds
     *
     * @return array<int, int>
     */
    public function countByExcuseIds(array $excuseIds): array
    {
        if ([] === $excuseIds) {
            return [];
        }

        $rows = $this->createQueryBuilder('c')
            ->select('IDENTITY(c.excuse) AS excuseId, COUNT(c.id) AS commentCount')
            ->andWhere('c.excuse IN (:excuseIds)')
            ->setParameter('excuseIds', $excuseIds)
            ->groupBy('c.excuse')
            ->getQuery()
            ->getArrayResult();

        $counts = [];
        foreach ($rows as $row) {
            $counts[(int) $row['excuseId']] = (int) $row['commentCount'];
        }

        return $counts;
    }
}
