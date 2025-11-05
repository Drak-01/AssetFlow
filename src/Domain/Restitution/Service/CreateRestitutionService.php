<?php

namespace App\Domain\Restitution\Service;

use App\Domain\Inventaire\ActifsRepository;
use App\Domain\Restitution\DTO\CreateRestitutionDTO;
use App\Domain\Restitution\Restitution;
use App\Domain\Attribution\AttributionRepository;
use App\Domain\User\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;

class CreateRestitutionService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AttributionRepository $attributionRepository,
        private readonly UtilisateurRepository $utilisateurRepository,
        private readonly ActifsRepository $actifsRepository,
    ) {}

    public function create(CreateRestitutionDTO $dto): Restitution
    {
        // Récupérer l'attribution
        $attribution = $this->attributionRepository->find($dto->attribution);
        $actifInventaire = $this->actifsRepository->find($attribution->getActif()->getId());
        if (!$attribution) {
            throw new \RuntimeException('Attribution non trouvée');
        }

        // Créer la restitution
        $restitution = new Restitution();
        $restitution->setAttribution($attribution);
        $restitution->setEmploye($attribution->getUtilisateur());
        $restitution->setEtat($dto->etat);
        $restitution->setObservation($dto->observation);
        $restitution->setCheckList($dto->checkList);
        $restitution->setCreatedAt(new \DateTimeImmutable());

        // Mettre à jour le statut de l'attribution
        $attribution->setStatus('restitué');
        $actifInventaire->setStatus('stock');

        // Mettre à jour la quantité de l'actif
        $actif = $attribution->getActif();
//        $nouvelleQuantite = $actif->getQuantite() + $attribution->getQuantite();
//        $actif->setQuantite($nouvelleQuantite);

        // Persister
        $this->entityManager->persist($restitution);
        $this->entityManager->persist($attribution);
        $this->entityManager->persist($actifInventaire);
        $this->entityManager->persist($actif);
        $this->entityManager->flush();

        return $restitution;
    }

    private function ensureDateTimeImmutable(\DateTimeInterface $date): \DateTimeImmutable
    {
        if ($date instanceof \DateTimeImmutable) {
            return $date;
        }

        if ($date instanceof \DateTime) {
            return \DateTimeImmutable::createFromMutable($date);
        }

        throw new \InvalidArgumentException('Type de date non supporté: ' . get_class($date));
    }
}
