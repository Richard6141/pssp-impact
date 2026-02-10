# 🚀 PSSP IMPACT+ v2.0 - Architecture Complète

## 📊 Analyse de l'Existant

### Technologies Actuelles
- **Backend**: Laravel 12 (PHP 8.2)
- **Frontend**: Blade + Bootstrap 5 + Vanilla JS
- **Database**: SQLite (migration vers MySQL/PostgreSQL recommandée pour production)
- **Authentication**: Laravel Auth + Spatie Permissions
- **PDF**: DomPDF
- **Design**: NiceAdmin Template Bootstrap

### Modèles Existants
- User (UUID, géolocalisation, soft deletes)
- Site (sites de collecte)
- Collecte (collectes de déchets)
- Facture (facturation)
- Paiement (paiements)
- Incident (incidents)
- TypeDechet (types de déchets)
- Validation (validations)
- Observation (observations)
- EcritureComptable (comptabilité de base)

---

## 🎨 Nouveau Design System

### 1. Palette de Couleurs Moderne

```css
:root {
  /* Primaires */
  --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  --success-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
  --danger-gradient: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  --info-gradient: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  
  /* Neutres */
  --dark-900: #0f172a;
  --dark-800: #1e293b;
  --dark-700: #334155;
  --gray-600: #475569;
  --gray-500: #64748b;
  --gray-400: #94a3b8;
  --gray-300: #cbd5e1;
  --gray-200: #e2e8f0;
  --gray-100: #f1f5f9;
  --white: #ffffff;
  
  /* Accents */
  --accent-purple: #9333ea;
  --accent-pink: #ec4899;
  --accent-cyan: #06b6d4;
  --accent-emerald: #10b981;
  
  /* Glassmorphism */
  --glass-bg: rgba(255, 255, 255, 0.1);
  --glass-border: rgba(255, 255, 255, 0.2);
  --backdrop-blur: 20px;
  
  /* Ombres */
  --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
  --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.07);
  --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
  --shadow-xl: 0 20px 25px rgba(0, 0, 0, 0.15);
  --shadow-2xl: 0 25px 50px rgba(0, 0, 0, 0.25);
  
  /* Animations */
  --transition-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);
  --transition-base: 300ms cubic-bezier(0.4, 0, 0.2, 1);
  --transition-slow: 500ms cubic-bezier(0.4, 0, 0.2, 1);
}
```

### 2. Composants UI Premium

#### Cards modernes avec glassmorphism
```css
.premium-card {
  background: var(--glass-bg);
  backdrop-filter: blur(var(--backdrop-blur));
  border: 1px solid var(--glass-border);
  border-radius: 16px;
  box-shadow: var(--shadow-lg);
  transition: all var(--transition-base);
}

.premium-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-xl);
}
```

#### Buttons avec gradients
```css
.btn-gradient-primary {
  background: var(--primary-gradient);
  border: none;
  color: white;
  padding: 12px 24px;
  border-radius: 12px;
  font-weight: 600;
  box-shadow: var(--shadow-md);
  transition: all var(--transition-base);
}

.btn-gradient-primary:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-lg);
}
```

#### Animations micro-interactions
```css
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in-up {
  animation: fadeInUp 0.5s ease-out;
}
```

### 3. Dashboard Premium

- **Layout**: Sidebar fixe avec glassmorphism
- **Header**: Sticky avec gradient background
- **Cards**: Design "neumorphique" avec micro-animations
- **Charts**: ApexCharts avec thème personnalisé
- **Tables**: DataTables avec design moderne
- **Modals**: Backdrop blur + animations fluides

---

## 🏗️ Architecture des 10 Nouvelles Fonctionnalités

## 1️⃣ Gouvernance & Sécurité

### Base de données

