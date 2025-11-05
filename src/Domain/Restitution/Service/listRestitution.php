<?php

namespace App\Domain\Restitution\Service;

use App\Domain\Restitution\RestitutionRepository;

readonly class listRestitution
{
    public function __construct(
        private RestitutionRepository $restitutionRepository,
    ) {}

    public function lists(): array
    {
        return $this->restitutionRepository->findAll();
    }

    public function stats(): array
    {
        $restitutions = $this->restitutionRepository->findAll();
        $now = new \DateTime();
        $debutMois = new \DateTime('first day of this month');

        // Restitutions ce mois
        $restitutionsCeMois = array_filter($restitutions, function($restitution) use ($debutMois) {
            return $restitution->getCreatedAt() >= $debutMois;
        });

        // En attente - basé uniquement sur l'état
        $enAttente = array_filter($restitutions, function($restitution) {
            return $restitution->getEtat() === 'pending';
        });

        // En retard
        $enRetard = array_filter($restitutions, function($restitution) use ($now) {
            $dateLimite = clone $restitution->getCreatedAt();
            $dateLimite->modify('+7 days');
            return $dateLimite < $now && $restitution->getEtat() === 'pending';
        });

        // Répartition par état
        $etats = ['excellent', 'bon', 'moyen', 'mauvais', 'hors_service'];
        $repartitionEtat = [];
        foreach ($etats as $etat) {
            $repartitionEtat[$etat] = count(array_filter($restitutions, function($restitution) use ($etat) {
                return $restitution->getEtat() === $etat;
            }));
        }

        // Calcul du taux de retour (version simplifiée)
        $totalRestitutions = count($restitutions);
        $restitutionsTerminees = array_filter($restitutions, function($restitution) {
            return $restitution->getEtat() !== 'pending';
        });

        $tauxRetour = $totalRestitutions > 0 ?
            round((count($restitutionsTerminees) / $totalRestitutions) * 100) : 0;

        return [
            'ceMois' => count($restitutionsCeMois),
            'enAttente' => count($enAttente),
            'enRetard' => count($enRetard),
            'tauxRetour' => $tauxRetour . '%',
            'totalRestitutions' => $totalRestitutions,
            'repartitionEtat' => $repartitionEtat,
        ];
    }

    public function listByEtat(string $etat): array
    {
        return $this->restitutionRepository->findBy(['etat' => $etat]);
    }

    public function listRecent(int $limit = 10): array
    {
        return $this->restitutionRepository->findBy(
            [],
            ['createdAt' => 'DESC'],
            $limit
        );
    }

    // Méthode pour les restitutions avec problèmes
    public function listWithProblems(): array
    {
        return array_filter($this->restitutionRepository->findAll(), function($restitution) {
            return $restitution->getEtat() === 'mauvais' ||
                $restitution->getEtat() === 'hors_service';
        });
    }
}
