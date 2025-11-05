<?php

namespace App\Domain\Attribution\Service;

use App\Domain\Attribution\Attribution;
use App\Domain\Attribution\AttributionRepository;
use App\Domain\Inventaire\ActifsRepository;
use App\Domain\Notification\Service\AddNotificationService;
use Doctrine\ORM\EntityManagerInterface;

readonly class approuveattribution
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AttributionRepository  $attributionRepository,
        private ActifsRepository       $actifsRepository,
        private AddNotificationService $addNotificationService,
    ) {
    }

    public function approve(Attribution $attribution): void
    {
        // Récupérer l'actif avec vérification
        $actif = $attribution->getActif();
        if (!$actif) {
            throw new \InvalidArgumentException("Aucun actif associé à cette attribution");
        }

        // Vérifier si l'utilisateur existe
        $utilisateur = $attribution->getUtilisateur();
        if (!$utilisateur) {
            throw new \InvalidArgumentException("Aucun utilisateur associé à cette attribution");
        }

        $message = "L'actif " . $actif->getName() . " a été attribué à " . $utilisateur->getFirstname();

        // Créer la notification
        $this->addNotificationService->createStockAlertNotification(
            $actif->getId(),
            $actif->getName(),
            $attribution->getQuantite(),
            $message
        );

        // Mettre à jour les statuts
        $actif->setStatus('attribue');
        $attribution->setStatus('attribue');
        $attribution->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($attribution);
        $this->entityManager->flush();
    }
}