```php
// Migration: create_audit_logs_table
Schema::create('audit_logs', function (Blueprint $table) {
    $table->uuid('audit_id')->primary();
    $table->uuid('user_id')->nullable();
    $table->string('event'); // created, updated, deleted, viewed, exported
    $table->string('auditable_type'); // Model name
    $table->string('auditable_id'); // Model ID
    $table->json('old_values')->nullable();
    $table->json('new_values')->nullable();
    $table->string('ip_address', 45)->nullable();
    $table->string('user_agent')->nullable();
    $table->string('module'); // collectes, factures, etc.
    $table->text('description')->nullable();
    $table->timestamps();
    
    $table->index(['user_id', 'created_at']);
    $table->index(['auditable_type', 'auditable_id']);
    $table->index('module');
});

// Migration: create_role_permissions_matrix
Schema::create('role_permissions_matrix', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('role_id');
    $table->string('module'); // collectes, factures, sites, etc.
    $table->json('permissions'); // [view, create, edit, delete, validate, export]
    $table->json('field_restrictions')->nullable(); // Champs accessibles
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    
    $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
    $table->unique(['role_id', 'module']);
});

// Migration: create_user_sessions_table
Schema::create('user_sessions', function (Blueprint $table) {
    $table->uuid('session_id')->primary();
    $table->uuid('user_id');
    $table->string('ip_address', 45);
    $table->string('user_agent');
    $table->string('device_type'); // mobile, tablet, desktop
    $table->string('browser');
    $table->string('os');
    $table->timestamp('last_activity');
    $table->timestamp('logged_in_at');
    $table->timestamp('logged_out_at')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    
    $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
    $table->index(['user_id', 'is_active']);
});

// Migration: create_password_policies_table
Schema::create('password_policies', function (Blueprint $table) {
    $table->id();
    $table->integer('min_length')->default(8);
    $table->boolean('require_uppercase')->default(true);
    $table->boolean('require_lowercase')->default(true);
    $table->boolean('require_numbers')->default(true);
    $table->boolean('require_special_chars')->default(true);
    $table->integer('expiry_days')->default(90);
    $table->integer('max_attempts')->default(5);
    $table->integer('lockout_duration')->default(15); // minutes
    $table->json('password_history')->default('[]'); // Nombre de mots de passe à retenir
    $table->timestamps();
});

// Migration: create_two_factor_auth_table
Schema::create('two_factor_auth', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('user_id');
    $table->string('secret_key')->nullable();
    $table->json('recovery_codes')->nullable();
    $table->string('method'); // totp, sms, email
    $table->string('phone')->nullable();
    $table->boolean('is_enabled')->default(false);
    $table->timestamp('enabled_at')->nullable();
    $table->timestamp('last_used_at')->nullable();
    $table->timestamps();
    
    $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
    $table->unique('user_id');
});

// Ajout à users table
Schema::table('users', function (Blueprint $table) {
    $table->integer('failed_login_attempts')->default(0)->after('password');
    $table->timestamp('locked_until')->nullable()->after('failed_login_attempts');
    $table->timestamp('password_changed_at')->nullable()->after('locked_until');
    $table->boolean('requires_2fa')->default(false)->after('password_changed_at');
});
```

### Services

```php
// app/Services/AuditService.php
class AuditService
{
    public function log(string $event, Model $model, ?array $oldValues = null, ?array $newValues = null)
    {
        AuditLog::create([
            'audit_id' => Str::uuid(),
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
    
    public function export(array $filters): Collection
    {
        return AuditLog::with('user')
            ->when($filters['user_id'] ?? null, fn($q, $v) => $q->where('user_id', $v))
            ->when($filters['module'] ?? null, fn($q, $v) => $q->where('module', $v))
            ->when($filters['event'] ?? null, fn($q, $v) => $q->where('event', $v))
            ->when($filters['date_from'] ?? null, fn($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['date_to'] ?? null, fn($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->orderBy('created_at', 'desc')
            ->get();
    }
}

// app/Services/TwoFactorAuthService.php
class TwoFactorAuthService
{
    public function generateSecret(User $user): string
    {
        $secret = Google2FA::generateSecretKey();
        
        TwoFactorAuth::updateOrCreate(
            ['user_id' => $user->user_id],
            [
                'id' => Str::uuid(),
                'secret_key' => encrypt($secret),
                'recovery_codes' => $this->generateRecoveryCodes(),
                'method' => 'totp',
            ]
        );
        
        return $secret;
    }
    
    public function verify(User $user, string $code): bool
    {
        $twoFactor = $user->twoFactorAuth;
        
        if (!$twoFactor || !$twoFactor->is_enabled) {
            return false;
        }
        
        $secret = decrypt($twoFactor->secret_key);
        $valid = Google2FA::verifyKey($secret, $code);
        
        if ($valid) {
            $twoFactor->update(['last_used_at' => now()]);
        }
        
        return $valid;
    }
    
    private function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(Str::random(10));
        }
        return $codes;
    }
}

// app/Services/SessionManagementService.php
class SessionManagementService
{
    public function recordSession(User $user)
    {
        $agent = new Agent();
        
        return UserSession::create([
            'session_id' => Str::uuid(),
            'user_id' => $user->user_id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'device_type' => $agent->isDesktop() ? 'desktop' : ($agent->isTablet() ? 'tablet' : 'mobile'),
            'browser' => $agent->browser(),
            'os' => $agent->platform(),
            'last_activity' => now(),
            'logged_in_at' => now(),
        ]);
    }
    
    public function terminateSession(string $sessionId)
    {
        $session = UserSession::findOrFail($sessionId);
        $session->update([
            'logged_out_at' => now(),
            'is_active' => false,
        ]);
        
        // Invalider la session Laravel si c'est la session courante
        if (session()->getId() === $sessionId) {
            Auth::logout();
        }
    }
    
    public function getActiveSessions(User $user)
    {
        return UserSession::where('user_id', $user->user_id)
            ->where('is_active', true)
            ->orderBy('last_activity', 'desc')
            ->get();
    }
}
```

