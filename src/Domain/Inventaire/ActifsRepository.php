<?php
// src/Repository/ActifsRepository.php

namespace App\Domain\Inventaire;

use App\Domain\Inventaire\Actifs;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ActifsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Actifs::class);
    }

    public function findByFilters(?string $search, ?string $category, ?string $status, int $page = 1, int $limit = 10): array
    {
        $queryBuilder = $this->createQueryBuilder('a')
            ->orderBy('a.createdAt', 'DESC');

        if ($search) {
            $queryBuilder->andWhere('a.name LIKE :search OR a.serie LIKE :search OR a.modele LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($category) {
            $queryBuilder->andWhere('a.category = :category')
                ->setParameter('category', $category);
        }

        if ($status) {
            $queryBuilder->andWhere('a.status = :status')
                ->setParameter('status', $status);
        }

        return $queryBuilder
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countByFilters(?string $search, ?string $category, ?string $status): int
    {
        $queryBuilder = $this->createQueryBuilder('a')
            ->select('COUNT(a.id)');

        if ($search) {
            $queryBuilder->andWhere('a.name LIKE :search OR a.serie LIKE :search OR a.modele LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($category) {
            $queryBuilder->andWhere('a.category = :category')
                ->setParameter('category', $category);
        }

        if ($status) {
            $queryBuilder->andWhere('a.status = :status')
                ->setParameter('status', $status);
        }

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * Récupère tous les actifs attribués
     */
    public function findActifsAttribues(): array
    {
        return $this->findBy(['status' => 'attribue']);
    }

    /**
     * Récupère les actifs en stock
     */
    public function findActifsEnStock(): array
    {
        return $this->findBy(['status' => 'stock']);
    }

    /**
     * Récupère les actifs en maintenance
     */
    public function findActifsEnMaintenance(): array
    {
        return $this->findBy(['status' => 'maintenance']);
    }
}
