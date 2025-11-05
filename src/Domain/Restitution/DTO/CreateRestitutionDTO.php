<?php

namespace App\Domain\Restitution\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CreateRestitutionDTO
{
    #[Assert\NotBlank(message: "L'attribution est obligatoire")]
    public ?int $attribution = null;

    #[Assert\NotBlank(message: "L'état de l'équipement est obligatoire")]
    #[Assert\Choice(
        choices: ['excellent', 'bon', 'moyen', 'mauvais', 'hors_service'],
        message: "L'état doit être parmi : excellent, bon, moyen, mauvais, hors_service"
    )]
    public ?string $etat = null;

    #[Assert\Length(max: 1000)]
    public ?string $observation = null;

    #[Assert\Type(type: 'array')]
    public ?array $checkList = [];

    #[Assert\NotBlank(message: "La date de restitution est obligatoire")]
    public ?\DateTimeInterface $dateRestitution = null;

    #[Assert\Type(type: 'bool')]
    public ?bool $equipementComplet = true;

    #[Assert\Type(type: 'bool')]
    public ?bool $rayures = false;

    #[Assert\Type(type: 'bool')]
    public ?bool $fonctionnel = true;

    #[Assert\Length(max: 500)]
    public ?string $notesTechnicien = null;

    public function __construct()
    {
        $this->dateRestitution = new \DateTimeImmutable();
        $this->checkList = [];
        $this->equipementComplet = true;
        $this->fonctionnel = true;
        $this->rayures = false;
    }
}