### Controllers

```php
// app/Http/Controllers/Admin/AuditController.php
class AuditController extends Controller
{
    public function __construct(private AuditService $auditService) {}
    
    public function index(Request $request)
    {
        $audits = AuditLog::with('user')
            ->when($request->search, fn($q, $v) => 
                $q->where('description', 'like', "%{$v}%")
                  ->orWhereHas('user', fn($q) => 
                      $q->where('firstname', 'like', "%{$v}%")
                        ->orWhere('lastname', 'like', "%{$v}%")
                  )
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
    
    public function export(Request $request)
    {
        $audits = $this->auditService->export($request->all());
        return Excel::download(new AuditExport($audits), 'audit-' . now()->format('Y-m-d') . '.xlsx');
    }
}

// app/Http/Controllers/Auth/TwoFactorController.php
class TwoFactorController extends Controller
{
    public function __construct(private TwoFactorAuthService $twoFactorService) {}
    
    public function enable()
    {
        $user = auth()->user();
        $secret = $this->twoFactorService->generateSecret($user);
        
        $qrCodeUrl = Google2FA::getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );
        
        return view('auth.2fa.enable', compact('qrCodeUrl', 'secret'));
    }
    
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|numeric|digits:6',
        ]);
        
        $user = auth()->user();
        
        if ($this->twoFactorService->verify($user, $request->code)) {
            $user->twoFactorAuth->update(['is_enabled' => true, 'enabled_at' => now()]);
            return redirect()->route('profile.show')->with('success', '2FA activé avec succès');
        }
        
        return back()->with('error', 'Code invalide');
    }
}

// app/Http/Controllers/Admin/SessionController.php
class SessionController extends Controller
{
    public function __construct(private SessionManagementService $sessionService) {}
    
    public function index()
    {
        $sessions = $this->sessionService->getActiveSessions(auth()->user());
        return view('admin.sessions.index', compact('sessions'));
    }
    
    public function destroy(string $sessionId)
    {
        $this->sessionService->terminateSession($sessionId);
        return back()->with('success', 'Session terminée avec succès');
    }
}
```

### Vues (Exemples)

```blade
{{-- resources/views/admin/audit/index.blade.php --}}
@extends('layouts.back')

@section('content')
<main id="main" class="main">
    <div class="pagetitle">
        <h1>📋 Journal d'Audit</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
                <li class="breadcrumb-item">Administration</li>
                <li class="breadcrumb-item active">Audit</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card premium-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title">Historique des actions</h5>
                            <a href="{{ route('admin.audit.export') }}" class="btn btn-gradient-primary">
                                <i class="bi bi-download"></i> Exporter
                            </a>
                        </div>

                        {{-- Filtres --}}
                        <form method="GET" class="mb-4">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <select name="module" class="form-select">
                                        <option value="">Tous les modules</option>
                                        <option value="collectes">Collectes</option>
                                        <option value="factures">Factures</option>
                                        <option value="sites">Sites</option>
                                        <option value="users">Utilisateurs</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select name="event" class="form-select">
                                        <option value="">Toutes les actions</option>
                                        <option value="created">Créations</option>
                                        <option value="updated">Modifications</option>
                                        <option value="deleted">Suppressions</option>
                                        <option value="viewed">Consultations</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="search" class="form-control" placeholder="Rechercher...">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-search"></i> Filtrer
                                    </button>
                                </div>
                            </div>
                        </form>

                        {{-- Table --}}
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Utilisateur</th>
                                        <th>Module</th>
                                        <th>Action</th>
                                        <th>Description</th>
                                        <th>IP</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($audits as $audit)
                                    <tr>
                                        <td>{{ $audit->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @if($audit->user)
                                                <span class="badge bg-info">
                                                    {{ $audit->user->firstname }} {{ $audit->user->lastname }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">Système</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ ucfirst($audit->module) }}</span>
                                        </td>
                                        <td>
                                            @switch($audit->event)
                                                @case('created')
                                                    <span class="badge bg-success">Création</span>
                                                    @break
                                                @case('updated')
                                                    <span class="badge bg-warning">Modification</span>
                                                    @break
                                                @case('deleted')
                                                    <span class="badge bg-danger">Suppression</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-info">{{ ucfirst($audit->event) }}</span>
                                            @endswitch
                                        </td>
                                        <td>{{ Str::limit($audit->description, 50) }}</td>
                                        <td><code>{{ $audit->ip_address }}</code></td>
                                        <td>
                                            <a href="{{ route('admin.audit.show', $audit->audit_id) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">Aucun enregistrement trouvé</td>
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

---

## 2️⃣ Collectes & Opérations

### Base de données

```php
// Migration: enhance_collectes_table
Schema::table('collectes', function (Blueprint $table) {
    $table->uuid('tournee_id')->nullable()->after('site_id');
    $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal')->after('status');
    $table->timestamp('planned_at')->nullable()->after('date_collecte');
    $table->timestamp('started_at')->nullable()->after('planned_at');
    $table->timestamp('completed_at')->nullable()->after('started_at');
    $table->integer('sla_minutes')->nullable()->after('completed_at');
    $table->boolean('sla_respected')->nullable()->after('sla_minutes');
    $table->json('gps_tracking')->nullable()->after('latitude'); // [{lat, lng, timestamp}]
    $table->json('attachments')->nullable()->after('gps_tracking'); // photos, docs
    $table->text('agent_notes')->nullable()->after('attachments');
    $table->decimal('estimated_duration', 8, 2)->nullable()->after('agent_notes'); // heures
    $table->decimal('actual_duration', 8, 2)->nullable()->after('estimated_duration');
    
    $table->foreign('tournee_id')->references('tournee_id')->on('tournees')->onDelete('set null');
    $table->index(['status', 'planned_at']);
    $table->index('sla_respected');
});

