# 🗺️ ARCHITECTURE VISUELLE - PSSP IMPACT+ v2.0

## 📐 Vue d'Ensemble du Système

```
┌─────────────────────────────────────────────────────────────────────┐
│                        PSSP IMPACT+ v2.0                           │
│                  Plateforme de Gestion de Déchets                   │
└─────────────────────────────────────────────────────────────────────┘
                                  │
                ┌─────────────────┼─────────────────┐
                │                 │                 │
         ┌──────▼──────┐   ┌─────▼──────┐   ┌─────▼──────┐
         │   WEB APP   │   │  MOBILE    │   │    API     │
         │   (Blade)   │   │    APP     │   │   REST     │
         └──────┬──────┘   └─────┬──────┘   └─────┬──────┘
                │                │                 │
                └────────────────┼─────────────────┘
                                 │
                    ┌────────────▼────────────┐
                    │   LARAVEL 12 BACKEND    │
                    │   (PHP 8.2 + Services)  │
                    └────────────┬────────────┘
                                 │
        ┌────────────────────────┼────────────────────────┐
        │                        │                        │
  ┌─────▼─────┐          ┌──────▼──────┐         ┌──────▼──────┐
  │ DATABASES │          │   STORAGE   │         │   QUEUES    │
  │  MySQL/   │          │    Files    │         │   Redis     │
  │PostgreSQL │          │  Documents  │         │   Jobs      │
  └───────────┘          └─────────────┘         └─────────────┘
```

---

## 🏗️ Architecture en Couches

```
┌─────────────────────────────────────────────────────────┐
│                  COUCHE PRÉSENTATION                    │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐             │
│  │  Blade   │  │   API    │  │  Mobile  │             │
│  │  Views   │  │  Routes  │  │   App    │             │
│  └──────────┘  └──────────┘  └──────────┘             │
└──────────────────────┬──────────────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────────────┐
│                 COUCHE CONTRÔLEURS                      │
│  Auth │ Admin │ Collectes │ Factures │ Sites │ ...     │
└──────────────────────┬──────────────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────────────┐
│                  COUCHE SERVICES                        │
│  AuditService │ TourneeService │ FactureWorkflow │ ... │
└──────────────────────┬──────────────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────────────┐
│                  COUCHE MODÈLES                         │
│  User │ Collecte │ Facture │ Site │ Document │ ...     │
└──────────────────────┬──────────────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────────────┐
│                 COUCHE DONNÉES                          │
│         MySQL/PostgreSQL + Redis + Filesystem           │
└─────────────────────────────────────────────────────────┘
```

---

## 🔄 Flux de Données Principaux

### 1. Flux de Collecte

```
┌──────────┐     ┌──────────┐     ┌──────────┐     ┌──────────┐
│  Agent   │────▶│ Tournee  │────▶│ Collecte │────▶│  Audit   │
│  Mobile  │     │ Service  │     │  Service │     │  Log     │
└──────────┘     └──────────┘     └──────────┘     └──────────┘
     │                                    │
     │                                    ▼
     │                            ┌──────────────┐
     │                            │  SLA Check   │
     │                            └──────────────┘
     │                                    │
     ▼                                    ▼
┌──────────┐                      ┌──────────────┐
│   GPS    │                      │ Notification │
│ Tracking │                      │   Service    │
└──────────┘                      └──────────────┘
```

### 2. Flux de Facturation

```
┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│  Collectes   │────▶│   Template   │────▶│   Facture    │
│  du mois     │     │ Auto-Génér.  │     │  (Brouillon) │
└──────────────┘     └──────────────┘     └──────────────┘
                                                  │
                                                  ▼
                                          ┌──────────────┐
                                          │  Validation  │
                                          │   Workflow   │
                                          └──────────────┘
                                                  │
                     ┌────────────┬───────────────┤
                     │            │               │
                     ▼            ▼               ▼
              ┌──────────┐ ┌──────────┐   ┌──────────┐
              │  Email   │ │ WhatsApp │   │   PDF    │
              │  Client  │ │  Client  │   │  Export  │
              └──────────┘ └──────────┘   └──────────┘
```

### 3. Flux de Paiement

