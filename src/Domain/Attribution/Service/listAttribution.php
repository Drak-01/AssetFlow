<?php

namespace App\Domain\Attribution\Service;

use App\Domain\Attribution\Attribution;
use App\Domain\Attribution\AttributionRepository;

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
}
