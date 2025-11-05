<?php

namespace App\Domain\Restitution;

use App\Domain\Attribution\Attribution;
use App\Domain\User\Utilisateur;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RestitutionRepository::class)]
class Restitution
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Attribution $attribution = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255)]
    private ?string $etat = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $observation = null;

    #[ORM\ManyToOne(inversedBy: 'restitutions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $employe = null;


    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $checkList = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAttribution(): ?Attribution
    {
        return $this->attribution;
    }

    public function setAttribution(?Attribution $attribution): static
    {
        $this->attribution = $attribution;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    public function getObservation(): ?string
    {
        return $this->observation;
    }

    public function setObservation(?string $observation): static
    {
        $this->observation = $observation;

        return $this;
    }

    public function getEmploye(): ?Utilisateur
    {
        return $this->employe;
    }

    public function setEmploye(?Utilisateur $employe): static
    {
        $this->employe = $employe;

        return $this;
    }

    public function getCheckList(): ?array
    {
        return $this->checkList;
    }

    public function setCheckList(?array $checkList): static
    {
        $this->checkList = $checkList;

        return $this;
    }

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

}