// Migration: create_tournees_table
Schema::create('tournees', function (Blueprint $table) {
    $table->uuid('tournee_id')->primary();
    $table->string('code')->unique(); // TRN-2026-001
    $table->string('name');
    $table->uuid('agent_id')->nullable();
    $table->date('planned_date');
    $table->time('start_time')->nullable();
    $table->time('end_time')->nullable();
    $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled'])->default('planned');
    $table->integer('total_sites')->default(0);
    $table->integer('completed_sites')->default(0);
    $table->text('description')->nullable();
    $table->json('optimized_route')->nullable(); // Ordre optimal des sites
    $table->timestamps();
    $table->softDeletes();
    
    $table->foreign('agent_id')->references('user_id')->on('users')->onDelete('set null');
    $table->index(['status', 'planned_date']);
});

// Migration: create_tournee_sites_table (relation many-to-many)
Schema::create('tournee_sites', function (Blueprint $table) {
    $table->id();
    $table->uuid('tournee_id');
    $table->uuid('site_id');
    $table->integer('order')->default(0); // Ordre de visite
    $table->enum('status', ['pending', 'completed', 'skipped'])->default('pending');
    $table->timestamp('visited_at')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();
    
    $table->foreign('tournee_id')->references('tournee_id')->on('tournees')->onDelete('cascade');
    $table->foreign('site_id')->references('site_id')->on('sites')->onDelete('cascade');
    $table->unique(['tournee_id', 'site_id']);
});

// Migration: enhance_incidents_table
Schema::table('incidents', function (Blueprint $table) {
    $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium')->after('description');
    $table->string('incident_type')->nullable()->after('severity'); // equipment, access, safety, etc.
    $table->uuid('assigned_to')->nullable()->after('reported_by');
    $table->timestamp('acknowledged_at')->nullable()->after('reported_at');
    $table->timestamp('resolved_at')->nullable()->after('acknowledged_at');
    $table->text('resolution_notes')->nullable()->after('status');
    $table->json('photos')->nullable()->after('resolution_notes');
    $table->json('attachments')->nullable()->after('photos');
    $table->string('root_cause')->nullable()->after('attachments');
    $table->json('affected_equipment')->nullable()->after('root_cause');
    
    $table->foreign('assigned_to')->references('user_id')->on('users')->onDelete('set null');
    $table->index(['status', 'severity']);
});

// Migration: create_sla_configurations_table
Schema::create('sla_configurations', function (Blueprint $table) {
    $table->id();
    $table->string('service_type'); // collecte_standard, collecte_express, incident_resolution
    $table->integer('target_minutes'); // Temps cible
    $table->integer('warning_threshold')->default(80); // Seuil d'alerte (%)
    $table->boolean('send_alerts')->default(true);
    $table->json('alert_recipients')->nullable(); // user_ids ou emails
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    
    $table->unique('service_type');
});

