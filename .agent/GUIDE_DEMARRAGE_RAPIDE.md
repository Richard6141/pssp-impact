# 🚀 GUIDE DE DÉMARRAGE RAPIDE - PSSP IMPACT+ v2.0

## 📋 Checklist Avant de Commencer

### Prérequis Techniques
- [ ] PHP 8.2+ installé
- [ ] Composer installé
- [ ] Laravel 12 opérationnel
- [ ] Base de données configurée
- [ ] Git configuré
- [ ] Éditeur de code (VS Code recommandé)

### Prérequis Projet
- [ ] Sauvegarde de la base de données actuelle
- [ ] Sauvegarde du code actuel
- [ ] Création branche Git `feature/v2.0`
- [ ] Environnement de développement séparé de production

---

## 🎯 Option 1 : Démarrage Progressif (Recommandé)

### Jour 1 : Design System (2-3 heures)

**1. Créer le fichier CSS premium**
```bash
# Créer le fichier
touch public/backend/assets/css/premium-design.css
```

**2. Copier le CSS depuis** `.agent/PLAN_ARCHITECTURE_V2_FINAL.md` (section "Design System CSS")

**3. Inclure dans le layout**
```blade
<!-- Dans resources/views/layouts/back.blade.php, après style.css -->
<link href="{{asset('backend/assets/css/premium-design.css')}}" rel="stylesheet">
```

**4. Tester le nouveau design**
Créer une page de test:
```bash
php artisan make:controller TestDesignController
```

```php
// app/Http/Controllers/TestDesignController.php
public function index()
{
    return view('test-design');
}
```

```blade
<!-- resources/views/test-design.blade.php -->
@extends('layouts.back')

@section('content')
<main id="main" class="main">
    <div class="pagetitle">
        <h1>🎨 Test Design Premium</h1>
    </div>

    <section class="section">
        <div class="row">
            <!-- Stats Cards -->
            <div class="col-lg-3">
                <div class="stats-card animate-fade-in-up">
                    <div class="stats-card-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <i class="bi bi-collection"></i>
                    </div>
                    <h3 class="stats-card-value">245</h3>
                    <p class="stats-card-label">Collectes ce mois</p>
                    <span class="stats-card-trend trend-up">
                        <i class="bi bi-arrow-up"></i> +12%
                    </span>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="stats-card animate-fade-in-up" style="animation-delay: 0.1s;">
                    <div class="stats-card-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
                        <i class="bi bi-cash"></i>
                    </div>
                    <h3 class="stats-card-value">12.5M</h3>
                    <p class="stats-card-label">Revenus (FCFA)</p>
                    <span class="stats-card-trend trend-up">
                        <i class="bi bi-arrow-up"></i> +8%
                    </span>
                </div>
            </div>

            <!-- Premium Card -->
            <div class="col-lg-12 mt-4">
                <div class="premium-card animate-fade-in-up">
                    <div class="premium-card-header">
                        <h5 class="premium-card-title">📊 Tableau de Bord Premium</h5>
                        <button class="btn btn-gradient-primary">
                            <i class="bi bi-plus"></i> Nouvelle Action
                        </button>
                    </div>
                    <div class="premium-card-body">
                        <p>Votre nouveau design premium est opérationnel ! 🎉</p>
                    </div>
                </div>
            </div>

            <!-- Table Premium -->
            <div class="col-lg-12 mt-4">
                <div class="premium-card">
                    <div class="premium-card-body">
                        <table class="table table-premium">
                            <thead>
                                <tr>
                                    <th>Site</th>
                                    <th>Status</th>
                                    <th>Collectes</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Site Central</td>
                                    <td><span class="badge-modern badge-success">Actif</span></td>
                                    <td>45</td>
                                    <td>
                                        <button class="btn btn-sm btn-gradient-info">Voir</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection
```

**5. Ajouter la route de test**
```php
// routes/web.php
Route::get('/test-design', [TestDesignController::class, 'index'])->name('test.design');
```

**6. Tester dans le navigateur**
```
http://localhost/gestionDechets/public/test-design
```

---

### Jour 2-3 : Premier Module (Audit Logs)

**1. Créer la migration**
```bash
php artisan make:migration create_audit_logs_table
```

```php
// database/migrations/xxxx_create_audit_logs_table.php
public function up()
{
    Schema::create('audit_logs', function (Blueprint $table) {
        $table->uuid('audit_id')->primary();
        $table->uuid('user_id')->nullable();
        $table->string('event');
        $table->string('auditable_type');
        $table->string('auditable_id');
        $table->json('old_values')->nullable();
        $table->json('new_values')->nullable();
        $table->string('ip_address', 45)->nullable();
        $table->string('user_agent')->nullable();
        $table->string('module');
        $table->text('description')->nullable();
        $table->timestamps();
        
        $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');
        $table->index(['user_id', 'created_at']);
        $table->index(['auditable_type', 'auditable_id']);
        $table->index('module');
    });
}
```

**2. Exécuter la migration**
```bash
php artisan migrate
```

