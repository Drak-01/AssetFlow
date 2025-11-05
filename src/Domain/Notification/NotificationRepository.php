<?php

declare(strict_types=1);

namespace App\Domain\Notification;

use App\Domain\Notification\Notification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    /**
     * Trouve les notifications non lues
     */
    public function findUnread(): array
    {
        return $this->createQueryBuilder('n')
            ->where('n.read = :read')
            ->setParameter('read', false)
            ->orderBy('n.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les notifications par type
     */
    public function findByType(string $type): array
    {
        return $this->createQueryBuilder('n')
            ->where('n.type = :type')
            ->setParameter('type', $type)
            ->orderBy('n.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre de notifications non lues
     */
    public function countUnread(): int
    {
        return (int) $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->where('n.read = :read')
            ->setParameter('read', false)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Trouve les notifications récentes (derniers 7 jours)
     */
    public function findRecent(int $days = 7): array
    {
        $date = new \DateTimeImmutable(sprintf('-%d days', $days));

        return $this->createQueryBuilder('n')
            ->where('n.createdAt >= :date')
            ->setParameter('date', $date)
            ->orderBy('n.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les notifications liées à une entité spécifique
     */
    public function findByRelatedEntity(string $entity, int $entityId): array
    {
        return $this->createQueryBuilder('n')
            ->where('n.relatedEntity = :entity')
            ->andWhere('n.relatedId = :entityId')
            ->setParameter('entity', $entity)
            ->setParameter('entityId', $entityId)
            ->orderBy('n.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Marque toutes les notifications comme lues
     */
    public function markAllAsRead(): int
    {
        return $this->createQueryBuilder('n')
            ->update()
            ->set('n.read', ':read')
            ->where('n.read = :unread')
            ->setParameter('read', true)
            ->setParameter('unread', false)
            ->getQuery()
            ->execute();
    }

    /**
     * Supprime les notifications anciennes (plus de 30 jours)
     */
    public function deleteOldNotifications(int $days = 30): int
    {
        $date = new \DateTimeImmutable(sprintf('-%d days', $days));

        return $this->createQueryBuilder('n')
            ->delete()
            ->where('n.createdAt < :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->execute();
    }

    /**
     * Trouve les notifications avec pagination
     */
    public function findPaginated(int $page = 1, int $limit = 10): array
    {
        $offset = ($page - 1) * $limit;

        return $this->createQueryBuilder('n')
            ->orderBy('n.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte les notifications par type
     */
    public function countByType(): array
    {
        return $this->createQueryBuilder('n')
            ->select('n.type, COUNT(n.id) as count')
            ->groupBy('n.type')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les alertes stock non lues
     */
    public function findUnreadStockAlerts(): array
    {
        return $this->createQueryBuilder('n')
            ->where('n.read = :read')
            ->andWhere('n.type = :type')
            ->andWhere('n.relatedEntity = :entity')
            ->setParameter('read', false)
            ->setParameter('type', 'warning')
            ->setParameter('entity', 'Actifs')
            ->orderBy('n.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Vérifie si une notification d'alerte stock existe déjà pour un actif
     */
    public function stockAlertExists(int $actifId): bool
    {
        $result = $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->where('n.relatedEntity = :entity')
            ->andWhere('n.relatedId = :actifId')
            ->andWhere('n.type = :type')
            ->andWhere('n.read = :read')
            ->setParameter('entity', 'Actifs')
            ->setParameter('actifId', $actifId)
            ->setParameter('type', 'warning')
            ->setParameter('read', false)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $result > 0;
    }

    /**
     * Trouve les notifications avec recherche
     */
    public function search(string $query): array
    {
        return $this->createQueryBuilder('n')
            ->where('n.title LIKE :query')
            ->orWhere('n.message LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('n.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // Save and remove methods for convenience
    public function save(Notification $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Notification $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
