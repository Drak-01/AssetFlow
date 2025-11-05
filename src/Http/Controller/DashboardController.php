<?php

declare(strict_types=1);

namespace App\Http\Controller;

use App\Domain\Attribution\Service\listAttribution;
use App\Domain\Inventaire\Service\listInventaire;
use App\Domain\Restitution\Service\listRestitution;
use App\Domain\User\Service\listUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dashboard', name: 'dashboard_')]
class DashboardController extends AbstractController
{
    public function __construct(
        private readonly listInventaire $listInventaire,
        private readonly listAttribution $listAttribution,
        private readonly listRestitution $listRestitution,
        private readonly listUser $listUser,
    ) {}

    #[Route('', name: 'index')]
    public function index(): Response
    {
        $user = $this->getUser();
        $stats = $this->getDashboardStats();
        $recentActivities = $this->getRecentActivities();
        $departmentsUsage = $this->getDepartmentsUsage();
        $upcomingDeadlines = $this->getUpcomingDeadlines();
        $assetDistribution = $this->getAssetDistribution();

        return $this->render('dashboard.html.twig', [
            'stats' => $stats,
            'recentActivities' => $recentActivities,
            'departmentsUsage' => $departmentsUsage,
            'upcomingDeadlines' => $upcomingDeadlines,
            'assetDistribution' => $assetDistribution,
            'user' => $user,
        ]);
    }

    #[Route('/stats', name: 'stats', methods: ['GET'])]
    public function getStats(): JsonResponse
    {
        $stats = $this->getDashboardStats();
        return $this->json($stats);
    }

    #[Route('/activities', name: 'activities', methods: ['GET'])]
    public function getActivities(): JsonResponse
    {
        $activities = $this->getRecentActivities();
        return $this->json($activities);
    }

    private function getDashboardStats(): array
    {

        $actifs = $this->listInventaire->getALl();
        $attributions = $this->listAttribution->getAttributions();
        $restitutions = $this->listRestitution->lists();

        $totalActifs = count($actifs);
        $actifsAttribues = count(array_filter($attributions, fn($a) => $a->getStatus() === 'active'));
        $enRestitution = count(array_filter($restitutions, fn($r) => $r->getEtat() === 'pending'));

        $tauxAttribution = $totalActifs > 0 ? round(($actifsAttribues / $totalActifs) * 100) : 0;
        $tauxDisponibilite = 100 - $tauxAttribution;
        $actifsDisponibles = $totalActifs - $actifsAttribues;

        // Calcul de la croissance (simplifié)
        $actifsGrowth = 12; // À remplacer par une logique réelle de comparaison mensuelle

        // Restitutions en retard
        $restitutionEnRetard = count(array_filter($restitutions, function($restitution) {
            $dateLimite = clone $restitution->getCreatedAt();
            $dateLimite->modify('+7 days');
            return $dateLimite < new \DateTime() && $restitution->getEtat() === 'pending';
        }));

        return [
            'totalActifs' => $totalActifs,
            'actifsAttribues' => $actifsAttribues,
            'enRestitution' => $enRestitution,
            'tauxAttribution' => $tauxAttribution,
            'tauxDisponibilite' => $tauxDisponibilite,
            'actifsDisponibles' => $actifsDisponibles,
            'actifsGrowth' => $actifsGrowth,
            'restitutionEnRetard' => $restitutionEnRetard,
        ];
    }

    private function getRecentActivities(): array
    {
        $attributions = $this->listAttribution->listRecent(5);
        $restitutions = $this->listRestitution->listRecent(5);

        $activities = [];

        // Convertir les attributions récentes en activités
        foreach ($attributions as $attribution) {
            $activities[] = [
                'id' => $attribution->getId(),
                'type' => 'attribution',
                'title' => 'Nouvelle attribution',
                'description' => $attribution->getActif()->getName() . ' attribué à ' . $attribution->getUtilisateur()->getFirstname() . ' ' . $attribution->getUtilisateur()->getLastname(),
                'time' => $this->getTimeAgo($attribution->getDateAttribution()),
                'timestamp' => $attribution->getDateAttribution()->getTimestamp(),
            ];
        }

        // Convertir les restitutions récentes en activités
        foreach ($restitutions as $restitution) {
            $activities[] = [
                'id' => $restitution->getId(),
                'type' => 'restitution',
                'title' => 'Restitution complétée',
                'description' => $restitution->getAttribution()->getActif()->getName() . ' restitué par ' . $restitution->getEmploye()->getFirstname() . ' ' . $restitution->getEmploye()->getLastname(),
                'time' => $this->getTimeAgo($restitution->getCreatedAt()),
                'timestamp' => $restitution->getCreatedAt()->getTimestamp(),
            ];
        }

        // Trier par timestamp et prendre les 5 plus récentes
        usort($activities, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);
        return array_slice($activities, 0, 5);
    }

