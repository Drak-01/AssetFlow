<?php

namespace App\Domain\Inventaire\Service;

use App\Domain\Inventaire\Actifs;
use App\Domain\Inventaire\ActifsRepository;
use App\Domain\User\Utilisateur;

class listInventaire
{
    public function __construct(
        private readonly ActifsRepository $actifsRepository
    )
    {
    }

    public function listInventaire(): array
    {
        return $this->actifsRepository->findAll();
    }

    public function findByFilter(string $search,string $category, string $status,int $page, int $limit): array
    {
        return $this->actifsRepository->findByFilters($search, $category, $status, $page, $limit);
    }


    public function findby(int $limit, int $page){
        return $this->actifsRepository->findBy([], ['createdAt' => 'DESC'], $limit, $page);
    }

    public function countall(): int
    {
        return $this->actifsRepository->count();
    }

    public function countAllByStatus(string $status): int
    {
        return $this->actifsRepository->count(['status' => $status]);
    }

    public function getByStatus(string $status): array
    {
        return $this->actifsRepository->findBy(['status' => $status]);
    }

    public function getALl():array
    {
        return $this->actifsRepository->findAll();
    }

    public function getByID(int $id): actifs
    {
        return $this->actifsRepository->find($id);
    }
}
