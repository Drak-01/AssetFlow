<?php
// src/Twig/AppExtension.php

namespace App\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_actif_icon', [$this, 'getActifIcon']),
            new TwigFunction('get_actif_status_config', [$this, 'getActifStatusConfig']),
            new TwigFilter('ago', [$this, 'getTimeAgo']),
        ];
    }

    public function getActifIcon(?string $category): array
    {
        $config = [
            'ordinateur_portable' => [
                'icon' => 'fas fa-laptop',
                'bgColor' => 'bg-blue-100',
                'textColor' => 'text-blue-600'
            ],
            'ordinateur_bureau' => [
                'icon' => 'fas fa-desktop',
                'bgColor' => 'bg-indigo-100',
                'textColor' => 'text-indigo-600'
            ],
            'telephone' => [
                'icon' => 'fas fa-mobile-alt',
                'bgColor' => 'bg-green-100',
                'textColor' => 'text-green-600'
            ],
            'tablette' => [
                'icon' => 'fas fa-tablet-alt',
                'bgColor' => 'bg-purple-100',
                'textColor' => 'text-purple-600'
            ],
            'ecran' => [
                'icon' => 'fas fa-tv',
                'bgColor' => 'bg-yellow-100',
                'textColor' => 'text-yellow-600'
            ],
            'imprimante' => [
                'icon' => 'fas fa-print',
                'bgColor' => 'bg-red-100',
                'textColor' => 'text-red-600'
            ],
            'logiciel' => [
                'icon' => 'fas fa-cog',
                'bgColor' => 'bg-gray-100',
                'textColor' => 'text-gray-600'
            ],
            'licence' => [
                'icon' => 'fas fa-key',
                'bgColor' => 'bg-orange-100',
                'textColor' => 'text-orange-600'
            ],
            'serveur' => [
                'icon' => 'fas fa-server',
                'bgColor' => 'bg-pink-100',
                'textColor' => 'text-pink-600'
            ],
            'reseau' => [
                'icon' => 'fas fa-network-wired',
                'bgColor' => 'bg-teal-100',
                'textColor' => 'text-teal-600'
            ],
            'peripherique' => [
                'icon' => 'fas fa-keyboard',
                'bgColor' => 'bg-cyan-100',
                'textColor' => 'text-cyan-600'
            ],
            'default' => [
                'icon' => 'fas fa-box',
                'bgColor' => 'bg-gray-100',
                'textColor' => 'text-gray-600'
            ]
        ];

        return $config[$category] ?? $config['default'];
    }

    public function getActifStatusConfig(?string $status): array
    {
        $config = [
            'actif' => [
                'class' => 'bg-green-100 text-green-800',
                'icon' => 'fas fa-circle',
                'label' => 'Actif'
            ],
            'inactif' => [
                'class' => 'bg-gray-100 text-gray-800',
                'icon' => 'fas fa-circle',
                'label' => 'Inactif'
            ],
            'maintenance' => [
                'class' => 'bg-yellow-100 text-yellow-800',
                'icon' => 'fas fa-tools',
                'label' => 'En Réparation'
            ],
            'stock' => [
                'class' => 'bg-blue-100 text-blue-800',
                'icon' => 'fas fa-warehouse',
                'label' => 'En Stock'
            ],
            'attribue' => [
                'class' => 'bg-purple-100 text-purple-800',
                'icon' => 'fas fa-user-check',
                'label' => 'Attribué'
            ],
            'retire' => [
                'class' => 'bg-red-100 text-red-800',
                'icon' => 'fas fa-times-circle',
                'label' => 'Retiré'
            ],
            'default' => [
                'class' => 'bg-gray-100 text-gray-800',
                'icon' => 'fas fa-circle',
                'label' => 'Non défini'
            ]
        ];

        return $config[$status] ?? $config['default'];
    }

    public function getTimeAgo(\DateTimeInterface $date): string
    {
        $now = new \DateTime();
        $diff = $now->diff($date);

        if ($diff->y > 0) {
            return $diff->y . ' an' . ($diff->y > 1 ? 's' : '');
        } elseif ($diff->m > 0) {
            return $diff->m . ' mois';
        } elseif ($diff->d > 0) {
            return $diff->d . ' jour' . ($diff->d > 1 ? 's' : '');
        } elseif ($diff->h > 0) {
            return $diff->h . ' heure' . ($diff->h > 1 ? 's' : '');
        } elseif ($diff->i > 0) {
            return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '');
        } else {
            return 'À l\'instant';
        }
    }
}