// Migration: create_collecte_tracking_table
Schema::create('collecte_tracking', function (Blueprint $table) {
    $table->uuid('tracking_id')->primary();
    $table->uuid('collecte_id');
    $table->decimal('latitude', 10, 7);
    $table->decimal('longitude', 10, 7);
    $table->decimal('accuracy', 8, 2)->nullable(); // précision en mètres
    $table->decimal('speed', 8, 2)->nullable(); // km/h
    $table->string('status'); // en_route, sur_place, collecte_en_cours, terminee
    $table->timestamp('recorded_at');
    $table->timestamps();
    
    $table->foreign('collecte_id')->references('collecte_id')->on('collectes')->onDelete('cascade');
    $table->index(['collecte_id', 'recorded_at']);
});
```

### Services

```php
// app/Services/TourneeService.php
class TourneeService
{
    public function create(array $data): Tournee
    {
        $tournee = Tournee::create([
            'tournee_id' => Str::uuid(),
            'code' => $this->generateCode(),
            'name' => $data['name'],
            'agent_id' => $data['agent_id'] ?? null,
            'planned_date' => $data['planned_date'],
            'start_time' => $data['start_time'] ?? null,
            'description' => $data['description'] ?? null,
        ]);
        
        // Attacher les sites
        if (isset($data['sites'])) {
            $this->attachSites($tournee, $data['sites']);
        }
        
        // Optimiser la route si demandé
        if ($data['optimize_route'] ?? false) {
            $this->optimizeRoute($tournee);
        }
        
        return $tournee->load('sites', 'agent');
    }
    
    public function optimizeRoute(Tournee $tournee): void
    {
        $sites = $tournee->sites()->with('site')->get();
        
        // Algorithme d'optimisation (plus proche voisin simplifié)
        // En production, utiliser une API comme Google Maps Distance Matrix
        $optimized = $this->nearestNeighborTSP($sites);
        
        // Mettre à jour l'ordre
        foreach ($optimized as $index => $siteRelation) {
            TourneeSite::where('tournee_id', $tournee->tournee_id)
                ->where('site_id', $siteRelation->site_id)
                ->update(['order' => $index + 1]);
        }
        
        $tournee->update([
            'optimized_route' => $optimized->pluck('site.name')->toArray(),
        ]);
    }
    
    private function nearestNeighborTSP(Collection $sites): Collection
    {
        if ($sites->count() <= 1) return $sites;
        
        $visited = collect();
        $current = $sites->first();
        $visited->push($current);
        $remaining = $sites->except($current->id);
        
        while ($remaining->isNotEmpty()) {
            $nearest = $remaining->sortBy(function ($site) use ($current) {
                return $this->calculateDistance(
                    $current->site->latitude, 
                    $current->site->longitude,
                    $site->site->latitude, 
                    $site->site->longitude
                );
            })->first();
            
            $visited->push($nearest);
            $remaining = $remaining->except($nearest->id);
            $current = $nearest;
        }
        
        return $visited;
    }
    
    private function calculateDistance($lat1, $lon1, $lat2, $lon2): float
    {
        // Formule de Haversine
        $earthRadius = 6371; // km
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
    }
    
    public function assignAgent(Tournee $tournee, string $agentId): void
    {
        $tournee->update(['agent_id' => $agentId]);
        
        // Notifier l'agent
        $agent = User::findOrFail($agentId);
        Notification::send($agent, new TourneeAssignedNotification($tournee));
    }
}

// app/Services/SLAService.php
class SLAService
{
    public function checkCollecteSLA(Collecte $collecte): bool
    {
        $config = SLAConfiguration::where('service_type', 'collecte_standard')->first();
        
        if (!$config || !$collecte->planned_at || !$collecte->completed_at) {
            return true; // Pas de SLA configuré ou collecte non complétée
        }
        
        $duration = $collecte->planned_at->diffInMinutes($collecte->completed_at);
        $respected = $duration <= $config->target_minutes;
        
        $collecte->update([
            'sla_minutes' => $duration,
            'sla_respected' => $respected,
        ]);
        
        // Envoyer alerte si SLA non respecté
        if (!$respected && $config->send_alerts) {
            $this->sendSLAAlert($collecte, $config);
        }
        
        return $respected;
    }
    
    public function getMetrics(Carbon $startDate, Carbon $endDate): array
    {
        $collectes = Collecte::whereBetween('completed_at', [$startDate, $endDate])
            ->whereNotNull('sla_respected')
            ->get();
        
        $total = $collectes->count();
        $respected = $collectes->where('sla_respected', true)->count();
        $percentage = $total > 0 ? ($respected / $total) * 100 : 0;
        
        return [
            'total_collectes' => $total,
            'sla_respected' => $respected,
            'sla_not_respected' => $total - $respected,
            'respect_percentage' => round($percentage, 2),
            'average_duration' => round($collectes->avg('sla_minutes'), 2),
        ];
    }
}

// app/Services/CollecteTrackingService.php
class CollecteTrackingService
{
    public function recordPosition(Collecte $collecte, array $data): CollecteTracking
    {
        return CollecteTracking::create([
            'tracking_id' => Str::uuid(),
            'collecte_id' => $collecte->collecte_id,
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'accuracy' => $data['accuracy'] ?? null,
            'speed' => $data['speed'] ?? null,
            'status' => $data['status'],
            'recorded_at' => now(),
        ]);
    }
    
