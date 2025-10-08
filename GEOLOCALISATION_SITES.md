# 📍 Géolocalisation des Sites - Documentation

## 🎯 Objectif

Permettre l'enregistrement des coordonnées GPS des sites pour que les agents puissent facilement s'y rendre en utilisant Google Maps.

---

## ✅ Implémentation Complète

### 1. **Modèle Site** (`app/Models/Site.php`)

**Ajout des casts pour les coordonnées GPS :**
```php
protected $casts = [
    'latitude' => 'decimal:7',
    'longitude' => 'decimal:7',
];
```

### 2. **Contrôleur** (`app/Http/Controllers/SiteController.php`)

**Validation améliorée :**
```php
$validated = $request->validate([
    'site_name' => 'required|string|max:255',
    'site_departement' => 'required|string|max:255',
    'site_commune' => 'required|string|max:255',
    'localisation' => 'required|string|max:255',
    'longitude' => 'nullable|numeric|between:-180,180',  // ✅ Validation stricte
    'latitude' => 'nullable|numeric|between:-90,90',     // ✅ Validation stricte
    'responsable' => 'nullable|exists:users,user_id',
]);
```

### 3. **Vues modifiées**

#### A. Vue de création (`sites/create.blade.php`)

**Ajout du bouton de géolocalisation :**
```html
<div class="col-12">
    <div class="mb-3">
        <label class="form-label fw-bold">Coordonnées GPS du site</label>
        <div>
            <button type="button" class="btn btn-success btn-sm" id="shareSiteLocationBtn">
                <i class="bi bi-geo-alt-fill"></i> Récupérer les coordonnées GPS
            </button>
            <small class="text-muted d-block mt-2" id="siteLocationStatus"></small>
        </div>
    </div>
</div>
```

**Champs latitude/longitude en readonly :**
```html
<input type="number" step="0.0000001" name="latitude" 
       class="form-control" id="latitude" readonly>

<input type="number" step="0.0000001" name="longitude" 
       class="form-control" id="longitude" readonly>
```

**Script JavaScript de géolocalisation inclus** ✅

#### B. Vue d'édition (`sites/edit.blade.php`)

- ✅ Même fonctionnalité que la vue de création
- ✅ Bouton de géolocalisation
- ✅ Champs auto-remplis
- ✅ Script JavaScript

#### C. Vue d'affichage (`sites/show.blade.php`)

**Affichage des coordonnées GPS :**
```blade
@if($site->longitude && $site->latitude)
    <div class="col-md-4">
        <label class="text-muted small">Latitude</label>
        <p class="mb-0 fw-bold">{{ $site->latitude }}</p>
    </div>

    <div class="col-md-4">
        <label class="text-muted small">Longitude</label>
        <p class="mb-0 fw-bold">{{ $site->longitude }}</p>
    </div>

    <div class="col-md-4">
        <label class="text-muted small">Navigation</label>
        <a href="https://www.google.com/maps?q={{ $site->latitude }},{{ $site->longitude }}" 
           target="_blank" 
           class="btn btn-sm btn-primary">
            <i class="bi bi-map me-1"></i> Ouvrir dans Google Maps
        </a>
    </div>
@endif
```

**Lien Google Maps :**
- Ouvre Google Maps avec les coordonnées exactes du site
- Permet à l'agent d'obtenir un itinéraire depuis sa position

---

## 🚀 Utilisation

### Pour créer un site avec coordonnées GPS

1. **Aller dans Sites → Nouveau site**
2. **Remplir les informations** (nom, département, commune, localisation)
3. **Cliquer sur "Récupérer les coordonnées GPS"**
   - Se rendre physiquement sur le site
   - Autoriser l'accès à la localisation
   - Les coordonnées se remplissent automatiquement
4. **Enregistrer le site**

### Pour modifier les coordonnées d'un site existant

1. **Ouvrir le site** (Sites → Détails)
2. **Cliquer sur "Modifier"**
3. **Cliquer sur "Récupérer les coordonnées GPS"**
4. **Mettre à jour**

### Pour se rendre sur un site

1. **Ouvrir le site** (Sites → Détails)
2. **Cliquer sur "Ouvrir dans Google Maps"**
3. **Google Maps s'ouvre** avec :
   - La position exacte du site
   - Possibilité d'obtenir un itinéraire
   - Navigation GPS disponible

---

## 📊 Flux de Géolocalisation pour les Sites

