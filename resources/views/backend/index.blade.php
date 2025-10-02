@extends('layouts.back')

@section('content')
<main id="main" class="main">
    @role('Super Admin|Coordonnateur|Comptable|Agent marketing|Administrateur|Agent collecte')
    <div class="pagetitle">
        <h1>Dashboard</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
                <li class="breadcrumb-item active">Tableau de Bord</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        <div class="row">

            <!-- ===== CARTES DE STATISTIQUES ===== -->

            <!-- Collectes - Permission: collectes.view -->
            @can('collectes.view')
            <div class="col-xxl-3 col-md-6">
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
            <div class="col-xxl-3 col-md-6">
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
            <div class="col-xxl-3 col-md-6">
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
            <div class="col-xxl-3 col-md-6">
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
            <div class="col-xxl-3 col-md-6">
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
            <div class="col-xxl-3 col-md-6">
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
                        <div id="collectesChart" style="min-height: 400px;"></div>
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

            <!-- Répartition par Type de Déchet - Permission: rapports.collectes -->
            @can('rapports.collectes')
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Répartition par Type de Déchet</h5>
                        <div id="typesChart" style="min-height: 400px;"></div>

                        <!-- Légendes détaillées -->
                        <div class="mt-3">
                            @foreach($typesDechets as $type)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>{{ $type->libelle }}</span>
                                <div class="text-end">
                                    <strong>{{ $type->nombre }} collectes</strong><br>
                                    <small class="text-muted">{{ number_format($type->poids_total, 1) }} kg</small>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endcan

            <!-- Top Sites - Permission: rapports.sites -->
            @can('rapports.sites')
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Sites Les Plus Actifs <span>| Ce mois</span></h5>
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <thead>
                                    <tr>
                                        <th>Site</th>
                                        <th>Collectes</th>
                                        <th>Poids Total</th>
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
                                        <td>{{ number_format($site->poids_total, 1) }} kg</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endcan

            <!-- Actions Rapides - Permissions multiples -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Actions Rapides</h5>
                        <div class="row g-3">
                            @can('collectes.create')
                            <div class="col-6">
                                <a href="{{ route('collectes.create') }}"
                                    class="btn btn-primary w-100 d-flex flex-column align-items-center p-3">
                                    <i class="bi bi-plus-circle fs-2 mb-2"></i>
                                    <span>Nouvelle Collecte</span>
                                </a>
                            </div>
                            @endcan

                            @can('factures.create')
                            <div class="col-6">
                                <a href="{{ route('factures.create') }}"
                                    class="btn btn-success w-100 d-flex flex-column align-items-center p-3">
                                    <i class="bi bi-receipt fs-2 mb-2"></i>
                                    <span>Créer Facture</span>
                                </a>
                            </div>
                            @endcan

                            @can('sites.view')
                            <div class="col-6">
                                <a href="{{ route('sites.index') }}"
                                    class="btn btn-warning w-100 d-flex flex-column align-items-center p-3">
                                    <i class="bi bi-hospital fs-2 mb-2"></i>
                                    <span>Gérer Sites</span>
                                </a>
                            </div>
                            @endcan

                            @can('rapports.view')
                            <div class="col-6">
                                <a href="{{ route('rapports.collectes') }}"
                                    class="btn btn-info w-100 d-flex flex-column align-items-center p-3">
                                    <i class="bi bi-bar-chart fs-2 mb-2"></i>
                                    <span>Rapports</span>
                                </a>
                            </div>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>

            <!-- Évolution Mensuelle - Permission: rapports.financier -->
            @can('rapports.financier')
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Évolution Mensuelle</h5>
                        <div id="evolutionChart" style="min-height: 300px;"></div>
                    </div>
                </div>
            </div>
            @endcan

            <!-- Collectes Récentes - Permission: collectes.view -->
            @can('collectes.view')
            <div class="col-12">
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
                            <table class="table table-borderless datatable" id="collectesTable">
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
                                        <td><strong>{{ $collecte['poids'] }} kg</strong></td>
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

        </div>
        @endrole
    </section>
</main>
@endsection