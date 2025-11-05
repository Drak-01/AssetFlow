<?php

namespace App\Domain\Attribution;

use App\Domain\Inventaire\Actifs;
use App\Domain\User\Utilisateur;
use App\Domain\Attribution\AttributionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AttributionRepository::class)]
#[ORM\Table(name: 'attributions')]
class Attribution
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'attributions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(targetEntity: Actifs::class, inversedBy: 'attributions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Actifs $actif = null;


    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $dateAttribution = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateFin = null;

    #[ORM\Column(length: 20)]
    private ?string $statut = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $assignePar = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->dateAttribution = new \DateTimeImmutable();
        $this->statut = 'pending';
    }

    // Getters et Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    public function getActif(): ?Actifs
    {
        return $this->actif;
    }

    public function setActif(?Actifs $actif): static
    {
        $this->actif = $actif;
        return $this;
    }


    public function getDateAttribution(): ?\DateTimeImmutable
    {
        return $this->dateAttribution;
    }

    public function setDateAttribution(\DateTimeImmutable $dateAttribution): static
    {
        $this->dateAttribution = $dateAttribution;
        return $this;
    }

    public function getDateFin(): ?\DateTimeImmutable
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTimeImmutable $dateFin): static
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
        return $this;
    }

    public function getAssignePar(): ?string
    {
        return $this->assignePar;
    }

    public function setAssignePar(?string $assignePar): static
    {
        $this->assignePar = $assignePar;
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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    // Méthodes utilitaires
    public function isActive(): bool
    {
        return $this->statut === 'active';
    }

    public function isPending(): bool
    {
        return $this->statut === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->statut === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->statut === 'cancelled';
    }

    public function activate(): static
    {
        $this->statut = 'active';
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function complete(): static
    {
        $this->statut = 'completed';
        $this->dateFin = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function cancel(): static
    {
        $this->statut = 'cancelled';
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getDuree(): string
    {
        $endDate = $this->dateFin ?? new \DateTimeImmutable();
        $interval = $this->dateAttribution->diff($endDate);

        if ($interval->y > 0) {
            return $interval->format('%y an(s) %m mois');
        } elseif ($interval->m > 0) {
            return $interval->format('%m mois %d jour(s)');
        } else {
            return $interval->format('%d jour(s)');
        }
    }

    // Méthode pour créer une nouvelle attribution
    public static function create(Utilisateur $utilisateur, Actifs $actif, ?string $notes = null): self
    {
        $attribution = new self();
        $attribution->setUtilisateur($utilisateur);
        $attribution->setActif($actif);
        $attribution->setNotes($notes);

        return $attribution;
    }
}
