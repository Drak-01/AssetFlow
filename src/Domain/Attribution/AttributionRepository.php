<?php

namespace App\Domain\Attribution;

use App\Domain\Attribution\Attribution;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AttributionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Attribution::class);
    }

    public function findActiveAttributions(): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.statut = :statut')
            ->setParameter('statut', 'active')
            ->orderBy('a.dateAttribution', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findAttributionsByUser(int $userId): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.utilisateur = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('a.dateAttribution', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findAttributionsByActif(int $actifId): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.actif = :actifId')
            ->setParameter('actifId', $actifId)
            ->orderBy('a.dateAttribution', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
