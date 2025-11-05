<?php

namespace App\Domain\User\Service;

use App\Domain\Departement\DepartementRepository;
use App\Domain\User\DTO\CreateDTO;
use App\Domain\User\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;

class createUserService
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DepartementRepository $departementRepository,
    )
    {
    }

    public function createUser(CreateDTO $dto): void
    {
        $user = new Utilisateur();

        $user->setEmail($dto->email);
        $user->setFirstname($dto->firstname);
        $user->setLastname($dto->lastname);
        $user->setPhone($dto->phone);
        $user->setStatus($dto->status);
        $user->setMatricule($dto->matricule);

        if($dto->departements){
            $departement = $this->departementRepository->find($dto->departements);
        }
        $user->setDepartement($departement);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

}

