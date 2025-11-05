<?php

namespace App\Domain\User\Service;

use App\Domain\User\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;

class deleteService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    )
    {
    }

    public function delete(Utilisateur $utilisateur)
    {
        $this->entityManager->remove($utilisateur);
        $this->entityManager->flush();
    }
}
