# 🚀 PSSP IMPACT+ v2.0 - Architecture Complète (Suite)

## 4️⃣ Comptabilité

### Base de données

```php
// Migration: create_comptes_comptables_table
Schema::create('comptes_comptables', function (Blueprint $table) {
    $table->uuid('compte_id')->primary();
    $table->string('numero_compte')->unique(); // 601, 701, 411, etc.
    $table->string('libelle');
    $table->enum('type', ['actif', 'passif', 'charge', 'produit', 'resultat']);
    $table->enum('categorie', ['immobilisation', 'stock', 'creance', 'dette', 'tresorerie', 'autre']);
    $table->uuid('parent_compte_id')->nullable(); // Pour hiérarchie
    $table->uuid('site_id')->nullable(); // Comptabilité par site
    $table->boolean('is_active')->default(true);
    $table->json('settings')->nullable(); // TVA automatique, etc.
    $table->timestamps();
    
    $table->foreign('parent_compte_id')->references('compte_id')->on('comptes_comptables')->onDelete('set null');
    $table->foreign('site_id')->references('site_id')->on('sites')->onDelete('cascade');
    $table->index(['type', 'is_active']);
});

// Migration: enhance_ecritures_comptables_table
Schema::table('ecritures_comptables', function (Blueprint $table) {
    $table->uuid('compte_debit_id')->nullable()->after('ecriture_id');
    $table->uuid('compte_credit_id')->nullable()->after('compte_debit_id');
    $table->string('journal')->default('OD')->after('compte_credit_id'); // OD, VE, AC, BQ
    $table->string('piece_reference')->nullable()->after('journal'); // N° facture, etc.
    $table->uuid('validated_by')->nullable()->after('piece_reference');
    $table->timestamp('validated_at')->nullable()->after('validated_by');
    $table->boolean('is_locked')->default(false)->after('validated_at');
    $table->uuid('site_id')->nullable()->after('is_locked');
    $table->json('analytique')->nullable()->after('site_id'); // Ventilation analytique
    
    $table->foreign('compte_debit_id')->references('compte_id')->on('comptes_comptables')->onDelete('restrict');
    $table->foreign('compte_credit_id')->references('compte_id')->on('comptes_comptables')->onDelete('restrict');
    $table->foreign('validated_by')->references('user_id')->on('users')->onDelete('set null');
    $table->foreign('site_id')->references('site_id')->on('sites')->onDelete('cascade');
});

// Migration: create_tva_configurations_table
Schema::create('tva_configurations', function (Blueprint $table) {
    $table->id();
    $table->string('service_type'); // collecte_standard, location, autre
    $table->decimal('taux_tva', 5, 2); // 18.00 pour 18%
    $table->string('compte_tva_collectee'); // 4451
    $table->string('compte_tva_deductible'); // 4456
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    
    $table->unique('service_type');
});

// Migration: create_exercices_comptables_table
Schema::create('exercices_comptables', function (Blueprint $table) {
    $table->uuid('exercice_id')->primary();
    $table->string('libelle'); // Exercice 2026
    $table->date('date_debut');
    $table->date('date_fin');
    $table->enum('status', ['ouvert', 'cloture', 'archive'])->default('ouvert');
    $table->uuid('closed_by')->nullable();
    $table->timestamp('closed_at')->nullable();
    $table->timestamps();
    
    $table->foreign('closed_by')->references('user_id')->on('users')->onDelete('set null');
    $table->index(['status', 'date_debut']);
});

// Migration: create_budgets_table
Schema::create('budgets', function (Blueprint $table) {
    $table->uuid('budget_id')->primary();
    $table->uuid('site_id')->nullable();
    $table->string('libelle');
    $table->uuid('exercice_id');
    $table->enum('type', ['charge', 'produit']);
    $table->uuid('compte_id')->nullable();
    $table->decimal('montant_prevu', 15, 2);
    $table->decimal('montant_realise', 15, 2)->default(0);
    $table->decimal('ecart', 15, 2)->default(0);
    $table->integer('pourcentage_realisation')->default(0);
    $table->timestamps();
    
    $table->foreign('site_id')->references('site_id')->on('sites')->onDelete('cascade');
    $table->foreign('exercice_id')->references('exercice_id')->on('exercices_comptables')->onDelete('cascade');
    $table->foreign('compte_id')->references('compte_id')->on('comptes_comptables')->onDelete('set null');
});

// Migration: create_export_configs_table
Schema::create('export_configs', function (Blueprint $table) {
    $table->id();
    $table->string('format'); // sage, quickbooks, csv_standard
    $table->string('name');
    $table->json('field_mapping'); // Correspondance des champs
    $table->string('separator')->default(';');
    $table->string('encoding')->default('UTF-8');
    $table->json('options')->nullable();
    $table->boolean('is_default')->default(false);
    $table->timestamps();
    
    $table->unique('format');
});
```