```
┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│   Client     │────▶│ Mobile Money │────▶│   Webhook    │
│  (MTN MOMO)  │     │   Gateway    │     │   Callback   │
└──────────────┘     └──────────────┘     └──────────────┘
                                                  │
                                                  ▼
                                          ┌──────────────┐
                                          │  Vérification│
                                          │   Paiement   │
                                          └──────────────┘
                                                  │
                            ┌─────────────────────┴─────────────┐
                            │                                   │
                            ▼                                   ▼
                    ┌──────────────┐                   ┌──────────────┐
                    │  Écriture    │                   │   Facture    │
                    │  Comptable   │                   │   = Payée    │
                    └──────────────┘                   └──────────────┘
```

---

## 📦 Organisation des Modules

```
PSSP IMPACT+ v2.0
│
├── 🔐 Module 1 : Gouvernance & Sécurité
│   ├── Audit Logs (qui/quoi/quand)
│   ├── 2FA (Google Authenticator)
│   ├── Gestion Sessions
│   ├── Politique Mots de Passe
│   └── Matrice Permissions
│
├── 🚛 Module 2 : Collectes & Opérations
│   ├── Planification Tournées
│   ├── Optimisation Routes (TSP)
│   ├── SLA & Alertes
│   ├── Tracking GPS Temps Réel
│   ├── Incidents Enrichis
│   └── Import/Export CSV
│
├── 💰 Module 3 : Facturation & Paiements
│   ├── Génération Auto (Templates)
│   ├── Workflow Validation
│   ├── Relances Automatiques
│   ├── Mobile Money (MTN/Orange/Wave)
│   └── Rapprochement Bancaire
│
├── 📊 Module 4 : Comptabilité
│   ├── Plan Comptable
│   ├── Écritures Auto
│   ├── TVA Auto
│   ├── Bilan & Compte Résultat
│   └── Exports (Sage/Quickbooks)
│
├── 📈 Module 5 : Rapports & BI
│   ├── Dashboards Dynamiques
│   ├── KPIs Personnalisés
│   ├── Filtres Sauvegardés
│   ├── Rapports Planifiés
│   └── Prévisions (ML)
│
├── 🏢 Module 6 : Gestion Sites
│   ├── Contrats par Site
│   ├── Historique Changements
│   ├── Alertes Auto
│   └── Cartes Intégrées (Leaflet)
│
├── 👥 Module 7 : Gestion Utilisateurs
│   ├── Invitations Email
│   ├── Profils Enrichis
│   ├── Multi-Site
│   └── Import CSV Masse
│
├── 🔔 Module 8 : Notifications
│   ├── Email
│   ├── WhatsApp
│   ├── SMS
│   ├── Centre Notifs In-App
│   └── Templates Personnalisables
│
├── 📁 Module 9 : Documents & Archivage
│   ├── Stockage Centralisé
│   ├── Versioning Auto
│   ├── Signatures Électroniques
│   └── Archivage Légal
│
└── ⭐ Module 10 : Qualité & Support
    ├── Ticketing (Réclamations)
    ├── Scores Qualité/Site
    ├── Enquêtes Satisfaction
    └── SLA Support
```

---

## 🗄️ Schéma de Base de Données Simplifié

```
┌─────────────────────────────────────────────────────────────────┐
│                         TABLES PRINCIPALES                       │
└─────────────────────────────────────────────────────────────────┘

┌──────────┐         ┌──────────┐         ┌──────────┐
│  users   │◄───────▶│  sites   │◄───────▶│ contrats │
└────┬─────┘         └────┬─────┘         └──────────┘
     │                    │
     │        ┌───────────┴──────────┐
     │        │                      │
     ▼        ▼                      ▼
┌──────────┐ ┌──────────┐      ┌──────────┐
│ collectes│ │ tournees │      │incidents │
└────┬─────┘ └────┬─────┘      └──────────┘
     │            │
     │            ▼
     │       ┌──────────┐
     │       │ tournee_ │
     │       │  sites   │
     │       └──────────┘
     │
     ▼
┌──────────┐         ┌──────────┐         ┌──────────┐
│ factures │◄───────▶│ paiements│────────▶│  mobile  │
└────┬─────┘         └──────────┘         │  money   │
     │                                     └──────────┘
     ▼
┌──────────┐         ┌──────────┐
│ ecritures│◄───────▶│ comptes  │
│comptables│         │comptables│
└──────────┘         └──────────┘

┌──────────┐         ┌──────────┐         ┌──────────┐
│  audit   │         │documents │         │notifica- │
│   logs   │         │          │         │  tions   │
└──────────┘         └──────────┘         └──────────┘
```