**3. Créer le modèle**
```bash
php artisan make:model AuditLog
```

```php
// app/Models/AuditLog.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AuditLog extends Model
{
    protected $primaryKey = 'audit_id';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'user_id',
        'event',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'module',
        'description',
    ];
    
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];
    
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
```

**4. Créer le service**
```bash
mkdir -p app/Services
touch app/Services/AuditService.php
```

```php
// app/Services/AuditService.php
<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditService
{
    public function log(string $event, Model $model, ?array $oldValues = null, ?array $newValues = null): AuditLog
    {
        return AuditLog::create([
            'user_id' => auth()->id(),
            'event' => $event,
            'auditable_type' => get_class($model),
            'auditable_id' => $model->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'module' => $this->getModuleFromModel($model),
            'description' => $this->generateDescription($event, $model),
        ]);
    }
    
    private function getModuleFromModel(Model $model): string
    {
        $className = class_basename($model);
        return strtolower($className);
    }
    
    private function generateDescription(string $event, Model $model): string
    {
        $modelName = class_basename($model);
        $user = auth()->user();
        $userName = $user ? "{$user->firstname} {$user->lastname}" : 'Système';
        
        return match($event) {
            'created' => "{$userName} a créé un(e) {$modelName}",
            'updated' => "{$userName} a modifié un(e) {$modelName}",
            'deleted' => "{$userName} a supprimé un(e) {$modelName}",
            'viewed' => "{$userName} a consulté un(e) {$modelName}",
            default => "{$userName} - {$event} sur {$modelName}",
        };
    }
}
```

**5. Créer le trait Auditable**
```bash
mkdir -p app/Traits
touch app/Traits/Auditable.php
```

```php
// app/Traits/Auditable.php
<?php

namespace App\Traits;

use App\Services\AuditService;

trait Auditable
{
    protected static function bootAuditable()
    {
        $auditService = app(AuditService::class);
        
        static::created(function ($model) use ($auditService) {
            $auditService->log('created', $model, null, $model->getAttributes());
        });
        
        static::updated(function ($model) use ($auditService) {
            $auditService->log('updated', $model, $model->getOriginal(), $model->getChanges());
        });
        
        static::deleted(function ($model) use ($auditService) {
            $auditService->log('deleted', $model, $model->getAttributes(), null);
        });
    }
}
```

**6. Utiliser le trait dans un modèle**
```php
// app/Models/Collecte.php
use App\Traits\Auditable;

class Collecte extends Model
{
    use HasFactory, SoftDeletes, Auditable; // Ajouter Auditable
    
    // ... reste du code
}
```

**7. Créer le contrôleur**
```bash
php artisan make:controller Admin/AuditController --resource
```

```php
// app/Http/Controllers/Admin/AuditController.php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $audits = AuditLog::with('user')
            ->when($request->search, fn($q, $v) => 
                $q->where('description', 'like', "%{$v}%")
            )
            ->when($request->module, fn($q, $v) => $q->where('module', $v))
            ->when($request->event, fn($q, $v) => $q->where('event', $v))
            ->orderBy('created_at', 'desc')
            ->paginate(50);
        
        return view('admin.audit.index', compact('audits'));
    }
    
    public function show(string $auditId)
    {
        $audit = AuditLog::with('user')->findOrFail($auditId);
        return view('admin.audit.show', compact('audit'));
    }
}
```

**8. Créer les vues**
```bash
mkdir -p resources/views/admin/audit
touch resources/views/admin/audit/index.blade.php
```

```blade
<!-- resources/views/admin/audit/index.blade.php -->
@extends('layouts.back')

@section('content')
<main id="main" class="main">
    <div class="pagetitle">
        <h1>📋 Journal d'Audit</h1>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="premium-card animate-fade-in-up">
                    <div class="premium-card-header">
                        <h5 class="premium-card-title">Historique des actions</h5>
                    </div>
                    <div class="premium-card-body">
                        <!-- Filtres -->
                        <form method="GET" class="mb-4">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <select name="module" class="form-select form-control-modern">
                                        <option value="">Tous les modules</option>
                                        <option value="collecte">Collectes</option>
                                        <option value="facture">Factures</option>
                                        <option value="site">Sites</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select name="event" class="form-select form-control-modern">
                                        <option value="">Toutes les actions</option>
                                        <option value="created">Créations</option>
                                        <option value="updated">Modifications</option>
                                        <option value="deleted">Suppressions</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="search" class="form-control form-control-modern" 
                                           placeholder="Rechercher..." value="{{ request('search') }}">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-gradient-primary w-100">
                                        <i class="bi bi-search"></i> Filtrer
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-premium">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Utilisateur</th>
                                        <th>Module</th>
                                        <th>Action</th>
                                        <th>Description</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($audits as $audit)
                                    <tr>
                                        <td>{{ $audit->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @if($audit->user)
                                                <span class="badge-modern" style="background: rgba(102, 126, 234, 0.1); color: #667eea;">
                                                    {{ $audit->user->firstname }} {{ $audit->user->lastname }}
                                                </span>
                                            @else
                                                <span class="badge-modern" style="background: rgba(100, 116, 139, 0.1); color: #64748b;">
                                                    Système
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge-modern" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                                                {{ ucfirst($audit->module) }}
                                            </span>
                                        </td>
                                        <td>
                                            @switch($audit->event)
                                                @case('created')
                                                    <span class="badge-modern" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                                                        Création
                                                    </span>
                                                    @break
                                                @case('updated')
                                                    <span class="badge-modern" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                                                        Modification
                                                    </span>
                                                    @break
                                                @case('deleted')
                                                    <span class="badge-modern" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                                                        Suppression
                                                    </span>
                                                    @break
                                            @endswitch
                                        </td>
                                        <td>{{ Str::limit($audit->description, 60) }}</td>
                                        <td>
                                            <a href="{{ route('admin.audit.show', $audit->audit_id) }}" 
                                               class="btn btn-sm btn-gradient-info">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Aucun enregistrement trouvé</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{ $audits->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection
```