    public function getTrackingData(Collecte $collecte): Collection
    {
        return CollecteTracking::where('collecte_id', $collecte->collecte_id)
            ->orderBy('recorded_at')
            ->get();
    }
    
    public function calculateTotalDistance(Collecte $collecte): float
    {
        $points = $this->getTrackingData($collecte);
        $totalDistance = 0;
        
        for ($i = 0; $i < $points->count() - 1; $i++) {
            $totalDistance += $this->calculateDistance(
                $points[$i]->latitude,
                $points[$i]->longitude,
                $points[$i + 1]->latitude,
                $points[$i + 1]->longitude
            );
        }
        
        return round($totalDistance, 2);
    }
}
```

---

## 3️⃣ Facturation & Paiements

### Base de données

```php
// Migration: enhance_factures_table
Schema::table('factures', function (Blueprint $table) {
    $table->enum('workflow_status', ['brouillon', 'validee', 'envoyee', 'payee', 'annulee'])
        ->default('brouillon')
        ->after('status');
    $table->uuid('validated_by')->nullable()->after('comptable_id');
    $table->timestamp('validated_at')->nullable()->after('validated_by');
    $table->timestamp('sent_at')->nullable()->after('validated_at');
    $table->integer('relance_count')->default(0)->after('sent_at');
    $table->timestamp('last_relance_at')->nullable()->after('relance_count');
    $table->date('due_date')->nullable()->after('date_facture');
    $table->boolean('is_overdue')->default(false)->after('due_date');
    $table->json('auto_generation_params')->nullable()->after('is_overdue');
    
    $table->foreign('validated_by')->references('user_id')->on('users')->onDelete('set null');
    $table->index(['workflow_status', 'due_date']);
    $table->index('is_overdue');
});

// Migration: enhance_paiements_table
Schema::table('paiements', function (Blueprint $table) {
    $table->enum('payment_method', ['cash', 'bank_transfer', 'check', 'momo', 'om', 'wave', 'paypal'])
        ->default('cash')
        ->after('montant');
    $table->string('transaction_reference')->nullable()->after('payment_method');
    $table->string('payment_provider')->nullable()->after('transaction_reference'); // mtn, orange, wave
    $table->json('provider_response')->nullable()->after('payment_provider');
    $table->enum('verification_status', ['pending', 'verified', 'failed'])
        ->default('pending')
        ->after('provider_response');
    $table->uuid('verified_by')->nullable()->after('verification_status');
    $table->timestamp('verified_at')->nullable()->after('verified_by');
    $table->json('reconciliation_data')->nullable()->after('verified_at');
    
    $table->foreign('verified_by')->references('user_id')->on('users')->onDelete('set null');
    $table->index(['payment_method', 'verification_status']);
});

