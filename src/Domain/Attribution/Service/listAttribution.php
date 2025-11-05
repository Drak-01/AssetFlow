<?php

namespace App\Domain\Attribution\Service;

use App\Domain\Attribution\Attribution;
use App\Domain\Attribution\AttributionRepository;
use App\Domain\Departement\Departement;

class listAttribution
{
    public function __construct(
        private readonly AttributionRepository $attributionRepository
    )
    {
    }

    public function getAttributions(): array
    {
        return $this->attributionRepository->findAll();
    }

    public function list_Attribuer(string $status):array
    {
        return $this->attributionRepository->findBy(['status' => $status]);
    }

    public function listRecent(int $taille): array
    {
        return $this->attributionRepository->findBy(
            [],
            ['createdAt' => 'DESC'], // tri par date décroissante
            $taille,
        );
    }

}
