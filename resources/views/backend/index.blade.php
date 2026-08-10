@extends('layouts.back')

@section('content')
<main id="main" class="main">
    @if(auth()->check() && auth()->user()->hasRole('Responsable site'))
    <div class="pagetitle">
        <h1>Bonjour, {{ auth()->user()->firstname }} 👋</h1>
        <p class="dashboard-subtitle">Suivi de vos sites · {{ now()->translatedFormat('l d F Y') }}</p>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
                <li class="breadcrumb-item active">Vue Responsable</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        <div class="row g-3">
            <div class="col-xxl-2 col-md-4 col-6">
                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg,#35604d,#56988e); color:#fff;">
                    <div class="card-body py-3">
                        <small>Sites couverts</small>
                        <h4 class="mb-0">{{ count($siteIds ?? []) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xxl-2 col-md-4 col-6">
                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg,#3f7a60,#549678); color:#fff;">
                    <div class="card-body py-3">
                        <small>Collectes du mois</small>
                        <h4 class="mb-0">{{ $collectesMois ?? 0 }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xxl-2 col-md-4 col-6">
                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg,#a06a24,#c98f3d); color:#fff;">
                    <div class="card-body py-3">
                        <small>Poids total (kg)</small>
                        <h4 class="mb-0">{{ \App\Models\Collecte::formatPoids($poidsMois ?? 0) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xxl-2 col-md-4 col-6">
                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg,#4c8b82,#56988e); color:#fff;">
                    <div class="card-body py-3">
                        <small>Factures du mois</small>
                        <h4 class="mb-0">{{ $facturesMois ?? 0 }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xxl-2 col-md-4 col-6">
                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg,#9d4b45,#c76b64); color:#fff;">
                    <div class="card-body py-3">
                        <small>Collectes à signer</small>
                        <h4 class="mb-0">{{ $collectesASigner ?? 0 }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xxl-2 col-md-4 col-6">
                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg,#3c4a41,#66786c); color:#fff;">
                    <div class="card-body py-3">
                        <small>Paiements en attente</small>
                        <h4 class="mb-0">{{ $paiementsEnAttenteValidation ?? 0 }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Tendance Collectes & Poids (6 mois)</h5>
                        <div id="responsableCollectesChart" style="min-height: 340px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Répartition des factures</h5>
                        <div id="responsableFacturesChart" style="min-height: 340px;"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Dernières factures de vos sites</h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle text-nowrap">
                                <thead>
                                    <tr>
                                        <th>Facture</th>
                                        <th>Site</th>
                                        <th>Date</th>
                                        <th>Montant</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse(($dernieresFacturesResponsable ?? []) as $facture)
                                    @php
                                        $sf = \Illuminate\Support\Str::of($facture->statut ?? '')
                                            ->replace('?', 'e')
                                            ->ascii()
                                            ->lower()
                                            ->toString();
                                    @endphp
                                    <tr>
                                        <td>{{ $facture->numero_facture }}</td>
                                        <td>{{ $facture->site?->site_name ?? '-' }}</td>
                                        <td>{{ optional($facture->date_facture)->format('d/m/Y') }}</td>
                                        <td>{{ number_format($facture->montant_facture, 0, ',', ' ') }} FCFA</td>
                                        <td>
                                            <span class="badge {{ in_array($sf, ['payee', 'paye']) ? 'bg-success' : 'bg-warning text-dark' }}">
                                                {{ in_array($sf, ['payee', 'paye']) ? 'Payee' : 'En attente' }}
                                            </span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="5" class="text-muted text-center">Aucune facture trouvée.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Top types de déchets (mois)</h5>
                        <ul class="list-group list-group-flush">
                            @forelse(($topTypesResponsable ?? []) as $type)
                            <li class="list-group-item d-flex justify-content-between">
                                <span>{{ $type->libelle }}</span>
                                <span class="badge bg-primary rounded-pill">{{ $type->total }}</span>
                            </li>
                            @empty
                            <li class="list-group-item text-muted">Aucune donnée de collecte.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif

    @if(auth()->check() && auth()->user()->hasRole('Comptable Site'))
    <div class="pagetitle">
        <h1>Bonjour, {{ auth()->user()->firstname }} 👋</h1>
        <p class="dashboard-subtitle">Suivi financier de votre établissement · {{ now()->translatedFormat('l d F Y') }}</p>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
                <li class="breadcrumb-item active">Vue Comptable Établissement</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        <div class="row g-3">

            <div class="col-xxl-2 col-md-4 col-6">
                <div class="card border-0 shadow-sm h-100" style="background:linear-gradient(135deg,#35604d,#56988e);color:#fff;">
                    <div class="card-body py-3">
                        <small>Factures du mois</small>
                        <h4 class="mb-0">{{ $cs_facturesMois ?? 0 }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-xxl-2 col-md-4 col-6">
                <div class="card border-0 shadow-sm h-100" style="background:linear-gradient(135deg,#3f7a60,#549678);color:#fff;">
                    <div class="card-body py-3">
                        <small>Montant facturé</small>
                        <h4 class="mb-0" style="font-size:1rem;">{{ number_format($cs_montantMois ?? 0, 0, ',', ' ') }} FCFA</h4>
                    </div>
                </div>
            </div>

            <div class="col-xxl-2 col-md-4 col-6">
                <div class="card border-0 shadow-sm h-100" style="background:linear-gradient(135deg,#3f7a60,#549678);color:#fff;">
                    <div class="card-body py-3">
                        <small>Factures payées</small>
                        <h4 class="mb-0">{{ $cs_facturesPayees ?? 0 }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-xxl-2 col-md-4 col-6">
                <div class="card border-0 shadow-sm h-100" style="background:linear-gradient(135deg,#a06a24,#c98f3d);color:#fff;">
                    <div class="card-body py-3">
                        <small>Factures en attente</small>
                        <h4 class="mb-0">{{ $cs_facturesEnAttente ?? 0 }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-xxl-2 col-md-4 col-6">
                <div class="card border-0 shadow-sm h-100" style="background:linear-gradient(135deg,#3c4a41,#66786c);color:#fff;">
                    <div class="card-body py-3">
                        <small>Paiements soumis</small>
                        <h4 class="mb-0">{{ $cs_paiementsEnAttente ?? 0 }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-xxl-2 col-md-4 col-6">
                <div class="card border-0 shadow-sm h-100" style="background:linear-gradient(135deg,#4c8b82,#56988e);color:#fff;">
                    <div class="card-body py-3">
                        <small>Collectes du mois</small>
                        <h4 class="mb-0">{{ $cs_collectesMois ?? 0 }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Revenus facturés (6 derniers mois)</h5>
                        <div id="csRevenusChart" style="min-height:300px;"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Répartition des factures</h5>
                        <div id="csStatutsChart" style="min-height:300px;"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Dernières factures de mon établissement</h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle text-nowrap">
                                <thead class="table-light">
                                    <tr>
                                        <th>Numéro</th>
                                        <th>Date</th>
                                        <th>Montant</th>
                                        <th>Statut</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse(($cs_dernieresFactures ?? []) as $facture)
                                    @php
                                        $sf = $facture->statut_normalise;
                                        $isPaid = in_array($sf, ['payee', 'paye']);
                                    @endphp
                                    <tr>
                                        <td><small>{{ $facture->numero_facture }}</small></td>
                                        <td>{{ optional($facture->date_facture)->format('d/m/Y') }}</td>
                                        <td>{{ number_format($facture->montant_facture, 0, ',', ' ') }} FCFA</td>
                                        <td>
                                            <span class="badge {{ $isPaid ? 'bg-success' : 'bg-warning text-dark' }}">
                                                {{ $isPaid ? 'Payée' : 'En attente' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if(!$isPaid)
                                            @can('paiements.record')
                                            <a href="{{ route('paiements.create', ['facture_id' => $facture->facture_id]) }}"
                                               class="btn btn-sm btn-outline-primary py-0">
                                                Soumettre paiement
                                            </a>
                                            @endcan
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="5" class="text-muted text-center">Aucune facture.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <a href="{{ route('factures.index') }}" class="btn btn-sm btn-link p-0 mt-2">Voir toutes les factures →</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Derniers paiements soumis</h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Montant</th>
                                        <th>Mode</th>
                                        <th>Date</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse(($cs_derniersPaiements ?? []) as $paiement)
                                    @php
                                        $sp = \Illuminate\Support\Str::of($paiement->statut ?? '')->replace('?','e')->ascii()->lower()->toString();
                                        $badgeClass = match(true) {
                                            in_array($sp, ['valide','validé','validate']) => 'bg-success',
                                            in_array($sp, ['annule','annulé']) => 'bg-danger',
                                            default => 'bg-warning text-dark',
                                        };
                                    @endphp
                                    <tr>
                                        <td>{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</td>
                                        <td><small>{{ $paiement->mode_paiement ?? '-' }}</small></td>
                                        <td><small>{{ optional($paiement->created_at)->format('d/m/Y') }}</small></td>
                                        <td><span class="badge {{ $badgeClass }}">{{ ucfirst($paiement->statut ?? 'En attente') }}</span></td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="text-muted text-center">Aucun paiement soumis.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <a href="{{ route('paiements.index') }}" class="btn btn-sm btn-link p-0 mt-2">Voir tous les paiements →</a>
                    </div>
                </div>
            </div>

        </div>
    </section>
    @endif

    @if(auth()->check() && auth()->user()->hasRole(['Super Admin','Coordonnateur','Comptable','Agent marketing','Administrateur','Agent collecte']))
    <div class="pagetitle">
        <h1>Bonjour, {{ auth()->user()->firstname }} 👋</h1>
        <p class="dashboard-subtitle">Vue d'ensemble de la plateforme · {{ now()->translatedFormat('l d F Y') }}</p>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
                <li class="breadcrumb-item active">Tableau de Bord</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        <div class="row">

            <!-- ===== CARTES DE STATISTIQUES (grille 6 colonnes) ===== -->
            <div class="col-12">
                <div class="kpi-grid">

            <!-- Collectes - Permission: collectes.view -->
            @can('collectes.view')
            <div class="kpi-cell">
                <div class="card info-card collectes-card">
                    <div class="filter">
                        <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                            <li><a class="dropdown-item" href="#"
                                    onclick="filterData('collectes', 'today')">Aujourd'hui</a></li>
                            <li><a class="dropdown-item" href="#" onclick="filterData('collectes', 'month')">Ce mois</a>
                            </li>
                            <li><a class="dropdown-item" href="#" onclick="filterData('collectes', 'year')">Cette
                                    année</a></li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Collectes <span id="collectes-period">| Ce mois</span></h5>
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-truck"></i>
                            </div>
                            <div class="ps-3">
                                <h6 id="collectesTotal">{{ $collectesTotal }}</h6>
                                <span
                                    class="small pt-1 fw-bold {{ $croissanceCollectes >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $croissanceCollectes >= 0 ? '+' : '' }}{{ $croissanceCollectes }}%
                                </span>
                                <span class="text-muted small pt-2 ps-1">vs mois précédent</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endcan

            <!-- Factures - Permission: factures.view -->
            @can('factures.view')
            <div class="kpi-cell">
                <div class="card info-card factures-card">
                    <div class="filter">
                        <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                            <li><a class="dropdown-item" href="#"
                                    onclick="filterData('factures', 'today')">Aujourd'hui</a></li>
                            <li><a class="dropdown-item" href="#" onclick="filterData('factures', 'month')">Ce mois</a>
                            </li>
                            <li><a class="dropdown-item" href="#" onclick="filterData('factures', 'year')">Cette
                                    année</a></li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Factures <span id="factures-period">| Ce mois</span></h5>
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-receipt"></i>
                            </div>
                            <div class="ps-3">
                                <h6 id="facturesTotal">{{ $facturesTotal }}</h6>
                                <span
                                    class="small pt-1 fw-bold {{ $croissanceFactures >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $croissanceFactures >= 0 ? '+' : '' }}{{ $croissanceFactures }}%
                                </span>
                                <span class="text-muted small pt-2 ps-1">vs mois précédent</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endcan

            <!-- Revenus - Permission: rapports.financier -->
            @can('rapports.financier')
            <div class="kpi-cell">
                <div class="card info-card revenue-card">
                    <div class="card-body">
                        <h5 class="card-title">Revenus <span>| Ce mois</span></h5>
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-currency-dollar"></i>
                            </div>
                            <div class="ps-3">
                                <h6 id="montantTotal">{{ number_format($montantTotal, 0, ',', ' ') }} FCFA</h6>
                                <span
                                    class="small pt-1 fw-bold {{ $croissanceRevenus >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $croissanceRevenus >= 0 ? '+' : '' }}{{ $croissanceRevenus }}%
                                </span>
                                <span class="text-muted small pt-2 ps-1">vs mois précédent</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endcan

            <!-- Sites Actifs - Permission: sites.view -->
            @can('sites.view')
            <div class="kpi-cell">
                <div class="card info-card customers-card">
                    <div class="card-body">
                        <h5 class="card-title">Sites Actifs <span>| Total</span></h5>
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-hospital"></i>
                            </div>
                            <div class="ps-3">
                                <h6 id="sitesActifs">{{ $sitesActifs }}</h6>
                                <span class="text-success small pt-1 fw-bold">{{ $nouveauxSites }}</span>
                                <span class="text-muted small pt-2 ps-1">nouveaux ce mois</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endcan

            <!-- ===== INDICATEURS SUPPLÉMENTAIRES ===== -->

            <!-- Taux de validation - Permission: validations.view -->
            @can('validations.view')
            <div class="kpi-cell">
                <div class="card info-card validation-card">
                    <div class="card-body">
                        <h5 class="card-title">Taux de Validation</h5>
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <div class="ps-3">
                                <h6>{{ $tauxValidation }}%</h6>
                                <span class="text-muted small">des collectes validées</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endcan

            <!-- Factures impayées - Permission: factures.view -->
            @can('factures.view')
            <div class="kpi-cell">
                <div class="card info-card impayees-card">
                    <div class="card-body">
                        <h5 class="card-title">Factures Impayées</h5>
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-exclamation-triangle"></i>
                            </div>
                            <div class="ps-3">
                                <h6>{{ $facturesImpayees }}</h6>
                                <span class="text-muted small">à suivre</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endcan

                </div><!-- /.kpi-grid -->
            </div><!-- /.col-12 -->

            <!-- Actions Rapides : barre compacte -->
            @canany(['collectes.create', 'factures.create', 'sites.view', 'rapports.view'])
            <div class="col-12">
                <div class="card quick-actions">
                    <div class="card-body d-flex flex-wrap align-items-center gap-2 py-2">
                        <span class="text-muted small me-1">Actions rapides :</span>
                        @can('collectes.create')
                        <a href="{{ route('collectes.create') }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-circle me-1"></i>Nouvelle collecte
                        </a>
                        @endcan
                        @can('factures.create')
                        <a href="{{ route('factures.create') }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-receipt me-1"></i>Créer une facture
                        </a>
                        @endcan
                        @can('sites.view')
                        <a href="{{ route('sites.index') }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-hospital me-1"></i>Sites
                        </a>
                        @endcan
                        @can('rapports.view')
                        <a href="{{ route('rapports.collectes') }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-bar-chart me-1"></i>Rapports
                        </a>
                        @endcan
                    </div>
                </div>
            </div>
            @endcanany

            <!-- ===== GRAPHIQUES ===== -->

            <!-- Évolution des Collectes - Permission: rapports.collectes -->
            @can('rapports.collectes')
            <div class="col-lg-8">
                <div class="card">
                    <div class="filter">
                        <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                            <li><a class="dropdown-item" href="#" onclick="updateChart('7days')">7 jours</a></li>
                            <li><a class="dropdown-item" href="#" onclick="updateChart('1month')">1 mois</a></li>
                            <li><a class="dropdown-item" href="#" onclick="updateChart('3months')">3 mois</a></li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Évolution des Collectes <span id="chart-period">/7 jours</span></h5>
                        <!-- IMPORTANT: div vide sans attribut style -->
                        <div id="collectesChart"></div>
                    </div>
                </div>
            </div>
            @endcan

            <!-- Activités Récentes - Visible pour tous les rôles connectés -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Activités Récentes <span>| Temps réel</span></h5>
                        <div class="activity">
                            @foreach($activitesRecentes as $activite)
                            <div class="activity-item d-flex">
                                <div class="activite-label">{{ $activite['date']->diffForHumans() }}</div>
                                <i
                                    class="bi {{ $activite['icone'] }} activity-badge text-{{ $activite['couleur'] }} align-self-start"></i>
                                <div class="activity-content">
                                    <strong>{{ $activite['titre'] }}</strong><br>
                                    {{ $activite['description'] }}
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Évolution Mensuelle - Permission: rapports.financier -->
            @can('rapports.financier')
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Évolution Mensuelle</h5>
                        <!-- IMPORTANT: div vide sans attribut style -->
                        <div id="evolutionChart"></div>
                    </div>
                </div>
            </div>
            @endcan

            <!-- Répartition par Type de Déchet - Permission: rapports.collectes -->
            @can('rapports.collectes')
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Répartition par Type de Déchet</h5>
                        <!-- IMPORTANT: div vide sans attribut style -->
                        <div id="typesChart"></div>

                        <!-- Légendes détaillées -->
                        <div class="mt-3">
                            @foreach($typesDechets as $type)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>{{ $type->libelle }}</span>
                                <div class="text-end">
                                    <strong>{{ $type->nombre }} collectes</strong><br>
                                    <small class="text-muted">{{ \App\Models\Collecte::formatPoids($type->poids_total) }}
                                        kg</small>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endcan

            <!-- Collectes Récentes - Permission: collectes.view -->
            @can('collectes.view')
            <div class="col-lg-8">
                <div class="card recent-sales overflow-auto">
                    <div class="filter">
                        <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                            <li><a class="dropdown-item" href="#" onclick="filterCollectes('today')">Aujourd'hui</a>
                            </li>
                            <li><a class="dropdown-item" href="#" onclick="filterCollectes('week')">Cette semaine</a>
                            </li>
                            <li><a class="dropdown-item" href="#" onclick="filterCollectes('month')">Ce mois</a></li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Collectes Récentes <span id="collectes-filter">| Toutes</span></h5>
                        <div class="table-responsive">
                            <table class="table table-borderless table-sm" id="collectesTable">
                                <thead>
                                    <tr>
                                        <th scope="col">Numéro</th>
                                        <th scope="col">Site</th>
                                        <th scope="col">Type Déchet</th>
                                        <th scope="col">Poids</th>
                                        <th scope="col">Agent</th>
                                        <th scope="col">Date</th>
                                        <th scope="col">Statut</th>
                                        @canany(['collectes.update'])
                                        <th scope="col">Actions</th>
                                        @endcanany
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($collectesRecentes as $collecte)
                                    <tr>
                                        <th scope="row">
                                            <a href="{{ route('collectes.show', $collecte['numero_collecte']) }}"
                                                class="text-primary">
                                                {{ $collecte['numero_collecte'] }}
                                            </a>
                                        </th>
                                        <td>{{ $collecte['site_name'] }}</td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $collecte['type_dechet'] }}</span>
                                        </td>
                                        <td><strong>{{ \App\Models\Collecte::formatPoids($collecte['poids']) }}
                                                kg</strong></td>
                                        <td>{{ $collecte['agent'] }}</td>
                                        <td>{{ $collecte['date_collecte'] }}</td>
                                        <td>
                                            @php
                                            $badgeClass = match($collecte['statut']) {
                                            'validee' => 'bg-success',
                                            'en_attente' => 'bg-warning',
                                            'terminee' => 'bg-primary',
                                            default => 'bg-danger'
                                            };
                                            $statut = match($collecte['statut']) {
                                            'validee' => 'Validée',
                                            'en_attente' => 'En attente',
                                            'terminee' => 'Terminée',
                                            default => 'Rejetée'
                                            };
                                            @endphp
                                            <span class="badge {{ $badgeClass }}">{{ $statut }}</span>
                                        </td>
                                        @canany(['collectes.update'])
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('collectes.show', $collecte['numero_collecte']) }}"
                                                    class="btn btn-outline-primary" title="Voir détails">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                @can('collectes.update')
                                                <a href="{{ route('collectes.edit', $collecte['numero_collecte']) }}"
                                                    class="btn btn-outline-warning" title="Modifier">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                @endcan
                                            </div>
                                        </td>
                                        @endcanany
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endcan

            <!-- Top Sites - Permission: rapports.sites -->
            @can('rapports.sites')
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Sites Les Plus Actifs <span>| Ce mois</span></h5>
                        <div class="table-responsive">
                            <table class="table table-borderless table-sm">
                                <thead>
                                    <tr>
                                        <th>Site</th>
                                        <th>Collectes</th>
                                        <th>Poids</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topSites as $index => $site)
                                    <tr>
                                        <td>
                                            <span class="badge bg-primary rounded-pill me-2">{{ $index + 1 }}</span>
                                            {{ $site->site_name }}
                                        </td>
                                        <td><strong>{{ $site->nombre_collectes }}</strong></td>
                                        <td>{{ \App\Models\Collecte::formatPoids($site->poids_total) }} kg</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endcan

        </div>
        @endif
    </section>
</main>
@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {

        // ===== DASHBOARD RESPONSABLE SITE =====
        try {
            const responsableCollectesTarget = document.getElementById('responsableCollectesChart');
            const responsableFacturesTarget = document.getElementById('responsableFacturesChart');

            if (responsableCollectesTarget && responsableFacturesTarget) {
                const evolutionResponsable = @json($evolutionCollectesResponsable ?? []);
                const repartitionResponsable = @json($repartitionFacturesResponsable ?? ['payees' => 0, 'en_attente' => 0]);

                const collectesCanvas = document.createElement('canvas');
                collectesCanvas.id = 'responsableCollectesCanvas';
                responsableCollectesTarget.appendChild(collectesCanvas);

                new Chart(collectesCanvas, {
                    type: 'line',
                    data: {
                        labels: evolutionResponsable.map(item => item.label),
                        datasets: [{
                            label: 'Collectes',
                            data: evolutionResponsable.map(item => item.collectes),
                            borderColor: '#56988e',
                            backgroundColor: 'rgba(86, 152, 142, 0.12)',
                            fill: true,
                            tension: 0.35
                        }, {
                            label: 'Poids (kg)',
                            data: evolutionResponsable.map(item => item.poids),
                            borderColor: '#549678',
                            backgroundColor: 'rgba(84, 150, 120, 0.12)',
                            fill: true,
                            tension: 0.35
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'top' } },
                        scales: { y: { beginAtZero: true } }
                    }
                });

                const facturesCanvas = document.createElement('canvas');
                facturesCanvas.id = 'responsableFacturesCanvas';
                responsableFacturesTarget.appendChild(facturesCanvas);

                new Chart(facturesCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: ['Payees', 'En attente'],
                        datasets: [{
                            data: [repartitionResponsable.payees || 0, repartitionResponsable.en_attente || 0],
                            backgroundColor: ['#549678', '#f0ad4e']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom' } }
                    }
                });
            }
        } catch (error) {
            console.error('Erreur dashboard responsable:', error);
        }

        // ===== DASHBOARD COMPTABLE SITE =====
        try {
            const csRevenusTarget = document.getElementById('csRevenusChart');
            const csStatutsTarget = document.getElementById('csStatutsChart');

            if (csRevenusTarget) {
                const csEvolution = @json($cs_evolutionRevenus ?? []);
                const csCanvas = document.createElement('canvas');
                csRevenusTarget.appendChild(csCanvas);

                new Chart(csCanvas, {
                    type: 'bar',
                    data: {
                        labels: csEvolution.map(item => item.label),
                        datasets: [{
                            label: 'Montant facturé (FCFA)',
                            data: csEvolution.map(item => item.montant),
                            backgroundColor: 'rgba(86, 152, 142, 0.75)',
                            borderColor: '#56988e',
                            borderWidth: 1,
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'top' } },
                        scales: { y: { beginAtZero: true } }
                    }
                });
            }

            if (csStatutsTarget) {
                const csStatuts = @json($cs_repartitionStatuts ?? ['payees' => 0, 'en_attente' => 0]);
                const csStatutsCanvas = document.createElement('canvas');
                csStatutsTarget.appendChild(csStatutsCanvas);

                new Chart(csStatutsCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: ['Payées', 'En attente'],
                        datasets: [{
                            data: [csStatuts.payees || 0, csStatuts.en_attente || 0],
                            backgroundColor: ['#28a745', '#f0ad4e']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom' } }
                    }
                });
            }
        } catch (error) {
            console.error('Erreur dashboard comptable site:', error);
        }

        // ===== GRAPHIQUE D'ÉVOLUTION DES COLLECTES =====
        @can('rapports.collectes')
        try {
            const evolutionData = @json($evolutionCollectes ?? []);
            const ctxCollectes = document.getElementById('collectesChart');

            if (ctxCollectes) {
                // Créer un canvas
                const canvas = document.createElement('canvas');
                canvas.id = 'collectesChartCanvas';
                ctxCollectes.appendChild(canvas);

                const collectesChart = new Chart(canvas, {
                    type: 'line',
                    data: {
                        labels: evolutionData.map(item => item.label),
                        datasets: [{
                            label: 'Collectes',
                            data: evolutionData.map(item => item.collectes),
                            borderColor: '#3f7a60',
                            backgroundColor: 'rgba(63, 122, 96, 0.1)',
                            tension: 0.4,
                            fill: true
                        }, {
                            label: 'Poids (kg)',
                            data: evolutionData.map(item => item.poids),
                            borderColor: '#6fae8c',
                            backgroundColor: 'rgba(111, 174, 140, 0.12)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });

                window.updateChart = function(period) {
                    fetch(`/api/chart-data?period=${period}`)
                        .then(response => response.json())
                        .then(data => {
                            collectesChart.data.labels = data.labels;
                            collectesChart.data.datasets[0].data = data.collectes;
                            collectesChart.data.datasets[1].data = data.poids || [];
                            collectesChart.update();

                            document.querySelector('#chart-period').textContent =
                                period === '7days' ? '/7 jours' :
                                period === '1month' ? '/1 mois' : '/3 mois';
                        })
                        .catch(error => console.error('Erreur:', error));
                };
            }
        } catch (error) {
            console.error('Erreur graphique collectes:', error);
        }
        @endcan

        // ===== GRAPHIQUE RÉPARTITION PAR TYPE =====
        @can('rapports.collectes')
        try {
            const typesData = @json($typesDechets ?? []);
            const ctxTypes = document.getElementById('typesChart');

            if (ctxTypes && typesData.length > 0) {
                const canvas = document.createElement('canvas');
                canvas.id = 'typesChartCanvas';
                ctxTypes.appendChild(canvas);

                new Chart(canvas, {
                    type: 'doughnut',
                    data: {
                        labels: typesData.map(item => item.libelle),
                        datasets: [{
                            data: typesData.map(item => item.nombre),
                            backgroundColor: [
                                '#3f7a60',
                                '#56988e',
                                '#9fce89',
                                '#f0ad4e',
                                '#35604d',
                                '#87ae74',
                                '#20c997'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }
        } catch (error) {
            console.error('Erreur graphique types:', error);
        }
        @endcan

        // ===== GRAPHIQUE ÉVOLUTION MENSUELLE =====
        @can('rapports.financier')
        try {
            const evolutionMenData = @json($evolutionMensuelle ?? []);
            const ctxEvolution = document.getElementById('evolutionChart');

            if (ctxEvolution && evolutionMenData.length > 0) {
                const canvas = document.createElement('canvas');
                canvas.id = 'evolutionChartCanvas';
                ctxEvolution.appendChild(canvas);

                new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: evolutionMenData.map(item => item.mois),
                        datasets: [{
                            label: 'Collectes',
                            data: evolutionMenData.map(item => item.collectes),
                            backgroundColor: '#3f7a60',
                            yAxisID: 'y'
                        }, {
                            label: 'Revenus (FCFA)',
                            data: evolutionMenData.map(item => item.revenus),
                            type: 'line',
                            borderColor: '#6fae8c',
                            backgroundColor: 'rgba(111, 174, 140, 0.12)',
                            tension: 0.4,
                            yAxisID: 'y1'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                title: {
                                    display: true,
                                    text: 'Collectes'
                                }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                title: {
                                    display: true,
                                    text: 'Revenus (FCFA)'
                                },
                                grid: {
                                    drawOnChartArea: false
                                }
                            }
                        }
                    }
                });
            }
        } catch (error) {
            console.error('Erreur graphique évolution:', error);
        }
        @endcan

        // ===== FONCTIONS DE FILTRAGE =====
        window.filterData = function(type, period) {
            fetch(`/api/filter-data/${type}/${period}`)
                .then(response => response.json())
                .then(data => {
                    const element = document.querySelector(`#${type}Total`);
                    if (element) element.textContent = data.formatted;

                    const periodElement = document.querySelector(`#${type}-period`);
                    if (periodElement) {
                        periodElement.textContent =
                            period === 'today' ? '| Aujourd\'hui' :
                            period === 'month' ? '| Ce mois' : '| Cette année';
                    }
                })
                .catch(error => console.error('Erreur:', error));
        };

        window.filterCollectes = function(period) {
            fetch(`/api/filter-collectes/${period}`)
                .then(response => response.json())
                .then(data => {
                    const filterElement = document.querySelector('#collectes-filter');
                    if (filterElement) {
                        filterElement.textContent =
                            period === 'today' ? '| Aujourd\'hui' :
                            period === 'week' ? '| Cette semaine' : '| Ce mois';
                    }
                })
                .catch(error => console.error('Erreur:', error));
        };
    });
</script>
@endsection

@push('scripts')
<script src="{{ asset('backend/assets/vendor/chart.js/chart.umd.js') }}"></script>
@endpush
@endsection