// Migration: create_facture_templates_table
Schema::create('facture_templates', function (Blueprint $table) {
    $table->uuid('template_id')->primary();
    $table->string('name');
    $table->string('code')->unique();
    $table->enum('frequency', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly']);
    $table->integer('frequency_value')->default(1); // Ex: tous les 2 mois
    $table->json('filters'); // Critères de sélection des collectes
    $table->boolean('auto_send')->default(false);
    $table->json('recipients')->nullable(); // Emails
    $table->boolean('is_active')->default(true);
    $table->timestamp('last_generated_at')->nullable();
    $table->timestamp('next_generation_at')->nullable();
    $table->timestamps();
});

// Migration: create_payment_relances_table
Schema::create('payment_relances', function (Blueprint $table) {
    $table->uuid('relance_id')->primary();
    $table->uuid('facture_id');
    $table->integer('relance_number');
    $table->enum('channel', ['email', 'whatsapp', 'sms'])->default('email');
    $table->text('message');
    $table->enum('status', ['pending', 'sent', 'delivered', 'failed'])->default('pending');
    $table->timestamp('sent_at')->nullable();
    $table->json('response_data')->nullable();
    $table->uuid('sent_by')->nullable();
    $table->timestamps();
    
    $table->foreign('facture_id')->references('facture_id')->on('factures')->onDelete('cascade');
    $table->foreign('sent_by')->references('user_id')->on('users')->onDelete('set null');
    $table->index(['facture_id', 'relance_number']);
});

// Migration: create_bank_reconciliations_table
Schema::create('bank_reconciliations', function (Blueprint $table) {
    $table->uuid('reconciliation_id')->primary();
    $table->date('reconciliation_date');
    $table->string('bank_account');
    $table->decimal('bank_balance', 15, 2);
    $table->decimal('system_balance', 15, 2);
    $table->decimal('difference', 15, 2);
    $table->enum('status', ['pending', 'in_progress', 'completed', 'discrepancy'])->default('pending');
    $table->uuid('performed_by');
    $table->text('notes')->nullable();
    $table->json('matched_payments')->nullable();
    $table->json('unmatched_payments')->nullable();
    $table->timestamps();
    
    $table->foreign('performed_by')->references('user_id')->on('users')->onDelete('cascade');
    $table->index('reconciliation_date');
});

// Migration: create_mobile_money_configurations_table
Schema::create('mobile_money_configurations', function (Blueprint $table) {
    $table->id();
    $table->string('provider'); // mtn, orange, wave
    $table->string('api_key')->nullable();
    $table->string('api_secret')->nullable();
    $table->string('merchant_id')->nullable();
    $table->string('callback_url')->nullable();
    $table->boolean('is_sandbox')->default(true);
    $table->boolean('is_active')->default(false);
    $table->json('supported_countries')->nullable();
    $table->timestamps();
    
    $table->unique('provider');
});
```

### Services

```php
// app/Services/FactureWorkflowService.php
class FactureWorkflowService
{
    public function createDraft(array $data): Facture
    {
        return Facture::create([
            'facture_id' => Str::uuid(),
            'numero_facture' => $this->generateNumero(),
            'site_id' => $data['site_id'],
            'comptable_id' => auth()->id(),
            'date_facture' => now(),
            'due_date' => now()->addDays(30),
            'workflow_status' => 'brouillon',
            // ... autres champs
        ]);
    }
    
    public function validate(Facture $facture): void
    {
        if ($facture->workflow_status !== 'brouillon') {
            throw new \Exception('Seules les factures en brouillon peuvent être validées');
        }
        
        $facture->update([
            'workflow_status' => 'validee',
            'validated_by' => auth()->id(),
            'validated_at' => now(),
        ]);
        
        event(new FactureValidated($facture));
    }
    
    public function send(Facture $facture, array $recipients = []): void
    {
        if (!in_array($facture->workflow_status, ['validee', 'envoyee'])) {
            throw new \Exception('La facture doit être validée avant envoi');
        }
        
        $pdf = PDF::loadView('factures.pdf', compact('facture'));
        
        foreach ($recipients as $email) {
            Mail::to($email)->send(new FactureMail($facture, $pdf));
        }
        
        $facture->update([
            'workflow_status' => 'envoyee',
            'sent_at' => now(),
        ]);
    }
    
    public function markAsPaid(Facture $facture, Paiement $paiement): void
    {
        $facture->update([
            'workflow_status' => 'payee',
            'status' => 'payée',
        ]);
        
        event(new FacturePaid($facture, $paiement));
    }
}

// app/Services/FactureAutoGenerationService.php
class FactureAutoGenerationService
{
    public function generateFromTemplate(FactureTemplate $template): Collection
    {
        $collectes = $this->getCollectesForTemplate($template);
        $factures = collect();
        
        // Grouper par site si nécessaire
        $groupedCollectes = $collectes->groupBy('site_id');
        
        foreach ($groupedCollectes as $siteId => $siteCollectes) {
            $facture = $this->createFactureFromCollectes($siteId, $siteCollectes);
            $factures->push($facture);
        }
        
        $template->update([
            'last_generated_at' => now(),
            'next_generation_at' => $this->calculateNextGeneration($template),
        ]);
        
        return $factures;
    }
    
    private function calculateNextGeneration(FactureTemplate $template): Carbon
    {
        $now = now();
        
        return match($template->frequency) {
            'daily' => $now->addDays($template->frequency_value),
            'weekly' => $now->addWeeks($template->frequency_value),
            'monthly' => $now->addMonths($template->frequency_value),
            'quarterly' => $now->addMonths(3 * $template->frequency_value),
            'yearly' => $now->addYears($template->frequency_value),
        };
    }
}

// app/Services/PaymentRelanceService.php
class PaymentRelanceService
{
    public function sendRelance(Facture $facture, string $channel = 'email'): PaymentRelance
    {
        $relanceNumber = $facture->relance_count + 1;
        $message = $this->generateMessage($facture, $relanceNumber);
        
        $relance = PaymentRelance::create([
            'relance_id' => Str::uuid(),
            'facture_id' => $facture->facture_id,
            'relance_number' => $relanceNumber,
            'channel' => $channel,
            'message' => $message,
            'sent_by' => auth()->id(),
        ]);
        
        try {
            match($channel) {
                'email' => $this->sendEmail($facture, $message),
                'whatsapp' => $this->sendWhatsApp($facture, $message),
                'sms' => $this->sendSMS($facture, $message),
            };
            
            $relance->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);
            
            $facture->increment('relance_count');
            $facture->update(['last_relance_at' => now()]);
            
        } catch (\Exception $e) {
            $relance->update([
                'status' => 'failed',
                'response_data' => ['error' => $e->getMessage()],
            ]);
        }
        
        return $relance;
    }
    
    private function generateMessage(Facture $facture, int $relanceNumber): string
    {
        $templates = [
            1 => "Bonjour, nous vous rappelons que la facture {numero} d'un montant de {montant} est échue depuis le {due_date}.",
            2 => "Deuxième relance : La facture {numero} ({montant}) n'a toujours pas été réglée. Merci de régulariser votre situation.",
            3 => "DERNIÈRE RELANCE : Veuillez régler la facture {numero} ({montant}) sous 48h, faute de quoi nous serons contraints de suspendre nos services.",
        ];
        
        $template = $templates[$relanceNumber] ?? $templates[3];
        
        return str_replace(
            ['{numero}', '{montant}', '{due_date}'],
            [$facture->numero_facture, $facture->montant_total, $facture->due_date->format('d/m/Y')],
            $template
        );
    }
}