**9. Ajouter la route**
```php
// routes/web.php
Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::resource('audit', AuditController::class)->only(['index', 'show']);
});
```

**10. Ajouter au menu sidebar**
```blade
<!-- Dans resources/views/layouts/partials/sidebar.blade.php -->
<li class="nav-item">
    <a class="nav-link collapsed" href="{{ route('admin.audit.index') }}">
        <i class="bi bi-journal-text"></i>
        <span>Journal d'Audit</span>
    </a>
</li>
```

**11. Tester**
1. Créer/modifier une collecte
2. Aller sur `/admin/audit`
3. Voir l'enregistrement de l'action !

---

## 🎯 Option 2 : Installation Complète Automatisée

Si vous voulez tout installer d'un coup, créons un script:

```bash
touch install-v2.sh
chmod +x install-v2.sh
```

```bash
#!/bin/bash

echo "🚀 Installation PSSP IMPACT+ v2.0"
echo "=================================="

# Installer les packages
echo "📦 Installation des packages..."
composer require maatwebsite/excel
composer require pragmarx/google2fa-laravel
composer require twilio/sdk
composer require --dev laravel/telescope
composer require --dev barryvdh/laravel-debugbar

# Publier les configs
echo "⚙️  Publication des configurations..."
php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider"
php artisan vendor:publish --provider="PragmaRX\Google2FALaravel\ServiceProvider"

# Créer les dossiers
echo "📁 Création des dossiers..."
mkdir -p app/Services
mkdir -p app/Traits
mkdir -p app/Exports
mkdir -p app/Imports
mkdir -p app/Jobs
mkdir -p resources/views/admin/audit
mkdir -p resources/views/admin/users
mkdir -p resources/views/tournees
mkdir -p resources/views/documents

# Copier le CSS premium
echo "🎨 Installation du design premium..."
cp .agent/premium-design.css public/backend/assets/css/

echo "✅ Installation terminée !"
echo "Prochaines étapes:"
echo "1. Lancer: php artisan migrate"
echo "2. Visiter: http://localhost/gestionDechets/public/test-design"
```

---

## 📚 Ressources Utiles

### Documentation
- [Laravel 12](https://laravel.com/docs/12.x)
- [Spatie Permissions](https://spatie.be/docs/laravel-permission)
- [Laravel Excel](https://docs.laravel-excel.com)
- [Twilio PHP](https://www.twilio.com/docs/libraries/php)
- [Leaflet.js](https://leafletjs.com)

### Tutoriels Vidéo Recommandés
- Laravel Service Pattern
- Laravel Repository Pattern
- Laravel Jobs & Queues
- Laravel Events & Listeners

---

## 🆘 Aide Rapide

### Erreurs Courantes

**1. Erreur: Class 'App\Services\AuditService' not found**
```bash
composer dump-autoload
```

**2. Erreur: Table 'audit_logs' doesn't exist**
```bash
php artisan migrate
```

**3. CSS ne s'applique pas**
```bash
php artisan cache:clear
php artisan view:clear
# Forcer le refresh du navigateur (Ctrl+Shift+R)
```

**4. UUID not generating**
Vérifier que le trait boot() est bien appelé dans le modèle.

---

## ✅ Validation du Setup

Après l'installation, vérifier:

- [ ] Page de test design accessible
- [ ] Design premium visible
- [ ] Migration audit_logs créée
- [ ] Modèle AuditLog fonctionne
- [ ] Service AuditService fonctionne
- [ ] Trait Auditable fonctionne
- [ ] Page admin/audit accessible
- [ ] Logs s'enregistrent automatiquement

---

## 📞 Prêt à Continuer ?

Une fois ces étapes complétées, vous pouvez:

1. **Continuer avec le roadmap** - Semaine 1, Jour 4-5 (2FA)
2. **Passer à un autre module** - Selon vos priorités
3. **Personnaliser le design** - Ajuster les couleurs, animations
4. **Demander de l'aide** - Pour un module spécifique

**Bonne chance ! 🎉**
