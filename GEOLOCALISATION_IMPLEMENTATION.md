# Implémentation de la Géolocalisation pour les Agents

## 📍 Vue d'ensemble

Cette fonctionnalité permet aux agents de partager leur position géographique directement depuis leur profil utilisateur. Les coordonnées GPS (latitude et longitude) sont automatiquement récupérées via l'API de géolocalisation du navigateur et sauvegardées en base de données.

## 🔧 Modifications apportées

### 1. Base de données (Déjà existante)

La table `users` possède déjà les champs nécessaires :
- `localisation` (string, nullable) - Nom de la localisation
- `latitude` (decimal 10,7, nullable) - Latitude GPS
- `longitude` (decimal 10,7, nullable) - Longitude GPS

### 2. Modèle User (`app/Models/User.php`)

**Ajout des casts pour les coordonnées GPS :**
```php
protected $casts = [
    'email_verified_at' => 'datetime',
    'password' => 'hashed',
    'isActive' => 'boolean',
    'social_links' => 'array',
    'settings' => 'array',
    'latitude' => 'decimal:7',    // ✅ Nouveau
    'longitude' => 'decimal:7',   // ✅ Nouveau
];
```

### 3. Contrôleur (`app/Http/Controllers/ProfileController.php`)

Le contrôleur valide déjà les coordonnées GPS lors de la mise à jour du profil :
```php
$validated = $request->validate([
    'firstname' => ['required', 'string', 'max:255'],
    'lastname' => ['required', 'string', 'max:255'],
    'username' => [...],
    'email' => [...],
    'localisation' => ['nullable', 'string', 'max:255'],
    'latitude' => ['nullable', 'numeric', 'between:-90,90'],      // ✅ Validation
    'longitude' => ['nullable', 'numeric', 'between:-180,180'],   // ✅ Validation
]);
```

### 4. Vue Profile (`resources/views/profile/show.blade.php`)

#### Interface utilisateur ajoutée :

1. **Bouton de partage de position** (ligne 189-192) :
```html
<button type="button" class="btn btn-success btn-sm" id="shareLocationBtn">
    <i class="bi bi-geo-alt-fill"></i> Partager ma position
</button>
<small class="text-muted d-block mt-2" id="locationStatus"></small>
```

2. **Champs latitude et longitude en lecture seule** :
   - Les champs sont maintenant en `readonly` pour éviter la saisie manuelle erronée
   - Ils sont automatiquement remplis lors du partage de position

3. **Script JavaScript de géolocalisation** (lignes 288-357) :

Le script gère :
- ✅ Vérification du support de la géolocalisation par le navigateur
- ✅ Demande de permission à l'utilisateur
- ✅ Récupération des coordonnées GPS avec haute précision
- ✅ Remplissage automatique des champs latitude/longitude
- ✅ Affichage de messages de statut (succès, erreur, chargement)
- ✅ Gestion complète des erreurs (permission refusée, timeout, etc.)

## 🚀 Utilisation

### Pour l'agent :

1. **Accéder au profil** :
   - Se connecter à l'application
   - Aller dans le menu profil
   - Cliquer sur l'onglet "Edit Profil"

2. **Partager sa position** :
   - Cliquer sur le bouton vert "**Partager ma position**"
   - Autoriser l'accès à la localisation quand le navigateur le demande
   - Les champs latitude et longitude se remplissent automatiquement
   - Un message de succès s'affiche avec la précision de la position

3. **Sauvegarder** :
   - Cliquer sur "**Save Changes**" pour enregistrer les coordonnées en base de données

### Messages de statut :

- 📍 **"Récupération de votre position..."** - Demande en cours
- ✅ **"Position récupérée avec succès ! (Précision: Xm)"** - Succès
- ❌ **"Vous avez refusé l'accès à votre position."** - Permission refusée
- ❌ **"Les informations de localisation ne sont pas disponibles."** - GPS indisponible
- ❌ **"La demande de localisation a expiré."** - Timeout (10 secondes)

## 🔐 Sécurité et confidentialité