### Services

```php
// app/Services/ComptabiliteService.php
class ComptabiliteService
{
    public function passEcriture(array $data): EcritureComptable
    {
        // Validation principe de la partie double
        if ($data['montant_debit'] !== $data['montant_credit']) {
            throw new \Exception('Débit et crédit doivent être égaux');
        }
        
        $ecriture = EcritureComptable::create([
            'ecriture_id' => Str::uuid(),
            'date_ecriture' => $data['date'],
            'compte_debit_id' => $data['compte_debit_id'],
            'compte_credit_id' => $data['compte_credit_id'],
            'montant_debit' => $data['montant'],
            'montant_credit' => $data['montant'],
            'journal' => $data['journal'] ?? 'OD',
            'libelle' => $data['libelle'],
            'piece_reference' => $data['piece_reference'] ?? null,
            'site_id' => $data['site_id'] ?? null,
        ]);
        
        event(new EcritureCreated($ecriture));
        
        return $ecriture;
    }
    
    public function passEcritureFromFacture(Facture $facture): Collection
    {
        $ecritures = collect();
        
        // Déterminer le taux de TVA
        $tvaConfig = TVAConfiguration::where('service_type', 'collecte_standard')
            ->where('is_active', true)
            ->first();
        
        $montantHT = $facture->montant_total / (1 + ($tvaConfig->taux_tva / 100));
        $montantTVA = $facture->montant_total - $montantHT;
        
        // Écriture principale (411 Client / 701 Ventes)
        $ecritures->push($this->passEcriture([
            'date' => $facture->date_facture,
            'compte_debit_id' => $this->getCompteByNumero('411')->compte_id, // Clients
            'compte_credit_id' => $this->getCompteByNumero('701')->compte_id, // Ventes
            'montant' => $montantHT,
            'journal' => 'VE',
            'libelle' => "Facture {$facture->numero_facture}",
            'piece_reference' => $facture->numero_facture,
            'site_id' => $facture->site_id,
        ]));
        
        // Écriture TVA (411 Client / 4451 TVA collectée)
        if ($montantTVA > 0) {
            $ecritures->push($this->passEcriture([
                'date' => $facture->date_facture,
                'compte_debit_id' => $this->getCompteByNumero('411')->compte_id,
                'compte_credit_id' => $this->getCompteByNumero('4451')->compte_id,
                'montant' => $montantTVA,
                'journal' => 'VE',
                'libelle' => "TVA sur facture {$facture->numero_facture}",
                'piece_reference' => $facture->numero_facture,
                'site_id' => $facture->site_id,
            ]));
        }
        
        return $ecritures;
    }
    
    public function passEcritureFromPaiement(Paiement $paiement): EcritureComptable
    {
        $compteBank = match($paiement->payment_method) {
            'cash' => '571', // Caisse
            'bank_transfer' => '512', // Banque
            'check' => '513', // Chèques à encaisser
            'momo', 'om', 'wave' => '518', // Autres valeurs
            default => '512',
        };
        
        // 512 Banque / 411 Clients
        return $this->passEcriture([
            'date' => $paiement->date_paiement,
            'compte_debit_id' => $this->getCompteByNumero($compteBank)->compte_id,
            'compte_credit_id' => $this->getCompteByNumero('411')->compte_id,
            'montant' => $paiement->montant,
            'journal' => 'BQ',
            'libelle' => "Règlement facture",
            'piece_reference' => $paiement->transaction_reference,
            'site_id' => $paiement->facture->site_id ?? null,
        ]);
    }
    
    public function getBalance(Carbon $dateDebut, Carbon $dateFin, ?string $siteId = null): Collection
    {
        return CompteComptable::with(['debitEcritures' => function($q) use ($dateDebut, $dateFin, $siteId) {
                $q->whereBetween('date_ecriture', [$dateDebut, $dateFin])
                  ->when($siteId, fn($q) => $q->where('site_id', $siteId));
            }, 'creditEcritures' => function($q) use ($dateDebut, $dateFin, $siteId) {
                $q->whereBetween('date_ecriture', [$dateDebut, $dateFin])
                  ->when($siteId, fn($q) => $q->where('site_id', $siteId));
            }])
            ->get()
            ->map(function($compte) {
                $totalDebit = $compte->debitEcritures->sum('montant_debit');
                $totalCredit = $compte->creditEcritures->sum('montant_credit');
                
                return [
                    'compte' => $compte,
                    'total_debit' => $totalDebit,
                    'total_credit' => $totalCredit,
                    'solde' => $totalDebit - $totalCredit,
                ];
            });
    }
    
    public function getBilan(string $exerciceId): array
    {
        $exercice = ExerciceComptable::findOrFail($exerciceId);
        
        $actifs = $this->getBalance($exercice->date_debut, $exercice->date_fin)
            ->where('compte.type', 'actif')
            ->groupBy('compte.categorie')
            ->map(fn($items) => $items->sum('solde'));
        
        $passifs = $this->getBalance($exercice->date_debut, $exercice->date_fin)
            ->where('compte.type', 'passif')
            ->groupBy('compte.categorie')
            ->map(fn($items) => $items->sum('solde'));
        
        return [
            'actifs' => $actifs,
            'passifs' => $passifs,
            'total_actif' => $actifs->sum(),
            'total_passif' => $passifs->sum(),
            'equilibre' => abs($actifs->sum() - $passifs->sum()) < 0.01,
        ];
    }
    
    public function getCompteResultat(string $exerciceId): array
    {
        $exercice = ExerciceComptable::findOrFail($exerciceId);
        
        $produits = $this->getBalance($exercice->date_debut, $exercice->date_fin)
            ->where('compte.type', 'produit')
            ->sum('solde');
        
        $charges = $this->getBalance($exercice->date_debut, $exercice->date_fin)
            ->where('compte.type', 'charge')
            ->sum('solde');
        
        $resultat = $produits - abs($charges);
        
        return [
            'produits' => $produits,
            'charges' => abs($charges),
            'resultat' => $resultat,
            'type' => $resultat >= 0 ? 'benefice' : 'perte',
        ];
    }
}

// app/Services/ExportComptableService.php
class ExportComptableService
{
    public function export(string $format, Carbon $dateDebut, Carbon $dateFin): string
    {
        $config = ExportConfig::where('format', $format)->firstOrFail();
        
        $ecritures = EcritureComptable::with(['compteDebit', 'compteCredit'])
            ->whereBetween('date_ecriture', [$dateDebut, $dateFin])
            ->orderBy('date_ecriture')
            ->get();
        
        return match($format) {
            'sage' => $this->exportSage($ecritures, $config),
            'quickbooks' => $this->exportQuickbooks($ecritures, $config),
            'csv_standard' => $this->exportCSV($ecritures, $config),
            default => throw new \Exception("Format non supporté: {$format}"),
        };
    }
    
    private function exportSage(Collection $ecritures, ExportConfig $config): string
    {
        $lines = [];
        
        foreach ($ecritures as $ecriture) {
            // Format Sage: JOURNAL|DATE|COMPTE|LIBELLE|DEBIT|CREDIT|PIECE
            $lines[] = implode($config->separator, [
                $ecriture->journal,
                $ecriture->date_ecriture->format('d/m/Y'),
                $ecriture->compteDebit->numero_compte,
                $ecriture->libelle,
                number_format($ecriture->montant_debit, 2, '.', ''),
                '0.00',
                $ecriture->piece_reference ?? '',
            ]);
            
            $lines[] = implode($config->separator, [
                $ecriture->journal,
                $ecriture->date_ecriture->format('d/m/Y'),
                $ecriture->compteCredit->numero_compte,
                $ecriture->libelle,
                '0.00',
                number_format($ecriture->montant_credit, 2, '.', ''),
                $ecriture->piece_reference ?? '',
            ]);
        }
        
        return implode("\n", $lines);
    }
    
    private function exportCSV(Collection $ecritures, ExportConfig $config): string
    {
        $csv = [];
        
        // En-tête
        $csv[] = implode($config->separator, [
            'Date', 'Journal', 'Compte Débit', 'Compte Crédit', 
            'Libellé', 'Montant', 'Pièce'
        ]);
        
        // Données
        foreach ($ecritures as $ecriture) {
            $csv[] = implode($config->separator, [
                $ecriture->date_ecriture->format('d/m/Y'),
                $ecriture->journal,
                $ecriture->compteDebit->numero_compte,
                $ecriture->compteCredit->numero_compte,
                '"' . $ecriture->libelle . '"',
                number_format($ecriture->montant_debit, 2, '.', ''),
                $ecriture->piece_reference ?? '',
            ]);
        }
        
        return implode("\n", $csv);
    }
}

// app/Services/BudgetService.php
class BudgetService
{
    public function updateRealisations()
    {
        $budgets = Budget::with('compte')->get();
        
        foreach ($budgets as $budget) {
            $realise = EcritureComptable::where(function($q) use ($budget) {
                    if ($budget->type === 'charge') {
                        $q->where('compte_debit_id', $budget->compte_id);
                    } else {
                        $q->where('compte_credit_id', $budget->compte_id);
                    }
                })
                ->sum('montant_debit');
            
            $ecart = $budget->montant_prevu - $realise;
            $pourcentage = $budget->montant_prevu > 0 
                ? ($realise / $budget->montant_prevu) * 100 
                : 0;
            
            $budget->update([
                'montant_realise' => $realise,
                'ecart' => $ecart,
                'pourcentage_realisation' => round($pourcentage),
            ]);
        }
    }
    
    public function getAlerts(): Collection
    {
        return Budget::where('pourcentage_realisation', '>', 90)
            ->orWhere('ecart', '<', 0) // Dépassement
            ->with('compte', 'site')
            ->get();
    }
}
```

