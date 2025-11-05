<?php

namespace App\Domain\Departement\Service;

use App\Domain\Departement\Departement;
use App\Domain\Departement\DepartementRepository;

class getDepartement
{
    public function __construct(
        private DepartementRepository $departementRepository,
    )
    {
    }

    public function getDepartement(): array
    {
        return $this->departementRepository->findAll();
    }

}
