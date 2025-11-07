<?php
// src/DataFixtures/AppFixtures.php

namespace App\DataFixtures;

use App\Domain\Departement\Departement;
use App\Domain\Inventaire\Actifs;
use App\Domain\User\Utilisateur;
use App\Domain\User\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR'); // Déplacer Faker ici

// 1. Création des utilisateurs
        $users = [
            [
                'email' => 'admin@assetflow.com',
                'password' => 'admin123',
                'roles' => ['ROLE_ADMIN']
            ],
            [
                'email' => 'user@assetflow.com',
                'password' => 'user123',
                'roles' => ['ROLE_USER']
            ],
            [
                'email' => 'dsi@assetflow.com',
                'password' => 'dsi123',
                'roles' => ['ROLE_ADMIN', 'ROLE_DSI']
            ]
        ];

        foreach ($users as $userData) {
            $user = new User();
            $user->setEmail($userData['email']);
            $user->setRoles($userData['roles']);
            $user->setPassword(
                $this->passwordHasher->hashPassword($user, $userData['password'])
            );

            $manager->persist($user);
            $this->addReference('user_' . explode('@', $userData['email'])[0], $user);
        }

        $manager->flush();

        $departments = [
            [
                'nom' => 'Direction Générale',
                'code' => 'DG',
                'responsable' => 'Pierre Martin',
                'email' => 'pierre.martin@entreprise.com'
            ],
            [
                'nom' => 'Ressources Humaines',
                'code' => 'RH',
                'responsable' => 'Sophie Bernard',
                'email' => 'sophie.bernard@entreprise.com'
            ],
            [
                'nom' => 'Informatique',
                'code' => 'IT',
                'responsable' => 'Marc Dubois',
                'email' => 'marc.dubois@entreprise.com'
            ],
            [
                'nom' => 'Marketing',
                'code' => 'MKT',
                'responsable' => 'Julie Lambert',
                'email' => 'julie.lambert@entreprise.com'
            ],
            [
                'nom' => 'Commercial',
                'code' => 'COM',
                'responsable' => 'Thomas Petit',
                'email' => 'thomas.petit@entreprise.com'
            ],
            [
                'nom' => 'Finance',
                'code' => 'FIN',
                'responsable' => 'Nicolas Moreau',
                'email' => 'nicolas.moreau@entreprise.com'
            ],
            [
                'nom' => 'Production',
                'code' => 'PROD',
                'responsable' => 'David Leroy',
                'email' => 'david.leroy@entreprise.com'
            ],
            [
                'nom' => 'Recherche et Développement',
                'code' => 'R&D',
                'responsable' => 'Laura Simon',
                'email' => 'laura.simon@entreprise.com'
            ],
            [
                'nom' => 'Support Client',
                'code' => 'SUP',
                'responsable' => 'Christine Robert',
                'email' => 'christine.robert@entreprise.com'
            ],
            [
                'nom' => 'Administration',
                'code' => 'ADM',
                'responsable' => 'Michel Durand',
                'email' => 'michel.durand@entreprise.com'
            ]
        ];

        foreach ($departments as $departmentData) {
            $department = new Departement();
            $department->setNom($departmentData['nom']);
            $department->setCode($departmentData['code']);
            $department->setResponsable($departmentData['responsable']);

            // Utiliser setEmail au lieu de setResponsableEmail (ou adapter selon votre entité)
            if (method_exists($department, 'setEmail')) {
                $department->setEmail($departmentData['email']);
            } elseif (method_exists($department, 'setResponsableEmail')) {
                $department->setResponsableEmail($departmentData['email']);
            }

            $department->setCreatedAt(new \DateTimeImmutable());

            $manager->persist($department);

            // Ajouter une référence pour usage dans d'autres fixtures
            $this->addReference('department_' . $departmentData['code'], $department);
        }

        $manager->flush();
        //Utilisateur
        $user = new Utilisateur();
        $user->setEmail('mister');

        // Catégories disponibles
        $categories = [
            'ordinateur_portable',
            'ordinateur_bureau',
            'telephone',
            'tablette',
            'ecran',
            'imprimante',
            'serveur',
            'reseau',
            'peripherique',
            'logiciel',
            'licence',
            'materiel'
        ];

        // Statuts possibles
        $statuts = ['stock'];

        // Génération de 12 actifs
        for ($i = 1; $i <= 12; $i++) {
            $actif = new Actifs();

            // Déterminer si c'est un logiciel ou matériel
            $isLogiciel = $i % 3 === 0; // Un tiers seront des logiciels

            if ($isLogiciel) {
                $this->createLogiciel($actif, $i, $faker, $categories, $statuts);
            } else {
                $this->createMateriel($actif, $i, $faker, $categories, $statuts);
            }

            $manager->persist($actif);
        }

        $manager->flush();
    }

    private function createMateriel(Actifs $actif, int $index, $faker, array $categories, array $statuts): void
    {
        $typesMateriel = [
            'ordinateur_portable' => ['Dell Latitude', 'HP EliteBook', 'Lenovo ThinkPad', 'MacBook Pro'],
            'ordinateur_bureau' => ['Dell OptiPlex', 'HP ProDesk', 'Lenovo ThinkCentre'],
            'telephone' => ['iPhone 14', 'Samsung Galaxy S23', 'Google Pixel 7'],
            'tablette' => ['iPad Pro', 'Samsung Galaxy Tab', 'Microsoft Surface Pro'],
            'ecran' => ['Dell UltraSharp', 'HP E-series', 'Samsung Business'],
            'imprimante' => ['HP LaserJet', 'Canon PIXMA', 'Epson WorkForce']
        ];

        $category = $faker->randomElement(array_keys($typesMateriel));
        $modeles = $typesMateriel[$category];
        $modele = $faker->randomElement($modeles);

        $actif->setName("{$modele} {$faker->randomNumber(4)}");
        $actif->setSlug($this->generateSlug($actif->getName()));
        $actif->setSerie('SN' . $faker->unique()->numerify('MAT########'));
        $actif->setModele($modele);
        $actif->setStatus($faker->randomElement($statuts));
        $actif->setCategory($category);
        // AJOUT DE LA LOGIQUE TYPE ET QUANTITE
        $actif->setType('materiel');
        $actif->setQuantite($faker->numberBetween(5, 50));
        $actif->setCreatedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-2 years', '-1 month')));
        $actif->setUpdatedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 month', 'now')));
    }

    private function createLogiciel(Actifs $actif, int $index, $faker, array $categories, array $statuts): void
    {
        $logiciels = [
            ['name' => 'Microsoft Windows 11 Professionnel', 'category' => 'logiciel'],
            ['name' => 'Microsoft Office 365 Entreprise', 'category' => 'licence'],
            ['name' => 'Adobe Creative Cloud', 'category' => 'licence'],
            ['name' => 'Symantec Endpoint Protection', 'category' => 'logiciel'],
        ];

        // Correction de l'index pour éviter l'erreur "division by zero"
        $logicielIndex = (int)(($index / 3) - 1);
        $logicielIndex = max(0, min($logicielIndex, count($logiciels) - 1));
        $logiciel = $logiciels[$logicielIndex];

        $actif->setName($logiciel['name']);
        $actif->setSlug($this->generateSlug($actif->getName()));
        $actif->setSerie(null);
        $actif->setModele('Édition Standard');
        $actif->setStatus($faker->randomElement($statuts));
        $actif->setCategory($logiciel['category']);
        // AJOUT DE LA LOGIQUE TYPE ET QUANTITE
        $actif->setType('logiciel');
        $actif->setQuantite($faker->numberBetween(5, 50));
        $actif->setCreatedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 year', 'now')));
        $actif->setUpdatedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 month', 'now')));
    }

    private function generateSlug(string $name): string
    {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    }
}