    private function getDepartmentsUsage(): array
    {
        $users = $this->listUser->list();
        $attributions = $this->listAttribution->getAttributions();

        $departments = [];

        // Compter les attributions par département
        foreach ($attributions as $attribution) {
            $user = $attribution->getUtilisateur();
            $deptName = $user->getDepartement() ? $user->getDepartement()->getNom() : 'Non spécifié';

            if (!isset($departments[$deptName])) {
                $departments[$deptName] = [
                    'name' => $deptName,
                    'initials' => $this->getInitials($deptName),
                    'actifs' => 0,
                    'totalUsers' => 0,
                ];
            }
            $departments[$deptName]['actifs']++;
        }

        // Compter les utilisateurs par département
        foreach ($users as $user) {
            $deptName = $user->getDepartement() ? $user->getDepartement()->getNom() : 'Non spécifié';

            if (!isset($departments[$deptName])) {
                $departments[$deptName] = [
                    'name' => $deptName,
                    'initials' => $this->getInitials($deptName),
                    'actifs' => 0,
                    'totalUsers' => 0,
                ];
            }
            $departments[$deptName]['totalUsers']++;
        }

        // Calculer le pourcentage d'utilisation
        $totalActifs = count($attributions);
        foreach ($departments as &$dept) {
            $dept['usage'] = $totalActifs > 0 ? round(($dept['actifs'] / $totalActifs) * 100) : 0;
        }

        return array_values($departments);
    }

    private function getUpcomingDeadlines(): array
    {
        $restitutions = $this->listRestitution->lists();
        $deadlines = [];

        foreach ($restitutions as $restitution) {
            if ($restitution->getEtat() === 'pending') {
                $createdAt = $restitution->getCreatedAt();
                $dueDate = clone $createdAt;
                $dueDate->modify('+7 days');
                $now = new \DateTime();
                $daysLeft = $dueDate->diff($now)->days;

                if ($daysLeft <= 7) { // Seulement les échéances dans les 7 prochains jours
                    $priority = $daysLeft <= 2 ? 'high' : ($daysLeft <= 4 ? 'medium' : 'low');

                    $deadlines[] = [
                        'id' => $restitution->getId(),
                        'title' => 'Restitution ' . $restitution->getAttribution()->getActif()->getName(),
                        'type' => 'Restitution',
                        'date' => $dueDate->format('d/m/Y'),
                        'daysLeft' => $daysLeft === 0 ? 'Aujourd\'hui' : "Dans {$daysLeft} jour(s)",
                        'priority' => $priority,
                    ];
                }
            }
        }

        // Trier par priorité et date
        usort($deadlines, function($a, $b) {
            $priorityOrder = ['high' => 1, 'medium' => 2, 'low' => 3];
            if ($priorityOrder[$a['priority']] !== $priorityOrder[$b['priority']]) {
                return $priorityOrder[$a['priority']] <=> $priorityOrder[$b['priority']];
            }
            return strtotime($a['date']) <=> strtotime($b['date']);
        });

        return array_slice($deadlines, 0, 5);
    }

    private function getAssetDistribution(): array
    {
        $actifs = $this->listInventaire->getALl();
        $distribution = [];

        foreach ($actifs as $actif) {
            $type = $actif->getModele() ?? 'Autre';

            if (!isset($distribution[$type])) {
                $distribution[$type] = 0;
            }
            $distribution[$type]++;
        }

        return $distribution;
    }

    private function getTimeAgo(\DateTimeInterface $date): string
    {
        $now = new \DateTime();
        $diff = $now->diff($date);

        if ($diff->y > 0) {
            return $diff->y > 1 ? "Il y a {$diff->y} ans" : "Il y a 1 an";
        } elseif ($diff->m > 0) {
            return $diff->m > 1 ? "Il y a {$diff->m} mois" : "Il y a 1 mois";
        } elseif ($diff->d > 0) {
            return $diff->d > 1 ? "Il y a {$diff->d} jours" : "Hier";
        } elseif ($diff->h > 0) {
            return $diff->h > 1 ? "Il y a {$diff->h} heures" : "Il y a 1 heure";
        } elseif ($diff->i > 0) {
            return $diff->i > 1 ? "Il y a {$diff->i} minutes" : "Il y a 1 minute";
        } else {
            return "À l'instant";
        }
    }

    private function getInitials(string $departmentName): string
    {
        $words = explode(' ', $departmentName);
        $initials = '';

        foreach ($words as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
        }

        return substr($initials, 0, 2);
    }

}
