<?php

namespace App\Repository;

use App\Entity\ClassicExcuse;
use App\Entity\EmergencyExcuse;
use App\Entity\Excuse;
use App\Entity\ProfessionalExcuse;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Excuse>
 */
class ExcuseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Excuse::class);
    }

    /**
     * @return Excuse[]
     */
    public function findPendingExcuses(): array
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.author', 'a')->addSelect('a')
            ->leftJoin('e.category', 'c')->addSelect('c')
            ->leftJoin('e.context', 'ctx')->addSelect('ctx')
            ->leftJoin('e.tone', 't')->addSelect('t')
            ->andWhere('e.status = :status')
            ->setParameter('status', 'pending')
            ->addSelect('COALESCE(e.updatedAt, e.createdAt) AS HIDDEN lastActivityAt')
            ->orderBy('lastActivityAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Excuse[]
     */
    public function findUserExcuses(User $user): array
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.category', 'c')->addSelect('c')
            ->leftJoin('e.context', 'ctx')->addSelect('ctx')
            ->leftJoin('e.tone', 't')->addSelect('t')
            ->andWhere('e.author = :author')
            ->setParameter('author', $user)
            ->addSelect('COALESCE(e.updatedAt, e.createdAt) AS HIDDEN lastActivityAt')
            ->orderBy('lastActivityAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Excuse[]
     */
    public function findValidatedExcuses(): array
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.author', 'a')->addSelect('a')
            ->leftJoin('e.category', 'c')->addSelect('c')
            ->leftJoin('e.context', 'ctx')->addSelect('ctx')
            ->leftJoin('e.tone', 't')->addSelect('t')
            ->andWhere('e.status = :status')
            ->setParameter('status', 'validated')
            ->orderBy('e.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Filtres supportés: status, authorId, categoryId, contextId, toneId, keyword, minCredibility, maxCredibility, sort.
     *
     * @param array<string, mixed> $filters
     *
     * @return Excuse[]
     */
    public function findByFilters(array $filters = []): array
    {
        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.author', 'a')->addSelect('a')
            ->leftJoin('e.category', 'c')->addSelect('c')
            ->leftJoin('e.context', 'ctx')->addSelect('ctx')
            ->leftJoin('e.tone', 't')->addSelect('t')
            ->addSelect('COALESCE(e.updatedAt, e.createdAt) AS HIDDEN sortActivityAt');

        if (!empty($filters['status'])) {
            $qb->andWhere('e.status = :status')
                ->setParameter('status', (string) $filters['status']);
        }

        if (!empty($filters['type'])) {
            $type = mb_strtolower((string) $filters['type']);

            if ('classic' === $type) {
                $qb->andWhere(sprintf('e INSTANCE OF %s', ClassicExcuse::class));
            } elseif ('emergency' === $type) {
                $qb->andWhere(sprintf('e INSTANCE OF %s', EmergencyExcuse::class));
            } elseif ('professional' === $type) {
                $qb->andWhere(sprintf('e INSTANCE OF %s', ProfessionalExcuse::class));
            }
        }

        if (!empty($filters['authorId'])) {
            $qb->andWhere('a.id = :authorId')
                ->setParameter('authorId', (int) $filters['authorId']);
        }

        if (!empty($filters['categoryId'])) {
            $qb->andWhere('c.id = :categoryId')
                ->setParameter('categoryId', (int) $filters['categoryId']);
        }

        if (!empty($filters['contextId'])) {
            $qb->andWhere('ctx.id = :contextId')
                ->setParameter('contextId', (int) $filters['contextId']);
        }

        if (!empty($filters['toneId'])) {
            $qb->andWhere('t.id = :toneId')
                ->setParameter('toneId', (int) $filters['toneId']);
        }

        if (!empty($filters['keyword'])) {
            $qb->andWhere('LOWER(e.title) LIKE :keyword OR LOWER(e.content) LIKE :keyword')
                ->setParameter('keyword', '%'.mb_strtolower((string) $filters['keyword']).'%');
        }

        if (isset($filters['minCredibility']) && '' !== (string) $filters['minCredibility']) {
            $qb->andWhere('e.credibilityScore >= :minCredibility')
                ->setParameter('minCredibility', (int) $filters['minCredibility']);
        }

        if (isset($filters['maxCredibility']) && '' !== (string) $filters['maxCredibility']) {
            $qb->andWhere('e.credibilityScore <= :maxCredibility')
                ->setParameter('maxCredibility', (int) $filters['maxCredibility']);
        }

        $sort = (string) ($filters['sort'] ?? 'recent');

        if ('oldest' === $sort) {
            $qb->orderBy('sortActivityAt', 'ASC');
        } elseif ('credibility_desc' === $sort) {
            $qb->orderBy('e.credibilityScore', 'DESC')->addOrderBy('sortActivityAt', 'DESC');
        } elseif ('credibility_asc' === $sort) {
            $qb->orderBy('e.credibilityScore', 'ASC')->addOrderBy('sortActivityAt', 'DESC');
        } elseif ('title_asc' === $sort) {
            $qb->orderBy('e.title', 'ASC')->addOrderBy('sortActivityAt', 'DESC');
        } elseif ('title_desc' === $sort) {
            $qb->orderBy('e.title', 'DESC')->addOrderBy('sortActivityAt', 'DESC');
        } else {
            $qb->orderBy('sortActivityAt', 'DESC');
        }

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Excuse[] Returns an array of Excuse objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Excuse
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