---

## 5️⃣ Rapports & BI

### Base de données

```php
// Migration: create_dashboards_table
Schema::create('dashboards', function (Blueprint $table) {
    $table->uuid('dashboard_id')->primary();
    $table->string('name');
    $table->string('slug')->unique();
    $table->uuid('user_id')->nullable(); // Si dashboard personnel
    $table->string('role_name')->nullable(); // Si dashboard par rôle
    $table->json('widgets'); // Configuration des widgets
    $table->json('layout')->nullable(); // Position des widgets
    $table->boolean('is_default')->default(false);
    $table->boolean('is_public')->default(false);
    $table->integer('refresh_interval')->default(300); // secondes
    $table->timestamps();
    
    $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
});

// Migration: create_saved_filters_table
Schema::create('saved_filters', function (Blueprint $table) {
    $table->uuid('filter_id')->primary();
    $table->uuid('user_id');
    $table->string('name');
    $table->string('module'); // collectes, factures, rapports
    $table->json('filters'); // Critères de filtrage
    $table->boolean('is_favorite')->default(false);
    $table->integer('usage_count')->default(0);
    $table->timestamp('last_used_at')->nullable();
    $table->timestamps();
    
    $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
    $table->index(['user_id', 'module']);
});

// Migration: create_kpi_definitions_table
Schema::create('kpi_definitions', function (Blueprint $table) {
    $table->id();
    $table->string('code')->unique(); // cout_par_collecte, kg_par_site, etc.
    $table->string('name');
    $table->string('category'); // finance, operations, qualite
    $table->text('description')->nullable();
    $table->string('unit'); // CFA, kg, %, nombre
    $table->text('sql_query')->nullable(); // Requête pour calculer le KPI
    $table->string('calculation_method')->nullable(); // PHP method name
    $table->json('thresholds')->nullable(); // Seuils (alerte, bon, excellent)
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

// Migration: create_kpi_values_table
Schema::create('kpi_values', function (Blueprint $table) {
    $table->uuid('value_id')->primary();
    $table->unsignedBigInteger('kpi_id');
    $table->date('period_date');
    $table->enum('period_type', ['day', 'week', 'month', 'quarter', 'year']);
    $table->uuid('site_id')->nullable();
    $table->decimal('value', 15, 4);
    $table->enum('trend', ['up', 'down', 'stable'])->nullable();
    $table->decimal('variation', 8, 2)->nullable(); // %
    $table->timestamps();
    
    $table->foreign('kpi_id')->references('id')->on('kpi_definitions')->onDelete('cascade');
    $table->foreign('site_id')->references('site_id')->on('sites')->onDelete('cascade');
    $table->index(['kpi_id', 'period_date', 'site_id']);
});

// Migration: create_report_templates_table
Schema::create('report_templates', function (Blueprint $table) {
    $table->uuid('template_id')->primary();
    $table->string('name');
    $table->string('code')->unique();
    $table->text('description')->nullable();
    $table->enum('type', ['pdf', 'excel', 'html'])->default('pdf');
    $table->json('sections'); // Sections du rapport
    $table->json('filters')->nullable(); // Filtres disponibles
    $table->string('blade_view')->nullable(); // Vue Blade pour PDF
    $table->boolean('allow_schedule')->default(true);
    $table->timestamps();
});

// Migration: create_scheduled_reports_table
Schema::create('scheduled_reports', function (Blueprint $table) {
    $table->uuid('schedule_id')->primary();
    $table->uuid('template_id');
    $table->uuid('created_by');
    $table->string('name');
    $table->enum('frequency', ['daily', 'weekly', 'monthly', 'quarterly']);
    $table->string('day_of_week')->nullable(); // Pour hebdomadaire
    $table->integer('day_of_month')->nullable(); // Pour mensuel
    $table->time('time')->default('08:00');
    $table->json('recipients'); // Emails
    $table->json('filters')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamp('last_sent_at')->nullable();
    $table->timestamp('next_send_at')->nullable();
    $table->timestamps();
    
    $table->foreign('template_id')->references('template_id')->on('report_templates')->onDelete('cascade');
    $table->foreign('created_by')->references('user_id')->on('users')->onDelete('cascade');
});

// Migration: create_forecast_models_table
Schema::create('forecast_models', function (Blueprint $table) {
    $table->id();
    $table->string('metric'); // collectes_count, revenue, waste_volume
    $table->enum('model_type', ['linear', 'exponential', 'moving_average']);
    $table->json('parameters'); // Paramètres du modèle
    $table->decimal('accuracy', 5, 2)->nullable(); // %
    $table->timestamp('trained_at')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

// Migration: create_forecasts_table
Schema::create('forecasts', function (Blueprint $table) {
    $table->uuid('forecast_id')->primary();
    $table->unsignedBigInteger('model_id');
    $table->date('forecast_date');
    $table->decimal('predicted_value', 15, 2);
    $table->decimal('confidence_lower', 15, 2)->nullable();
    $table->decimal('confidence_upper', 15, 2)->nullable();
    $table->decimal('actual_value', 15, 2)->nullable();
    $table->timestamps();
    
    $table->foreign('model_id')->references('id')->on('forecast_models')->onDelete('cascade');
    $table->index(['model_id', 'forecast_date']);
});
```

