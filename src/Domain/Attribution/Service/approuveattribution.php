<?php

namespace App\Domain\Attribution\Service;

use App\Domain\Attribution\Attribution;
use App\Domain\Attribution\AttributionRepository;
use App\Domain\Inventaire\ActifsRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\This;

class approuveattribution
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AttributionRepository $attributionRepository,
        private readonly ActifsRepository $actifsRepository
    )
    {
    }

    public function approve(Attribution $attribution): void
    {

        $actif = $this->actifsRepository->find($attribution->getActif()->getId());

        $actif->setStatus('attribue');
        $attribution->setStatut('attribue');

        $attribution->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($attribution);
        $this->entityManager->flush();
    }
}
