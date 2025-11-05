<?php

namespace App\Domain\Attribution\Service;

use App\Domain\Attribution\Attribution;
use App\Domain\Attribution\AttributionRepository;
use App\Domain\Attribution\DTO\createAttributionDTO;
use App\Domain\Inventaire\ActifsRepository;
use App\Domain\Inventaire\Service\listInventaire;
use App\Domain\User\Service\listUser;
use Doctrine\ORM\EntityManagerInterface;
use function PHPUnit\Framework\throwException;

class createAttribution
{
    public function __construct(
        private readonly AttributionRepository $attributionRepository,
        private readonly listUser $listUser,
        private readonly listInventaire $listInventaire,
        private readonly EntityManagerInterface $entityManager
    )
    {
    }

    public function create(createAttributionDTO $dto): Attribution
    {
        $attribution = new Attribution();
        // Récupérer l'utilisateur
        $user = $this->listUser->getById($dto->utilisateur);
        $actif = $this->listInventaire->getById($dto->actifs);
        $actif->setStatus('pending');

        if (!$user || !$actif) {
            throw new \RuntimeException('Utilisateur or actif non trouvé');
        }

        if($actif->getType() == 'logiciel'){
            $qte = $actif->getQuantite() - $dto->quantite;
            if($qte < 0){
                throw new \RuntimeException('La quantité insuffisante');
            }
            $attribution->setQuantite($qte);

        }

        $attribution->setQuantite($dto->quantite);
        $attribution->setType($actif->getType());
        $attribution->setactif($actif);
        $attribution->setUtilisateur($user);
        $attribution->setStatus('pending');

//        if (is_array($dto->actifs)) {
//            foreach ($dto->actifs as $actifId) {
//                $actif = $this->listInventaire->getByID($actifId);
//                if ($actif) {
//                    $attribution->setActif($actif);
//                }
//            }
//        } else {
//            // Si c'est un entier (un seul actif)
//            $actif = $this->listInventaire->getByID((int)$dto->actifs);
//            if ($actif) {
//                $attribution->setActif($actif);
//            }
//        }

        $dateAttribution = $dto->dateAttribution;
        if ($dateAttribution instanceof \DateTime) {
            $dateAttribution = \DateTimeImmutable::createFromMutable($dateAttribution);
        }
        $attribution->setDateAttribution($dateAttribution);

        $attribution->setNotes($dto->notes);
//        $attribution->setStatut('active');
        $attribution->setDateAttribution(new \DateTimeImmutable());

        // Persister l'entité
        $this->entityManager->persist($attribution);
        $this->entityManager->flush();

        return $attribution;
    }
}