### Services

```php
// app/Services/DashboardService.php
class DashboardService
{
    public function getDashboardData(string $role): array
    {
        return match($role) {
            'Admin' => $this->getAdminDashboard(),
            'Comptable' => $this->getComptableDashboard(),
            'Agent' => $this->getAgentDashboard(),
            'Responsable Site' => $this->getResponsableSiteDashboard(),
            default => $this->getDefaultDashboard(),
        };
    }
    
    private function getAdminDashboard(): array
    {
        return [
            'stats' => [
                'total_sites' => Site::count(),
                'total_collectes_month' => Collecte::whereMonth('date_collecte', now()->month)->count(),
                'revenue_month' => Facture::whereMonth('date_facture', now()->month)
                    ->where('status', 'payée')
                    ->sum('montant_total'),
                'pending_validations' => Validation::where('status', 'pending')->count(),
            ],
            'charts' => [
                'collectes_trend' => $this->getCollectesTrend(),
                'revenue_by_site' => $this->getRevenueBySite(),
                'waste_distribution' => $this->getWasteDistribution(),
                'sla_compliance' => $this->getSLACompliance(),
            ],
            'recent_activities' => $this->getRecentActivities(),
            'alerts' => $this->getSystemAlerts(),
        ];
    }
    
    private function getCollectesTrend(): array
    {
        $data = Collecte::selectRaw('DATE(date_collecte) as date, COUNT(*) as count')
            ->where('date_collecte', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        return [
            'labels' => $data->pluck('date')->map(fn($d) => Carbon::parse($d)->format('d/m'))->toArray(),
            'values' => $data->pluck('count')->toArray(),
        ];
    }
    
    private function getRevenueBySite(): array
    {
        $data = Facture::join('sites', 'factures.site_id', '=', 'sites.site_id')
            ->selectRaw('sites.nom as site, SUM(factures.montant_total) as total')
            ->where('factures.status', 'payée')
            ->whereMonth('factures.date_facture', now()->month)
            ->groupBy('sites.site_id', 'sites.nom')
            ->orderByDesc('total')
            ->limit(10)
            ->get();
        
        return [
            'labels' => $data->pluck('site')->toArray(),
            'values' => $data->pluck('total')->toArray(),
        ];
    }
}

// app/Services/KPIService.php
class KPIService
{
    public function calculate(string $kpiCode, Carbon $date, ?string $siteId = null): float
    {
        $definition = KPIDefinition::where('code', $kpiCode)->firstOrFail();
        
        if ($definition->calculation_method) {
            return $this->{$definition->calculation_method}($date, $siteId);
        }
        
        // Exécuter la requête SQL personnalisée
        if ($definition->sql_query) {
            $query = str_replace(
                [':date', ':site_id'],
                ["'{$date->format('Y-m-d')}'", $siteId ? "'{$siteId}'" : 'NULL'],
                $definition->sql_query
            );
            
            $result = DB::selectOne($query);
            return $result->value ?? 0;
        }
        
        return 0;
    }
    
    private function coutParCollecte(Carbon $date, ?string $siteId): float
    {
        $query = Collecte::whereMonth('date_collecte', $date->month)
            ->whereYear('date_collecte', $date->year)
            ->when($siteId, fn($q) => $q->where('site_id', $siteId));
        
        $totalCollectes = $query->count();
        
        if ($totalCollectes === 0) return 0;
        
        $totalCout = Facture::whereHas('collectes', function($q) use ($date, $siteId) {
                $q->whereMonth('date_collecte', $date->month)
                  ->whereYear('date_collecte', $date->year)
                  ->when($siteId, fn($q) => $q->where('site_id', $siteId));
            })
            ->sum('montant_total');
        
        return round($totalCout / $totalCollectes, 2);
    }
    
    private function kgParSite(Carbon $date, ?string $siteId): float
    {
        return Collecte::whereMonth('date_collecte', $date->month)
            ->whereYear('date_collecte', $date->year)
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->avg('quantite') ?? 0;
    }
    
    private function tauxIncidents(Carbon $date, ?string $siteId): float
    {
        $totalCollectes = Collecte::whereMonth('date_collecte', $date->month)
            ->whereYear('date_collecte', $date->year)
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->count();
        
        if ($totalCollectes === 0) return 0;
        
        $totalIncidents = Incident::whereMonth('reported_at', $date->month)
            ->whereYear('reported_at', $date->year)
            ->when($siteId, fn($q) => $q->whereHas('collecte', fn($q) => $q->where('site_id', $siteId)))
            ->count();
        
        return round(($totalIncidents / $totalCollectes) * 100, 2);
    }
    
    public function storeValue(string $kpiCode, Carbon $date, float $value, ?string $siteId = null): KPIValue
    {
        $definition = KPIDefinition::where('code', $kpiCode)->firstOrFail();
        
        // Calculer la tendance
        $previousValue = KPIValue::where('kpi_id', $definition->id)
            ->where('period_date', '<', $date)
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->orderBy('period_date', 'desc')
            ->first();
        
        $trend = 'stable';
        $variation = 0;
        
        if ($previousValue) {
            $variation = $previousValue->value != 0 
                ? (($value - $previousValue->value) / $previousValue->value) * 100 
                : 0;
            
            if (abs($variation) < 5) {
                $trend = 'stable';
            } elseif ($variation > 0) {
                $trend = 'up';
            } else {
                $trend = 'down';
            }
        }
        
        return KPIValue::create([
            'value_id' => Str::uuid(),
            'kpi_id' => $definition->id,
            'period_date' => $date,
            'period_type' => 'month',
            'site_id' => $siteId,
            'value' => $value,
            'trend' => $trend,
            'variation' => round($variation, 2),
        ]);
    }
    
    public function calculateAllKPIs(Carbon $date): void
    {
        $kpis = KPIDefinition::where('is_active', true)->get();
        $sites = Site::all();
        
        foreach ($kpis as $kpi) {
            // KPI global
            $value = $this->calculate($kpi->code, $date);
            $this->storeValue($kpi->code, $date, $value);
            
            // KPI par site
            foreach ($sites as $site) {
                $siteValue = $this->calculate($kpi->code, $date, $site->site_id);
                $this->storeValue($kpi->code, $date, $siteValue, $site->site_id);
            }
        }
    }
}

// app/Services/ReportService.php
class ReportService
{
    public function generate(string $templateCode, array $filters = []): string
    {
        $template = ReportTemplate::where('code', $templateCode)->firstOrFail();
        
        $data = $this->prepareData($template, $filters);
        
        return match($template->type) {
            'pdf' => $this->generatePDF($template, $data),
            'excel' => $this->generateExcel($template, $data),
            'html' => $this->generateHTML($template, $data),
        };
    }
    
    private function generatePDF(ReportTemplate $template, array $data): string
    {
        $pdf = PDF::loadView($template->blade_view, $data);
        
        $filename = storage_path('app/reports/' . Str::slug($template->name) . '-' . now()->format('Y-m-d-His') . '.pdf');
        $pdf->save($filename);
        
        return $filename;
    }
    
    private function generateExcel(ReportTemplate $template, array $data): string
    {
        $export = new GenericReportExport($data);
        $filename = 'reports/' . Str::slug($template->name) . '-' . now()->format('Y-m-d-His') . '.xlsx';
        
        Excel::store($export, $filename);
        
        return storage_path('app/' . $filename);
    }
    
    public function schedule(ScheduledReport $schedule): void
    {
        $template = $schedule->template;
        $filepath = $this->generate($template->code, $schedule->filters ?? []);
        
        // Envoyer par email
        foreach ($schedule->recipients as $email) {
            Mail::to($email)->send(new ScheduledReportMail($schedule, $filepath));
        }
        
        $schedule->update([
            'last_sent_at' => now(),
            'next_send_at' => $this->calculateNextSend($schedule),
        ]);
    }
    
    private function calculateNextSend(ScheduledReport $schedule): Carbon
    {
        $now = now();
        [$hour, $minute] = explode(':', $schedule->time);
        
        return match($schedule->frequency) {
            'daily' => $now->addDay()->setTime($hour, $minute),
            'weekly' => $now->next($schedule->day_of_week)->setTime($hour, $minute),
            'monthly' => $now->addMonth()->day($schedule->day_of_month)->setTime($hour, $minute),
            'quarterly' => $now->addMonths(3)->setTime($hour, $minute),
        };
    }
}

// app/Services/ForecastService.php
class ForecastService
{
    public function trainModel(string $metric, string $modelType = 'linear'): ForecastModel
    {
        // Récupérer les données historiques
        $historicalData = $this->getHistoricalData($metric);
        
        // Entraîner le modèle
        $parameters = match($modelType) {
            'linear' => $this->trainLinearRegression($historicalData),
            'exponential' => $this->trainExponentialSmoothing($historicalData),
            'moving_average' => $this->trainMovingAverage($historicalData),
        };
        
        // Calculer l'accuracy
        $predictions = $this->predict($parameters, $historicalData->pluck('date'));
        $accuracy = $this->calculateAccuracy($historicalData->pluck('value'), $predictions);
        
        return ForecastModel::create([
            'metric' => $metric,
            'model_type' => $modelType,
            'parameters' => $parameters,
            'accuracy' => $accuracy,
            'trained_at' => now(),
        ]);
    }
    
    private function trainLinearRegression(Collection $data): array
    {
        $n = $data->count();
        $sumX = 0;
        $sumY = 0;
        $sumXY = 0;
        $sumX2 = 0;
        
        foreach ($data as $index => $point) {
            $x = $index;
            $y = $point['value'];
            
            $sumX += $x;
            $sumY += $y;
            $sumXY += $x * $y;
            $sumX2 += $x * $x;
        }
        
        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        $intercept = ($sumY - $slope * $sumX) / $n;
        
        return [
            'slope' => $slope,
            'intercept' => $intercept,
        ];
    }
    
    public function forecast(ForecastModel $model, Carbon $date): Forecast
    {
        $historicalData = $this->getHistoricalData($model->metric);
        $index = $historicalData->count() + $date->diffInMonths($historicalData->last()['date']);
        
        $predictedValue = match($model->model_type) {
            'linear' => $model->parameters['intercept'] + $model->parameters['slope'] * $index,
            'exponential' => $this->forecastExponential($model, $index),
            'moving_average' => $this->forecastMovingAverage($model, $historicalData),
        };
        
        // Intervalle de confiance (simplifié à ±10%)
        $confidenceLower = $predictedValue * 0.9;
        $confidenceUpper = $predictedValue * 1.1;
        
        return Forecast::create([
            'forecast_id' => Str::uuid(),
            'model_id' => $model->id,
            'forecast_date' => $date,
            'predicted_value' => round($predictedValue, 2),
            'confidence_lower' => round($confidenceLower, 2),
            'confidence_upper' => round($confidenceUpper, 2),
        ]);
    }
    
    private function getHistoricalData(string $metric): Collection
    {
        return match($metric) {
            'collectes_count' => Collecte::selectRaw('DATE_FORMAT(date_collecte, "%Y-%m-01") as date, COUNT(*) as value')
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
            
            'revenue' => Facture::selectRaw('DATE_FORMAT(date_facture, "%Y-%m-01") as date, SUM(montant_total) as value')
                ->where('status', 'payée')
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
            
            'waste_volume' => Collecte::selectRaw('DATE_FORMAT(date_collecte, "%Y-%m-01") as date, SUM(quantite) as value')
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
        };
    }
}
```

