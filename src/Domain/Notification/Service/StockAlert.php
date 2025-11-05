<?php

namespace App\Domain\Notification\Service;

use App\Domain\Inventaire\Actifs;
use App\Domain\Inventaire\ActifsRepository;
use App\Domain\Notification\Notification;
use App\Domain\Notification\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class StockAlert
{
    public function __construct(
        private ActifsRepository       $actifsRepository,
        private NotificationRepository $notificationRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function stockAlert(): void
    {
        // Récupérer tous les actifs de type logiciel avec quantité < 5
        $actifsFaibleStock = $this->actifsRepository->createQueryBuilder('a')
            ->where('a.type = :type')
            ->andWhere('a.quantite < :seuil')
            ->andWhere('a.quantite IS NOT NULL')
            ->setParameter('type', 'logiciel')
            ->setParameter('seuil', 5)
            ->getQuery()
            ->getResult();

        foreach ($actifsFaibleStock as $actif) {
            $this->createStockNotification($actif);
        }

        $this->entityManager->flush();
    }

    private function createStockNotification(Actifs $actif): void
    {
        // Vérifier si une notification existe déjà pour cet actif
        $existingNotification = $this->notificationRepository->findOneBy([
            'relatedEntity' => 'Actifs',
            'relatedId' => $actif->getId(),
            'type' => 'warning'
        ]);

        // Si aucune notification n'existe, en créer une nouvelle
        if (!$existingNotification) {
            $notification = new Notification();
            $notification->setTitle('Alerte Stock Faible');
            $notification->setMessage(
                sprintf(
                    'Le stock de "%s" est faible : %d licence(s) restante(s)',
                    $actif->getName(),
                    $actif->getQuantite()
                )
            );
            $notification->setType('warning');
            $notification->setRelatedEntity('Actifs');
            $notification->setRelatedId($actif->getId());
//            $notification->setCreatedAt(new \DateTimeImmutable());

            $this->notificationRepository->save($notification);
        }
    }

    /**
     * Vérifie les stocks et retourne le nombre d'alertes créées
     */
    public function checkAndCreateAlerts(): int
    {
        $alertesCreees = 0;

        $actifsFaibleStock = $this->actifsRepository->createQueryBuilder('a')
            ->where('a.type = :type')
            ->andWhere('a.quantite < :seuil')
            ->andWhere('a.quantite IS NOT NULL')
            ->setParameter('type', 'logiciel')
            ->setParameter('seuil', 5)
            ->getQuery()
            ->getResult();

        foreach ($actifsFaibleStock as $actif) {
            if (!$this->notificationExistsForActif($actif)) {
                $this->createStockNotification($actif);
                $alertesCreees++;
            }
        }

        $this->entityManager->flush();

        return $alertesCreees;
    }

    /**
     * Vérifie si une notification d'alerte existe déjà pour un actif
     */
    private function notificationExistsForActif(Actifs $actif): bool
    {
        $existingNotification = $this->notificationRepository->findOneBy([
            'relatedEntity' => 'Actifs',
            'relatedId' => $actif->getId(),
            'type' => 'warning'
        ]);

        return $existingNotification !== null;
    }

    /**
     * Retourne la liste des actifs avec stock faible
     */
    public function getLowStockActifs(): array
    {
        return $this->actifsRepository->createQueryBuilder('a')
            ->where('a.type = :type')
            ->andWhere('a.quantite < :seuil')
            ->andWhere('a.quantite IS NOT NULL')
            ->setParameter('type', 'logiciel')
            ->setParameter('seuil', 5)
            ->getQuery()
            ->getResult();
    }
}
