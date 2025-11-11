# 📦 AssetFlow — Gestion des Actifs d’Entreprise

AssetFlow est une application web développée avec **Symfony 7** pour gérer les actifs matériels et logiciels d’une entreprise : inventaire, attribution aux employés, restitution avec contrôle, et reporting.

---

## ✅ Fonctionnalités

| Module                         | Description                                               |
| ------------------------------ | --------------------------------------------------------- |
| 👨‍💼 Gestion des utilisateurs    | Gestion des employés, rôles & départements                |
| 🖥️ Inventaire                  | Création et gestion des actifs matériels & logiciels      |
| 🔑 Attribution                 | Attribution des actifs aux employés avec suivi            |
| 🔁 Restitution                 | Restitution avec checklist d’état & mise à jour du statut |
| 📊 Reporting *(WIP)*           | Statistiques d’inventaire & indicateurs d’utilisation     |

---

## 🛠️ Technologies

* **Symfony 7**
* **PostgreSQL** + Doctrine ORM
* **Twig** + **TailwindCSS**
* **Docker** *(optionnel)*
* **Faker** pour les fixtures

---

## 🚀 Installation & Exécution

### ✅ Prérequis

Assurez-vous d’avoir installé :

| Outil       | Version |
| ----------- | ------- |
| PHP         | 8.1+    |
| Composer    | ✅       |
| Symfony CLI | ✅       |
| PostgreSQL  | ✅       |
| Git         | ✅       |

---

### 1️⃣ Cloner le projet

```bash
git clone https://github.com/Drak-01/AssetFlow.git
cd AssetFlow
composer install
```

---

### 2️⃣ Configurer l’environnement

Copier la configuration :

```bash
cp .env .env.dev
```

Modifier `.env.dev` selon vos accès BD :

```env
DATABASE_URL="postgresql://user:password@localhost:5432/assetflow?serverVersion=15&charset=utf8"
```

Créer la base, appliquer migrations & charger les fixtures :

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate -n
php bin/console doctrine:fixtures:load -n
```

---

### 3️⃣ Lancer le serveur

```bash
symfony serve 
```

Application accessible sur 👉
➡️ [http://localhost:8000/](http://localhost:8000/)

---

## 🧩 Structure du projet (résumé)

```
AssetFlow/
├── src/          # Code source Symfony
├── templates/    # Vues Twig
├── migrations/   # Migrations Doctrine
├── assets/       # Fichiers CSS/JS (Tailwind)
└── docker/       # Config Docker (optionnel)
```

---

## 🛂 Authentification

> Les utilisateurs de test sont créés automatiquement avec les fixtures.

| Email                                         | Mot de passe | Rôle       |
| --------------------------------------------- | ------------ | ---------- |
| [admin@example.com](mailto:admin@assetflow.com) | admin123     | ROLE_ADMIN |
---


