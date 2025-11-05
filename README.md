# 📦 AssetFlow — Gestion des Actifs en Entreprise

AssetFlow est une application web développée avec **Symfony** permettant la gestion des inventaires matériels et logiciels, l’attribution d’actifs aux employés ainsi que leur restitution avec contrôle de conformité.

---

## 
Fonctionnalités

| Module | Fonctionnalités |
|--------|----------------|
| 👨‍💼 Utilisateurs | Gestion des employés et départements |
| 🖥️ Inventaire | Ajout d’actifs matériels et logiciels |
| 🔑 Attribution | Attribution d’actifs aux employés + suivi de statut |
| 🔁 Restitution | Restitution avec checklist d’état |
| 📊 Reporting | Statistiques d’utilisation & d’inventaire (à venir) |

---

##  Technologies Utilisées

- Symfony 7
- PostgreSQL
- Doctrine ORM
- Twig + TailwindCSS
- Faker (Fixtures)
- Docker (optionnel)

---

## Installation

###  Prérequis
- PHP 8.1+  
- Composer  
- Symfony CLI  
- PostgreSQL  
- Git  

---

### 📥 Cloner le projet & installer les dépendances

```bash
git clone https://github.com/username/AssetFlow.git
cd AssetFlow
composer install

Configuration de l’environnement
cp .env .env.local


Modifier la connexion base de données :

.env.local :

DATABASE_URL="postgresql://user:password@localhost:5432/assetflow?serverVersion=15&charset=utf8"

Initialisation de la base
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate -n
php bin/console doctrine:fixtures:load -n

Lancement du serveur
symfony serve -d


Application disponible sur :
http://localhost:8000
