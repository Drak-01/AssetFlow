<?php

namespace App\Domain\Attribution\Service;

use App\Domain\Attribution\Attribution;
use App\Domain\Attribution\AttributionRepository;
use App\Domain\Inventaire\ActifsRepository;
use Doctrine\ORM\EntityManagerInterface;

class revokeattribution
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AttributionRepository $attributionRepository,
        private readonly ActifsRepository $actifsRepository
    )
    {
    }

    public function revoke(Attribution $attribution): void
    {
        $actifs = $this->actifsRepository->find($attribution->getActif()->getId());

        $qte = $actifs->getQuantite();
        $actifs->setQuantite($qte + $attribution->getQuantite());

        $attribution->setStatus('retire');
        $actifs->setStatus('stock');

        $this->entityManager->persist($actifs);
        $this->entityManager->persist($attribution);
        $this->entityManager->flush();

    }
}