---

## 🎨 Architecture du Design System

```
DESIGN SYSTEM PREMIUM
│
├── 🎨 COULEURS
│   ├── Primaires (Gradients)
│   │   ├── Primary: #667eea → #764ba2
│   │   ├── Success: #10b981 → #059669
│   │   ├── Danger: #ef4444 → #dc2626
│   │   ├── Warning: #f59e0b → #d97706
│   │   └── Info: #3b82f6 → #2563eb
│   │
│   └── Neutres
│       ├── Dark: #0f172a → #334155
│       ├── Gray: #475569 → #cbd5e1
│       └── White: #ffffff
│
├── 🧩 COMPOSANTS
│   ├── Cards
│   │   ├── Premium Card
│   │   ├── Glass Card (glassmorphism)
│   │   ├── Stats Card
│   │   └── Gradient Card
│   │
│   ├── Buttons
│   │   ├── Gradient Buttons
│   │   ├── Outline Buttons
│   │   └── Icon Buttons
│   │
│   ├── Forms
│   │   ├── Input Modern
│   │   ├── Select Modern
│   │   └── Input Groups
│   │
│   ├── Tables
│   │   ├── Premium Table
│   │   ├── Hover Effects
│   │   └── Rounded Rows
│   │
│   └── Badges
│       ├── Modern Badge
│       ├── Glow Badge
│       └── Status Badge
│
├── ✨ ANIMATIONS
│   ├── Fade In Up
│   ├── Fade In Right
│   ├── Pulse
│   ├── Shimmer (loading)
│   ├── Hover Transforms
│   └── Scale Effects
│
└── 📐 LAYOUT
    ├── Sidebar Dark
    ├── Header Sticky
    ├── Main Content
    └── Footer
```

---

## 🔌 Intégrations Externes

```
┌─────────────────────────────────────────────────────────────┐
│                    PSSP IMPACT+ CORE                        │
└──────────────┬──────────────────────┬───────────────────────┘
               │                      │
    ┌──────────▼──────────┐    ┌─────▼──────────────┐
    │   COMMUNICATION     │    │    PAIEMENTS       │
    ├─────────────────────┤    ├────────────────────┤
    │ • Twilio (WhatsApp) │    │ • MTN Mobile Money │
    │ • Twilio (SMS)      │    │ • Orange Money     │
    │ • SendGrid (Email)  │    │ • Wave             │
    └─────────────────────┘    └────────────────────┘
               │                      │
    ┌──────────▼──────────┐    ┌─────▼──────────────┐
    │    CARTOGRAPHIE     │    │   STOCKAGE         │
    ├─────────────────────┤    ├────────────────────┤
    │ • Leaflet.js        │    │ • Local Storage    │
    │ • OpenStreetMap     │    │ • AWS S3 (option)  │
    │ • GPS APIs          │    │ • Google Drive     │
    └─────────────────────┘    └────────────────────┘
```

---

## ⚙️ Infrastructure Technique

```
┌─────────────────────────────────────────────────────────────┐
│                      PRODUCTION                             │
└─────────────────────────────────────────────────────────────┘

┌──────────────┐    ┌──────────────┐    ┌──────────────┐
│  NGINX       │───▶│  PHP-FPM     │───▶│  MySQL       │
│  (Reverse    │    │  (Laravel)   │    │  (Primary)   │
│   Proxy)     │    └──────────────┘    └──────────────┘
└──────────────┘            │
                            │
                 ┌──────────┴──────────┐
                 │                     │
          ┌──────▼──────┐      ┌──────▼──────┐
          │   Redis     │      │  Supervisor │
          │  (Cache +   │      │  (Queue     │
          │   Queue)    │      │  Workers)   │
          └─────────────┘      └─────────────┘

┌─────────────────────────────────────────────────────────────┐
│                    MONITORING                               │
├─────────────────────────────────────────────────────────────┤
│  Laravel Telescope │ Debug Bar │ Logs │ Metrics            │
└─────────────────────────────────────────────────────────────┘
```

---

## 📊 Flux de Travail Quotidien

### Workflow Agent de Collecte