```
┌─────────────────────────────────────────────────────┐
│  1. Créer/Modifier un site                          │
│     Remplir : Nom, Département, Commune, Loc        │
└────────────────┬────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────┐
│  2. Cliquer sur "Récupérer les coordonnées GPS"     │
│     🌍 Se rendre physiquement sur le site           │
└────────────────┬────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────┐
│  3. Autoriser la géolocalisation                    │
│     Navigateur demande la permission                │
└────────────────┬────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────┐
│  4. Coordonnées GPS récupérées                      │
│     Latitude : 14.6937300                           │
│     Longitude : -17.4468900                         │
└────────────────┬────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────┐
│  5. Enregistrer le site                             │
│     ✅ Site sauvegardé avec coordonnées GPS         │
└────────────────┬────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────┐
│  6. Les agents peuvent maintenant :                 │
│     • Voir les coordonnées du site                  │
│     • Ouvrir Google Maps pour s'y rendre            │
│     • Suivre l'itinéraire GPS                       │
└─────────────────────────────────────────────────────┘
```

---

## 🗺️ Fonctionnalité Google Maps

### Lien généré
```
https://www.google.com/maps?q=14.6937300,-17.4468900
```

### Ce que Google Maps offre :

1. **📍 Position exacte du site**
   - Marqueur sur la carte
   - Adresse approximative

2. **🚗 Itinéraire**
   - Depuis la position actuelle de l'agent
   - Plusieurs options de trajet
   - Temps de trajet estimé
   - Distance

3. **🧭 Navigation GPS**
   - Instructions vocales
   - Guidage en temps réel
   - Mise à jour du trajet

4. **🌍 Informations supplémentaires**
   - Vue satellite
   - Street View (si disponible)
   - Points d'intérêt à proximité

---

## 💡 Cas d'Usage

### Scénario 1 : Création d'un nouveau site

**Contexte :** Un responsable doit enregistrer un nouveau site de collecte.

**Étapes :**
1. Se rend sur le site physiquement
2. Crée le site dans l'application
3. Clique sur "Récupérer les coordonnées GPS"
4. Les coordonnées exactes sont enregistrées
5. **Résultat :** Les agents pourront facilement trouver le site

### Scénario 2 : Agent doit se rendre sur un site

**Contexte :** Un agent de collecte reçoit une mission pour un site spécifique.

**Étapes :**
1. Consulte les détails du site dans l'application
2. Voit les coordonnées GPS affichées
3. Clique sur "Ouvrir dans Google Maps"
4. Google Maps s'ouvre avec l'itinéraire
5. **Résultat :** L'agent suit la navigation GPS jusqu'au site

### Scénario 3 : Mise à jour de coordonnées incorrectes

**Contexte :** Les coordonnées d'un site sont erronées ou ont changé.

**Étapes :**
1. Se rend sur le site correct
2. Ouvre la modification du site
3. Clique sur "Récupérer les coordonnées GPS"
4. Met à jour le site
5. **Résultat :** Les coordonnées sont corrigées

---

## 🎨 Interface Visuelle

### Vue Création/Modification
```
┌────────────────────────────────────────┐
│  Nom du site : Site de Collecte A     │
│  Département : Dakar                   │
│  Commune     : Plateau                 │
│  Localisation: Rue 10 x Avenue B      │
│                                        │
│  Coordonnées GPS du site               │
│  ┌──────────────────────────────┐      │
│  │ [📍 Récupérer coordonnées]   │      │
│  │ ✅ Coordonnées récupérées !   │      │
│  └──────────────────────────────┘      │
│                                        │
│  Latitude  : 14.6937300  🔒           │
│  Longitude : -17.4468900 🔒           │
│                                        │
│  [Enregistrer] [Annuler]               │
└────────────────────────────────────────┘
```

### Vue Détails du Site
```
┌────────────────────────────────────────┐
│  Site de Collecte A      [Modifier]    │
├────────────────────────────────────────┤
│  📋 Informations générales             │
│  Nom        : Site de Collecte A       │
│  Département: Dakar                    │
│  Commune    : Plateau                  │
│  Localisation: Rue 10 x Avenue B      │
│                                        │
│  🌍 Coordonnées GPS                    │
│  Latitude   : 14.6937300               │
│  Longitude  : -17.4468900              │
│  Navigation : [🗺️ Ouvrir Google Maps] │
│                                        │
│  ℹ️ Cliquez sur "Ouvrir dans Google   │
│     Maps" pour obtenir les directions │
└────────────────────────────────────────┘
```

---

## ⚠️ Messages Possibles

### ✅ Messages de succès
```
✅ Coordonnées GPS récupérées avec succès ! (Précision: 12m)
✅ Site ajouté avec succès.
✅ Site mis à jour avec succès.
```

### ⏳ Messages d'information
```
⏳ Récupération de la position du site...
```

### ❌ Messages d'erreur
```
❌ Vous avez refusé l'accès à votre position.
❌ Les informations de localisation ne sont pas disponibles.
❌ La demande de localisation a expiré.
```

### ⚠️ Messages d'avertissement
```
⚠️ Les coordonnées GPS de ce site ne sont pas encore enregistrées.
   Modifier le site pour ajouter les coordonnées.
```

---

## 🔍 Différences avec la Géolocalisation des Agents