// app/Services/MobileMoneyService.php
class MobileMoneyService
{
    public function initiate Payment(Facture $facture, string $provider, string $phoneNumber): array
    {
        $config = MobileMoneyConfiguration::where('provider', $provider)
            ->where('is_active', true)
            ->firstOrFail();
        
        // Implémentation spécifique par provider
        return match($provider) {
            'mtn' => $this->initiateMTNPayment($facture, $phoneNumber, $config),
            'orange' => $this->initiateOrangePayment($facture, $phoneNumber, $config),
            'wave' => $this->initiateWavePayment($facture, $phoneNumber, $config),
            default => throw new \Exception("Provider non supporté: {$provider}"),
        };
    }
    
    private function initiateMTNPayment(Facture $facture, string $phone, $config): array
    {
        $client = new \GuzzleHttp\Client();
        
        $url = $config->is_sandbox 
            ? 'https://sandbox.momodeveloper.mtn.com/collection/v1_0/requesttopay'
            : 'https://proxy.momoapi.mtn.com/collection/v1_0/requesttopay';
        
        $response = $client->post($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getMTNAccessToken($config),
                'X-Reference-Id' => Str::uuid(),
                'X-Target-Environment' => $config->is_sandbox ? 'sandbox' : 'production',
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'amount' => $facture->montant_total,
                'currency' => 'XOF', // ou votre devise
                'externalId' => $facture->facture_id,
                'payer' => [
                    'partyIdType' => 'MSISDN',
                    'partyId' => $phone,
                ],
                'payerMessage' => "Paiement facture {$facture->numero_facture}",
                'payeeNote' => "PSSP IMPACT+",
            ],
        ]);
        
        return [
            'success' => $response->getStatusCode() === 202,
            'transaction_id' => $response->getHeader('X-Reference-Id')[0] ?? null,
            'status' => 'pending',
        ];
    }
    
    public function verifyPayment(string $provider, string $transactionId): array
    {
        // Vérifier le statut du paiement auprès du provider
        // Retourner le résultat
    }
}

// app/Services/BankReconciliationService.php
class BankReconciliationService
{
    public function create(array $data): BankReconciliation
    {
        $reconciliation = BankReconciliation::create([
            'reconciliation_id' => Str::uuid(),
            'reconciliation_date' => $data['date'],
            'bank_account' => $data['bank_account'],
            'bank_balance' => $data['bank_balance'],
            'system_balance' => $this->calculateSystemBalance($data['date']),
            'difference' => 0, // Calculé après
            'performed_by' => auth()->id(),
            'status' => 'in_progress',
        ]);
        
        $reconciliation->update([
            'difference' => $reconciliation->bank_balance - $reconciliation->system_balance,
        ]);
        
        return $reconciliation;
    }
    
    public function matchPayments(BankReconciliation $reconciliation): void
    {
        $payments = Paiement::where('payment_method', 'bank_transfer')
            ->where('verification_status', 'pending')
            ->whereDate('date_paiement', '<=', $reconciliation->reconciliation_date)
            ->get();
        
        $matched = [];
        $unmatched = [];
        
        foreach ($payments as $payment) {
            // Logique de matching (montant, date, référence)
            // Simplifiée ici
            if ($this->shouldMatch($payment, $reconciliation)) {
                $matched[] = $payment->paiement_id;
                $payment->update([
                    'verification_status' => 'verified',
                    'verified_by' => auth()->id(),
                    'verified_at' => now(),
                ]);
            } else {
                $unmatched[] = $payment->paiement_id;
            }
        }
        
        $reconciliation->update([
            'matched_payments' => $matched,
            'unmatched_payments' => $unmatched,
            'status' => empty($unmatched) ? 'completed' : 'discrepancy',
        ]);
    }
}
```

---

## 4️⃣ Comptabilité

**(Continuation dans le prochain fichier...)**

Je continue avec les autres modules. Voulez-vous que je poursuive avec le module Comptabilité et les autres, ou préférez-vous qu'on discute d'abord de l'architecture proposée jusqu'ici ?