```
08:00 ─────────────▶ Connexion App Mobile
         │
         ├─────────▶ Voir Tournée du Jour
         │                  │
         │                  ├─────────▶ Site 1 (GPS activé)
         │                  │                │
         │                  │                ├─▶ Collecte effectuée
         │                  │                │
         │                  │                ├─▶ Photos prises
         │                  │                │
         │                  │                └─▶ Validation
         │                  │
         │                  ├─────────▶ Site 2 ...
         │                  │
         │                  └─────────▶ Site N
         │
17:00 ◄─┴──────────── Fin de Tournée (Rapport Auto)
```

### Workflow Comptable

```
Jour 1 ────────────▶ Génération Auto Factures
         │
         ├─────────▶ Validation Factures
         │                  │
         │                  └─────────▶ Envoi Clients (Email/WhatsApp)
         │
Jour 10 ─┴─────────▶ Suivi Paiements
                              │
                              ├─────────▶ Relance Auto (J+10)
                              │
Jour 30 ──────────────────────┴─────────▶ Rapprochement Bancaire
                                                  │
                                                  └─▶ Écritures Comptables
```

---

## 🎯 Points d'Entrée du Système

```
UTILISATEURS
│
├── ADMIN
│   └── /admin/dashboard
│       ├── Audit Logs
│       ├── Utilisateurs
│       ├── Configurations
│       └── Rapports Globaux
│
├── COMPTABLE
│   └── /comptable/dashboard
│       ├── Factures
│       ├── Paiements
│       ├── Écritures Comptables
│       └── Rapprochement Bancaire
│
├── AGENT
│   └── /agent/dashboard
│       ├── Mes Tournées
│       ├── Collectes en Cours
│       ├── Incidents
│       └── Historique
│
└── RESPONSABLE SITE
    └── /site/dashboard
        ├── Aperçu Site
        ├── Collectes du Site
        ├── Contrat & Facturation
        └── Réclamations
```

---

## 🔄 Cycle de Vie d'une Collecte

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│  PLANIFIÉE  │────▶│  ASSIGNÉE   │────▶│  EN COURS   │
└─────────────┘     └─────────────┘     └─────────────┘
      ▲                                         │
      │                                         │
      │                                         ▼
      │                                  ┌─────────────┐
      │                                  │  COMPLÉTÉE  │
      │                                  └─────────────┘
      │                                         │
      │                                         ▼
      │                                  ┌─────────────┐
      │                                  │  FACTURÉE   │
      │                                  └─────────────┘
      │                                         │
      │                                         ▼
      │                                  ┌─────────────┐
      └──────────────────────────────────│   PAYÉE     │
                                         └─────────────┘
                      
                      INCIDENT ?
                           │
                           ▼
                    ┌─────────────┐
                    │  SIGNALÉE   │
                    │  (incident) │
                    └─────────────┘
```

---

## 📱 Architecture Mobile (Future)

```
┌─────────────────────────────────────────────────────────────┐
│                     MOBILE APP                              │
│                  (React Native / Flutter)                   │
└──────────────┬──────────────────────┬───────────────────────┘
               │                      │
    ┌──────────▼──────────┐    ┌─────▼──────────────┐
    │   AGENT MODULE      │    │  CLIENT MODULE     │
    ├─────────────────────┤    ├────────────────────┤
    │ • Voir Tournées     │    │ • Voir Factures    │
    │ • Scanner QR Sites  │    │ • Payer (MOMO)     │
    │ • Photo Collectes   │    │ • Réclamations     │
    │ • GPS Tracking      │    │ • Notifications    │
    └─────────────────────┘    └────────────────────┘
               │                      │
               └──────────┬───────────┘
                          │
                    ┌─────▼──────┐
                    │  REST API  │
                    │  (Laravel) │
                    └────────────┘
```

---

## 🎉 Résultat Final

```
┌─────────────────────────────────────────────────────────────┐
│                                                             │
│           PSSP IMPACT+ v2.0 - FULL FEATURED ERP             │
│                                                             │
│  🔐 Sécurité Enterprise      📊 Analytics Avancé           │
│  🚛 Gestion Opérations       💰 Finance Complète            │
│  👥 Multi-Utilisateurs       📱 Mobile Ready               │
│  🔔 Notifications Multi      📁 GED Intégrée               │
│  ⭐ Support Client 24/7      🌍 Géolocalisation            │
│                                                             │
│              🚀 Production-Ready en 8 semaines              │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

**Cette architecture visuelle vous permet de voir d'un coup d'œil comment tout s'articule ! 🎯**
