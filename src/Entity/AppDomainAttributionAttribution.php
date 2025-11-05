<?php

namespace App\Entity;

use App\Repository\AppDomainAttributionAttributionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AppDomainAttributionAttributionRepository::class)]
class AppDomainAttributionAttribution
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
