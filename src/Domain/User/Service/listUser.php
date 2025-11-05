<?php

namespace App\Domain\User\Service;

use App\Domain\User\Utilisateur;
use App\Domain\User\UtilisateurRepository;

class listUser
{
    public function __construct(
        private readonly UtilisateurRepository $utilisateurRepository,
    )
    {
    }

    public function list():array
    {
        return $this->utilisateurRepository->findAll();
    }

    public function active():int
    {
        return $this->utilisateurRepository->count(['status' => 'active']);
    }

    public function inactive():int
    {
        return  $this->utilisateurRepository->count(['status' => 'pending']);
    }

    public function getById(int $id): Utilisateur
    {
        return $this->utilisateurRepository->find($id);
    }
}
