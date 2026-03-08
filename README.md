# PSSP IMPACT — Système de Gestion des Déchets

Application web de gestion opérationnelle et financière des collectes de déchets, développée pour les entreprises de services environnementaux. Elle couvre l'ensemble du cycle de vie d'une collecte : planification, validation terrain, facturation, comptabilité et reporting.

## Stack technique

| Catégorie         | Technologie                                    |
|-------------------|------------------------------------------------|
| Backend           | PHP 8.2 · Laravel 12                           |
| Frontend          | Blade · Vite · Tailwind CSS                    |
| Base de données   | MySQL (SQLite en dev)                          |
| Auth              | Sessions Laravel · 2FA TOTP (Google Authenticator) |
| Permissions       | Spatie Laravel Permission (RBAC)               |
| PDF               | barryvdh/laravel-dompdf                        |
| Email             | SMTP (configurable)                            |
| CI/CD             | GitHub Actions                                 |
| Serveur de dev    | Laragon / Laravel Sail                         |

## Fonctionnalités principales

- **Authentification sécurisée** — connexion, 2FA par QR code, gestion des sessions multi-appareils, politique de mot de passe
- **Gestion des rôles** — Super Admin, Admin, Responsable de site, Comptable (permissions granulaires par rôle)
- **Collectes** — création, suivi, double validation terrain/bureau avant facturation
- **Facturation** — génération de factures PDF, liaison collectes ↔ factures, suivi des paiements
- **Comptabilité** — écritures comptables automatiques, export comptable
- **Sites & types de déchets** — configuration par l'administrateur, géolocalisation des sites
- **Incidents & observations** — signalement terrain, suivi par responsable
- **Rapports** — tableaux de bord par rôle, exports
- **Invitations utilisateurs** — inscription par token d'invitation, audit des accès

## Architecture

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/          # Gestion utilisateurs, invitations, sessions, audit
│   │   ├── Auth/           # Authentification, 2FA
│   │   ├── backend/        # API internes
│   │   ├── CollecteController.php
│   │   ├── ComptabiliteController.php
│   │   ├── FactureController.php
│   │   ├── PaiementController.php
│   │   ├── RapportController.php
│   │   └── ...
│   └── Middleware/
├── Models/                 # Eloquent : Collecte, Facture, Site, TypeDechet...
├── Services/               # Logique métier découplée
├── Mail/                   # Notifications email (invitations, alertes)
└── Exports/                # Exports Excel/CSV
database/
├── migrations/             # 20+ migrations, historique complet du schéma
└── seeders/                # Rôles, permissions, données de démonstration
resources/views/            # Templates Blade par module
routes/web.php              # Routes groupées par rôle
.github/workflows/          # Pipeline CI/CD (deploy.yml)
```

## Prérequis

- PHP >= 8.2 avec extensions : `pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`
- Composer >= 2
- Node.js >= 18 + npm
- MySQL >= 8 (ou SQLite pour le développement)

## Installation

```bash
# 1. Cloner le dépôt
git clone https://github.com/<votre-compte>/gestionDechets.git
cd gestionDechets

# 2. Installer les dépendances PHP
composer install

# 3. Installer les dépendances JS
npm install

# 4. Configurer l'environnement
cp .env.example .env
php artisan key:generate

# 5. Configurer la base de données dans .env, puis migrer
php artisan migrate --seed

# 6. Lier le stockage public
php artisan storage:link
```

## Démarrage

```bash
# Démarrer tous les services (serveur, queue, logs, vite) en parallèle
composer run dev
```

Ou séparément :

```bash
php artisan serve          # Serveur PHP
npm run dev                # Vite (assets)
php artisan queue:listen   # Worker de queue (emails, jobs)
```

L'application est accessible sur `http://localhost:8000`.

## Tests

```bash
composer test
# ou
php artisan test
```

## Variables d'environnement clés

Voir `.env.example` pour la liste complète. Les variables essentielles :

| Variable              | Description                                      |
|-----------------------|--------------------------------------------------|
| `APP_KEY`             | Clé de chiffrement (générée par `artisan key:generate`) |
| `DB_CONNECTION`       | `mysql` ou `sqlite`                              |
| `DB_DATABASE`         | Nom de la base de données                        |
| `MAIL_MAILER`         | Driver email (`smtp`, `log` en dev)              |
| `MAIL_HOST`           | Serveur SMTP                                     |
| `MAIL_USERNAME`       | Identifiant SMTP                                 |
| `MAIL_PASSWORD`       | Mot de passe SMTP                                |

## Licence

MIT