| Aspect | Sites | Agents |
|--------|-------|--------|
| **Objectif** | Localiser le site physiquement | Connaître la position de l'agent |
| **Fréquence** | Une fois (lors de la création) | Peut changer régulièrement |
| **Utilisation** | Navigation vers le site | Suivi, validation de présence |
| **Qui renseigne ?** | Responsable/Admin sur place | L'agent lui-même |
| **Google Maps** | ✅ Lien direct disponible | ❌ Juste affichage coordonnées |

---

## 🛠️ Configuration Technique

### Base de données
**Table :** `sites`
```sql
latitude   DECIMAL(10,7) NULLABLE  -- Ex: 14.6937300
longitude  DECIMAL(10,7) NULLABLE  -- Ex: -17.4468900
```

### Validation
```php
'latitude' => 'nullable|numeric|between:-90,90',
'longitude' => 'nullable|numeric|between:-180,180',
```

### Geolocation API
```javascript
navigator.geolocation.getCurrentPosition(
    success_callback,
    error_callback,
    {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0
    }
);
```

---

## 📱 Compatibilité

### ✅ Navigateurs supportés
- Chrome, Firefox, Safari, Edge, Opera
- Tous les navigateurs mobiles modernes

### ✅ Plateformes
- 💻 Desktop (WiFi/IP - précision moyenne)
- 📱 Mobile (GPS - haute précision)
- 📲 Tablette (GPS/WiFi)

### ⚠️ Prérequis
- HTTPS en production
- JavaScript activé
- Permission géolocalisation autorisée

---

## 🎯 Avantages

### Pour les responsables
- ✅ **Enregistrement simple** : 1 clic pour capturer les coordonnées
- ✅ **Précision** : GPS haute précision sur mobile
- ✅ **Pas d'erreur** : Plus de saisie manuelle

### Pour les agents
- ✅ **Navigation facile** : Lien direct vers Google Maps
- ✅ **Itinéraire optimisé** : Google Maps calcule le meilleur trajet
- ✅ **Gain de temps** : Trouve rapidement le site

### Pour l'application
- ✅ **Données fiables** : Coordonnées exactes
- ✅ **Intégration Google Maps** : Fonctionnalité native
- ✅ **Base pour évolutions** : Calcul de distances, géofencing, etc.

---

## 🔮 Évolutions Futures

### Phase 1 (Court terme)
- [ ] Afficher une carte directement dans la page de détails
- [ ] Calculer la distance entre l'agent et le site
- [ ] Afficher tous les sites sur une carte interactive

### Phase 2 (Moyen terme)
- [ ] Validation : vérifier que l'agent est bien sur le site lors de la collecte
- [ ] Optimisation de tournées : suggérer un ordre de visite optimal
- [ ] Historique des visites avec coordonnées

### Phase 3 (Long terme)
- [ ] Géofencing : alertes si agent sort du périmètre
- [ ] Heatmap des sites les plus visités
- [ ] Analyse géographique avancée

---

## ✅ Checklist de Validation

### Code
- [x] Modèle Site avec casts latitude/longitude
- [x] Validation stricte dans SiteController
- [x] Vue create avec géolocalisation
- [x] Vue edit avec géolocalisation
- [x] Vue show avec coordonnées et lien Google Maps
- [x] Scripts JavaScript fonctionnels

### Fonctionnalités
- [x] Récupération GPS automatique
- [x] Affichage des coordonnées
- [x] Lien Google Maps opérationnel
- [x] Messages de statut clairs
- [x] Gestion d'erreurs complète

### Documentation
- [x] Documentation technique
- [x] Guide d'utilisation
- [x] Cas d'usage documentés
- [x] Flux visuels

---

## 🎉 Résultat Final

**Les sites peuvent maintenant être géolocalisés avec précision !**

### Workflow complet :
```
1. Responsable crée un site sur place
         ↓
2. Récupère les coordonnées GPS automatiquement
         ↓
3. Site sauvegardé avec coordonnées exactes
         ↓
4. Agent consulte le site dans l'application
         ↓
5. Clique sur "Ouvrir dans Google Maps"
         ↓
6. Google Maps le guide jusqu'au site
         ↓
7. Agent arrive au bon endroit ! ✅
```

---

## 📞 Support

### En cas de problème avec la géolocalisation des sites

**Vérifier :**
- Le navigateur supporte la géolocalisation
- HTTPS est activé (en production)
- La permission géolocalisation est autorisée
- L'utilisateur est physiquement sur le site

**Tester :**
- Créer un site test
- Activer le GPS sur mobile
- Se mettre à l'extérieur (meilleure réception)
- Vérifier la console navigateur (F12)

---

**Géolocalisation des sites implémentée avec succès !** 🎯📍🗺️

*Cette fonctionnalité permet aux agents de se rendre facilement sur les sites de collecte en utilisant Google Maps pour la navigation GPS.*