---

## 6️⃣ Gestion des Sites

### Base de données

```php
// Migration: enhance_sites_table
Schema::table('sites', function (Blueprint $table) {
    $table->uuid('contract_id')->nullable()->after('site_id');
    $table->enum('status', ['actif', 'inactif', 'suspendu', 'resilie'])->default('actif')->after('longitude');
    $table->date('contract_start_date')->nullable()->after('status');
    $table->date('contract_end_date')->nullable()->after('contract_start_date');
    $table->decimal('contract_amount', 15, 2)->nullable()->after('contract_end_date');
    $table->enum('billing_frequency', ['daily', 'weekly', 'monthly', 'quarterly'])->default('monthly')->after('contract_amount');
    $table->integer('sla_response_time')->nullable()->after('billing_frequency'); // minutes
    $table->json('service_levels')->nullable()->after('sla_response_time');
    $table->timestamp('last_activity_at')->nullable()->after('service_levels');
    $table->json('alerts_config')->nullable()->after('last_activity_at');
    
    $table->index(['status', 'contract_end_date']);
});

// Migration: create_site_contracts_table
Schema::create('site_contracts', function (Blueprint $table) {
    $table->uuid('contract_id')->primary();
    $table->uuid('site_id');
    $table->string('contract_number')->unique();
    $table->date('start_date');
    $table->date('end_date');
    $table->decimal('monthly_amount', 15, 2);
    $table->enum('billing_type', ['fixed', 'variable', 'mixed']);
    $table->json('pricing_details'); // Tarification par type de déchet
    $table->json('sla_terms'); // Termes du SLA
    $table->text('special_conditions')->nullable();
    $table->enum('status', ['draft', 'active', 'expired', 'terminated'])->default('draft');
    $table->uuid('signed_by')->nullable();
    $table->timestamp('signed_at')->nullable();
    $table->string('document_path')->nullable();
    $table->timestamps();
    $table->softDeletes();
    
    $table->foreign('site_id')->references('site_id')->on('sites')->onDelete('cascade');
    $table->foreign('signed_by')->references('user_id')->on('users')->onDelete('set null');
});

// Migration: create_site_history_table
Schema::create('site_history', function (Blueprint $table) {
    $table->uuid('history_id')->primary();
    $table->uuid('site_id');
    $table->string('field_changed');
    $table->text('old_value')->nullable();
    $table->text('new_value')->nullable();
    $table->uuid('changed_by');
    $table->string('change_reason')->nullable();
    $table->timestamps();
    
    $table->foreign('site_id')->references('site_id')->on('sites')->onDelete('cascade');
    $table->foreign('changed_by')->references('user_id')->on('users')->onDelete('cascade');
    $table->index(['site_id', 'created_at']);
});

// Migration: create_site_alerts_table
Schema::create('site_alerts', function (Blueprint $table) {
    $table->uuid('alert_id')->primary();
    $table->uuid('site_id');
    $table->enum('type', ['inactivity', 'contract_expiring', 'payment_overdue', 'sla_breach', 'custom']);
    $table->enum('severity', ['info', 'warning', 'critical']);
    $table->string('title');
    $table->text('message');
    $table->boolean('is_resolved')->default(false);
    $table->uuid('resolved_by')->nullable();
    $table->timestamp('resolved_at')->nullable();
    $table->json('metadata')->nullable();
    $table->timestamps();
    
    $table->foreign('site_id')->references('site_id')->on('sites')->onDelete('cascade');
    $table->foreign('resolved_by')->references('user_id')->on('users')->onDelete('set null');
    $table->index(['site_id', 'is_resolved']);
});
```

**(Continue dans le prochain message...)**
