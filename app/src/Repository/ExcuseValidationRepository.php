<?php

namespace App\Repository;

use App\Entity\ExcuseValidation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExcuseValidation>
 */
class ExcuseValidationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExcuseValidation::class);
    }

    /**
     * @param list<int> $excuseIds
     *
     * @return array<int, string>
     */
    public function findLatestRejectedCommentsByExcuseIds(array $excuseIds): array
    {
        if ([] === $excuseIds) {
            return [];
        }

        $rows = $this->createQueryBuilder('ev')
            ->select('IDENTITY(ev.excuse) AS excuseId', 'ev.comment')
            ->andWhere('ev.status = :status')
            ->andWhere('ev.comment IS NOT NULL')
            ->andWhere('ev.comment <> :emptyComment')
            ->andWhere('ev.excuse IN (:excuseIds)')
            ->setParameter('status', 'rejected')
            ->setParameter('emptyComment', '')
            ->setParameter('excuseIds', $excuseIds)
            ->orderBy('ev.validatedAt', 'DESC')
            ->getQuery()
            ->getArrayResult();

        $commentsByExcuse = [];
        foreach ($rows as $row) {
            $excuseId = (int) ($row['excuseId'] ?? 0);
            if ($excuseId <= 0 || array_key_exists($excuseId, $commentsByExcuse)) {
                continue;
            }

            $comment = trim((string) ($row['comment'] ?? ''));
            if ('' === $comment) {
                continue;
            }

            $commentsByExcuse[$excuseId] = $comment;
        }

        return $commentsByExcuse;
    }
}