1. **Permission utilisateur obligatoire** :
   - Le navigateur demande explicitement la permission avant d'accéder à la position
   - L'utilisateur peut refuser à tout moment

2. **Haute précision** :
   - `enableHighAccuracy: true` - Utilise le GPS si disponible
   - Précision affichée à l'utilisateur

3. **Timeout** :
   - 10 secondes maximum pour récupérer la position
   - Évite le blocage de l'interface

4. **Validation côté serveur** :
   - Latitude : entre -90 et 90
   - Longitude : entre -180 et 180

## 📊 Affichage des coordonnées

Dans l'onglet "**Infos personnelles**", les coordonnées sont affichées si elles existent :

```blade
@if(Auth::user()->longitude && Auth::user()->latitude)
<div class="row">
    <div class="col-lg-3 col-md-4 label">Coordinates</div>
    <div class="col-lg-9 col-md-8">
        {{ Auth::user()->latitude }}, {{ Auth::user()->longitude }}
    </div>
</div>
@endif
```

## 🔄 Flux de données

```
1. Utilisateur clique sur "Partager ma position"
   ↓
2. Navigateur demande la permission
   ↓
3. API Geolocation récupère les coordonnées GPS
   ↓
4. JavaScript remplit les champs latitude/longitude
   ↓
5. Utilisateur clique sur "Save Changes"
   ↓
6. Validation Laravel côté serveur
   ↓
7. Sauvegarde en base de données (table users)
   ↓
8. Redirection avec message de succès
```

## 🌐 Compatibilité navigateurs

La Geolocation API est supportée par :
- ✅ Chrome 5+
- ✅ Firefox 3.5+
- ✅ Safari 5+
- ✅ Edge (tous)
- ✅ Opera 16+
- ✅ Mobile (iOS Safari, Chrome Mobile, etc.)

**Note** : HTTPS est requis pour la géolocalisation (sauf en localhost)

## 🛠️ Extensions possibles

1. **Afficher sur une carte** :
   - Intégrer Leaflet ou Google Maps pour visualiser la position
   - Permettre le placement manuel sur la carte

2. **Historique des positions** :
   - Créer une table pour stocker l'historique des positions
   - Tracer les déplacements de l'agent

3. **Validation de proximité** :
   - Vérifier que l'agent est sur le site lors d'une collecte
   - Calculer la distance entre la position de l'agent et le site

4. **Géofencing** :
   - Alerter si l'agent sort d'une zone définie
   - Restrictions d'actions selon la position

## 📝 Notes techniques

- **Précision** : Dépend du GPS, WiFi, ou IP (de quelques mètres à plusieurs kilomètres)
- **Délai** : 1-5 secondes en moyenne pour récupérer la position
- **Batterie** : `enableHighAccuracy` consomme plus de batterie sur mobile
- **Format** : Les coordonnées sont stockées avec 7 décimales (précision ~11mm)

## ✅ Checklist de validation

- [x] Champs GPS ajoutés au modèle User
- [x] Validation des coordonnées dans ProfileController
- [x] Bouton de partage de position dans le formulaire
- [x] Script JavaScript fonctionnel avec gestion d'erreurs
- [x] Affichage des coordonnées dans les infos personnelles
- [x] Champs en readonly pour éviter les erreurs de saisie
- [x] Messages de statut informatifs pour l'utilisateur
- [x] Casts appropriés dans le modèle (decimal:7)
- [x] Support des navigateurs modernes
- [x] Gestion complète des erreurs et permissions

## 🎯 Résultat final

Les agents peuvent maintenant :
1. Partager leur position GPS en un clic
2. Voir leurs coordonnées actuelles dans leur profil
3. Mettre à jour leur position à tout moment
4. Sauvegarder les coordonnées en base de données pour utilisation future

Cette fonctionnalité peut être utilisée pour :
- 📍 Géolocaliser les collectes de déchets
- 🗺️ Tracer les itinéraires des agents
- ✅ Vérifier la présence sur site
- 📊 Générer des rapports géographiques
