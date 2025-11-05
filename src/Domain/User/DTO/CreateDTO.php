<?php

namespace App\Domain\User\DTO;

class CreateDTO
{
    public string $matricule;
    public string $email;
    public string $firstname;
    public string $lastname;
    public string $phone;
    public string $status; // Changer de array à string
    public string $departements; // Changer de array à string et corriger le nom
}
