<?php

namespace App\Repository;

use App\Entity\Excuse;
use App\Entity\ExcuseRating;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExcuseRating>
 */
class ExcuseRatingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExcuseRating::class);
    }

    public function findOneByExcuseAndAuthor(Excuse $excuse, User $author): ?ExcuseRating
    {
        return $this->findOneBy(['excuse' => $excuse, 'author' => $author]);
    }

    /**
     * @return array{average: float|null, count: int}
     */
    public function getStatsForExcuse(Excuse $excuse): array
    {
        $result = $this->createQueryBuilder('r')
            ->select('AVG(r.score) AS average', 'COUNT(r.id) AS total')
            ->andWhere('r.excuse = :excuse')
            ->setParameter('excuse', $excuse)
            ->getQuery()
            ->getSingleResult();

        return [
            'average' => null !== $result['average'] ? round((float) $result['average'], 1) : null,
            'count' => (int) $result['total'],
        ];
    }
}
