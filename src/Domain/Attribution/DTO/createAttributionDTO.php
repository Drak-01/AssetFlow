<?php
// src/Domain/Attribution/DTO/CreateAttributionDTO.php

namespace App\Domain\Attribution\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class createAttributionDTO
{
    #[Assert\NotBlank(message: "L'utilisateur est obligatoire")]
    public ?int $utilisateur = null;

    #[Assert\NotBlank(message: "L'actif est obligatoire")]
    public ?int $actifs = null;


    #[Assert\NotBlank(message: "La date d'attribution est obligatoire")]
    public ?\DateTimeInterface $dateAttribution = null;

    #[Assert\Length(max: 1000)]
    public ?string $notes = null;

    public function __construct()
    {
        $this->dateAttribution = new \DateTimeImmutable();
    }
}
