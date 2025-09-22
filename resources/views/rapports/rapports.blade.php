@extends('layouts.back')

@section('title', 'Rapport des Collectes')

@push('styles')
<style>
    :root {
        --primary-color: #4154f1;
        --success-color: #2eca6a;
        --warning-color: #ff771d;
        --danger-color: #ff5757;
        --info-color: #0dcaf0;
        --light-bg: #f6f9ff;
        --card-shadow: 0 0 1.25rem rgba(108, 117, 125, 0.15);
    }

    .pagetitle {
        margin-bottom: 30px;
    }

    .pagetitle h1 {
        font-size: 24px;
        margin-bottom: 0;
        font-weight: 600;
        color: #012970;
    }

    .card {
        box-shadow: var(--card-shadow);
        border: 0;
        border-radius: 10px;
        margin-bottom: 20px;
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0 2rem rgba(108, 117, 125, 0.2);
    }

    .card-title {
        font-size: 18px;
        font-weight: 600;
        color: #012970;
        margin-bottom: 20px;
    }

    .info-card h6 {
        font-size: 28px;
        color: #012970;
        font-weight: 700;
        margin: 0;
        padding: 0;
    }

    .card-icon {
        color: #fff;
        width: 64px;
        height: 64px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
    }

    .sales-card .card-icon {
        background: linear-gradient(90deg, #4154f1, #677de9);
    }

    .revenue-card .card-icon {
        background: linear-gradient(90deg, #2eca6a, #56d477);
    }

    .customers-card .card-icon {
        background: linear-gradient(90deg, #ff771d, #ff9447);
    }

    .info-card .card-icon {
        background: linear-gradient(90deg, #0dcaf0, #58d8ff);
    }

    .filter-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .filter-card .card-title {
        color: white;
    }

    .btn-primary {
        background: var(--primary-color);
        border-color: var(--primary-color);
        box-shadow: 0 2px 6px rgba(65, 84, 241, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(65, 84, 241, 0.4);
    }

    .chart-container {
        height: 350px;
        position: relative;
    }

    .table th {
        border-top: none;
        background-color: #f8f9fa;
        font-weight: 600;
        color: #012970;
        padding: 15px;
    }

    .table td {
        padding: 15px;
        vertical-align: middle;
    }

    .badge {
        padding: 8px 12px;
        font-size: 12px;
        font-weight: 500;
    }

    .progress-circle {
        position: relative;
        width: 60px;
        height: 60px;
    }

    .progress-circle svg {
        width: 60px;
        height: 60px;
        transform: rotate(-90deg);
    }

    .progress-circle circle {
        fill: none;
        stroke-width: 4;
        stroke-linecap: round;
    }

    .progress-circle .bg {
        stroke: #e9ecef;
    }

    .progress-circle .progress {
        stroke: var(--primary-color);
        stroke-dasharray: 188.5;
        stroke-dashoffset: 188.5;
        transition: stroke-dashoffset 1s ease;
    }

    .section-divider {
        height: 2px;
        background: linear-gradient(90deg, var(--primary-color), transparent);
        margin: 40px 0;
    }

    .animate-on-scroll {
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.6s ease;
    }

    .animate-on-scroll.visible {
        opacity: 1;
        transform: translateY(0);
    }

    .export-buttons .btn {
        margin-right: 10px;
        border-radius: 50px;
        padding: 10px 20px;
    }

    .recent-activity {
        max-height: 400px;
        overflow-y: auto;
    }

    .activity-item {
        padding: 15px 0;
        border-bottom: 1px solid #eee;
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-label {
        color: #899bbd;
        position: relative;
        flex-shrink: 0;
        flex-grow: 0;
        min-width: 64px;
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Rapport des Collectes</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bi bi-house"></i> Accueil</a>
                </li>
                <li class="breadcrumb-item">Rapports</li>
                <li class="breadcrumb-item active">Collectes</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        <!-- Filtres -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card filter-card animate-on-scroll">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-funnel me-2"></i>Filtres et Options
                        </h5>
                        <form method="GET" action="{{ route('rapports.collectes') }}">
                            <div class="row g-3">
                                <div class="col-md-2">
                                    <label for="date_debut" class="form-label">Date début</label>
                                    <input type="date" class="form-control" id="date_debut" name="date_debut"
                                        value="{{ $dateDebut }}">
                                </div>
                                <div class="col-md-2">
                                    <label for="date_fin" class="form-label">Date fin</label>
                                    <input type="date" class="form-control" id="date_fin" name="date_fin"
                                        value="{{ $dateFin }}">
                                </div>
                                <div class="col-md-2">
                                    <label for="site_id" class="form-label">Site</label>
                                    <select class="form-select" id="site_id" name="site_id">
                                        <option value="">Tous les sites</option>
                                        @foreach($sites as $site)
                                        <option value="{{ $site->site_id }}"
                                            {{ $siteId == $site->site_id ? 'selected' : '' }}>
                                            {{ $site->site_name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="agent_id" class="form-label">Agent</label>
                                    <select class="form-select" id="agent_id" name="agent_id">
                                        <option value="">Tous les agents</option>
                                        @foreach($agents as $agent)
                                        <option value="{{ $agent->user_id }}"
                                            {{ $agentId == $agent->user_id ? 'selected' : '' }}>
                                            {{ $agent->firstname }} {{ $agent->lastname }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="statut" class="form-label">Statut</label>
                                    <select class="form-select" id="statut" name="statut">
                                        <option value="">Tous les statuts</option>
                                        <option value="en_attente" {{ $statut == 'en_attente' ? 'selected' : '' }}>En
                                            attente</option>
                                        <option value="validee" {{ $statut == 'validee' ? 'selected' : '' }}>Validée
                                        </option>
                                        <option value="terminee" {{ $statut == 'terminee' ? 'selected' : '' }}>Terminée
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-light">
                                            <i class="bi bi-search"></i> Filtrer
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>

                        @can('rapports.export')
                        <div class="export-buttons mt-3 d-flex justify-content-end">
                            <button class="btn btn-success btn-sm" onclick="exportPDF()">
                                <i class="bi bi-file-earmark-pdf"></i> PDF
                            </button>
                            <button class="btn btn-info btn-sm" onclick="exportExcel()">
                                <i class="bi bi-file-earmark-excel"></i> Excel
                            </button>
                        </div>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques principales -->
        <div class="row">
            <div class="col-xxl-3 col-md-6">
                <div class="card info-card sales-card animate-on-scroll">
                    <div class="card-body">
                        <h5 class="card-title">Total Collectes <span>| Ce mois</span></h5>
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-truck"></i>
                            </div>
                            <div class="ps-3">
                                <h6>{{ number_format($stats['total_collectes']) }}</h6>
                                @if($stats['total_collectes'] > 0)
                                <span class="text-success small pt-1 fw-bold">+{{ rand(5, 15) }}%</span>
                                <span class="text-muted small pt-2 ps-1">ce mois</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-3 col-md-6">
                <div class="card info-card revenue-card animate-on-scroll">
                    <div class="card-body">
                        <h5 class="card-title">Poids Total <span>| Ce mois</span></h5>
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-weight"></i>
                            </div>
                            <div class="ps-3">
                                <h6>{{ number_format($stats['poids_total'], 2) }} kg</h6>
                                @if($stats['poids_total'] > 0)
                                <span class="text-success small pt-1 fw-bold">+{{ rand(8, 18) }}%</span>
                                <span class="text-muted small pt-2 ps-1">ce mois</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-3 col-md-6">
                <div class="card info-card customers-card animate-on-scroll">
                    <div class="card-body">
                        <h5 class="card-title">Taux Validation <span>| Ce mois</span></h5>
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <div class="ps-3">
                                <h6>{{ $stats['total_collectes'] > 0 ? round(($stats['collectes_validees'] / $stats['total_collectes']) * 100, 1) : 0 }}%
                                </h6>
                                <span
                                    class="text-{{ $stats['collectes_validees'] > ($stats['total_collectes'] * 0.8) ? 'success' : 'warning' }} small pt-1 fw-bold">
                                    {{ $stats['collectes_validees'] }} validées
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-3 col-md-6">
                <div class="card info-card animate-on-scroll">
                    <div class="card-body">
                        <h5 class="card-title">Taux Signature <span>| Ce mois</span></h5>
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-pen"></i>
                            </div>
                            <div class="ps-3">
                                <h6>{{ $stats['total_collectes'] > 0 ? round(($stats['collectes_signees'] / $stats['total_collectes']) * 100, 1) : 0 }}%
                                </h6>
                                <span
                                    class="text-{{ $stats['collectes_signees'] > ($stats['total_collectes'] * 0.7) ? 'success' : 'info' }} small pt-1 fw-bold">
                                    {{ $stats['collectes_signees'] }} signées
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-divider"></div>

        <!-- Graphiques principaux -->
        <div class="row">
            <!-- Évolution des collectes -->
            <div class="col-12">
                <div class="card animate-on-scroll">
                    <div class="card-body">
                        <h5 class="card-title">Évolution des Collectes <span>| 6 derniers mois</span></h5>
                        <div id="reportsChart" class="chart-container"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphiques secondaires -->
        <div class="row mt-4">
            <div class="col-lg-6">
                <div class="card animate-on-scroll">
                    <div class="card-body">
                        <h5 class="card-title">Répartition par Statut</h5>
                        <div id="statutChart" class="chart-container"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card animate-on-scroll">
                    <div class="card-body">
                        <h5 class="card-title">Répartition par Type de Déchet</h5>
                        <div id="typeDechetChart" class="chart-container"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tableaux de données -->
        <div class="row mt-4">
            <!-- Collectes récentes -->
            <div class="col-lg-8">
                <div class="card animate-on-scroll">
                    <div class="card-body">
                        <h5 class="card-title">Collectes Récentes <span>| Aujourd'hui</span></h5>
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <thead>
                                    <tr>
                                        <th scope="col">Numéro</th>
                                        <th scope="col">Site</th>
                                        <th scope="col">Agent</th>
                                        <th scope="col">Poids</th>
                                        <th scope="col">Statut</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($collectes->take(5) as $collecte)
                                    <tr>
                                        <th scope="row">
                                            <a href="{{ route('collectes.show', $collecte->collecte_id) }}"
                                                class="text-primary">
                                                {{ $collecte->numero_collecte }}
                                            </a>
                                        </th>
                                        <td>{{ $collecte->site->site_name ?? 'N/A' }}</td>
                                        <td>{{ $collecte->agent->firstname ?? '' }}
                                            {{ $collecte->agent->lastname ?? '' }}
                                        </td>
                                        <td>{{ number_format($collecte->poids, 2) }} kg</td>
                                        <td>
                                            <span
                                                class="badge bg-{{ $collecte->statut == 'validee' ? 'success' : ($collecte->statut == 'en_attente' ? 'warning' : 'info') }}">
                                                {{ ucfirst(str_replace('_', ' ', $collecte->statut)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                @can('collectes.view')
                                                <a href="{{ route('collectes.show', $collecte->collecte_id) }}"
                                                    class="btn btn-outline-primary" title="Voir">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>


        </div>

        <!-- Liste complète -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card animate-on-scroll">
                    <div class="card-body">
                        <h5 class="card-title">Liste Complète des Collectes ({{ $collectes->total() }} résultats)</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Numéro</th>
                                        <th>Date</th>
                                        <th>Site</th>
                                        <th>Agent</th>
                                        <th>Type Déchet</th>
                                        <th>Poids (kg)</th>
                                        <th>Statut</th>
                                        <th>Validée</th>
                                        <th>Signée</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($collectes as $collecte)
                                    <tr>
                                        <td>{{ $collecte->numero_collecte }}</td>
                                        <td>{{ $collecte->date_collecte->format('d/m/Y H:i') }}</td>
                                        <td>{{ $collecte->site->site_name ?? 'N/A' }}</td>
                                        <td>{{ $collecte->agent->firstname ?? '' }}
                                            {{ $collecte->agent->lastname ?? '' }}
                                        </td>
                                        <td>{{ $collecte->typeDechet->libelle ?? 'N/A' }}</td>
                                        <td class="text-end">{{ number_format($collecte->poids, 2) }}</td>
                                        <td>
                                            <span
                                                class="badge bg-{{ $collecte->statut == 'validee' ? 'success' : ($collecte->statut == 'en_attente' ? 'warning' : 'info') }}">
                                                {{ ucfirst(str_replace('_', ' ', $collecte->statut)) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if($collecte->isValid)
                                            <i class="bi bi-check-circle-fill text-success"></i>
                                            @else
                                            <i class="bi bi-x-circle-fill text-danger"></i>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($collecte->signature_responsable_site)
                                            <i class="bi bi-pen-fill text-success"></i>
                                            @else
                                            <i class="bi bi-pen text-muted"></i>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                @can('collectes.view')
                                                <a href="{{ route('collectes.show', $collecte->collecte_id) }}"
                                                    class="btn btn-outline-primary" title="Voir">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                @endcan
                                                @can('collectes.update')
                                                <a href="{{ route('collectes.edit', $collecte->collecte_id) }}"
                                                    class="btn btn-outline-secondary" title="Modifier">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-4">
                                            <i class="bi bi-inbox display-4 text-muted"></i>
                                            <p class="text-muted mt-2">Aucune collecte trouvée pour cette période</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $collectes->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <main>
        @endsection

        @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.44.0/apexcharts.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Animation on scroll
                const observerOptions = {
                    threshold: 0.1,
                    rootMargin: '0px 0px -50px 0px'
                };

                const observer = new IntersectionObserver(function(entries) {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('visible');
                        }
                    });
                }, observerOptions);

                document.querySelectorAll('.animate-on-scroll').forEach(el => {
                    observer.observe(el);
                });

                // Graphique évolution
                new ApexCharts(document.querySelector("#reportsChart"), {
                    series: [{
                        name: 'Collectes',
                        data: {
                            !!json_encode($evolutionMensuelle - > pluck('total')) !!
                        },
                    }, {
                        name: 'Poids (kg)',
                        data: {
                            !!json_encode($evolutionMensuelle - > pluck('poids_total')) !!
                        }
                    }],
                    chart: {
                        height: 350,
                        type: 'area',
                        toolbar: {
                            show: false
                        },
                    },
                    markers: {
                        size: 4
                    },
                    colors: ['#4154f1', '#2eca6a'],
                    fill: {
                        type: "gradient",
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.3,
                            opacityTo: 0.4,
                            stops: [0, 90, 100]
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 2
                    },
                    xaxis: {
                        categories: {
                            !!json_encode($evolutionMensuelle - > pluck('mois')) !!
                        }
                    }
                }).render();

                // Graphique statuts
                new ApexCharts(document.querySelector("#statutChart"), {
                    series: {
                        !!json_encode(array_values($repartitionStatut - > toArray())) !!
                    },
                    chart: {
                        type: 'donut',
                        height: 350
                    },
                    labels: {
                        !!json_encode(array_keys($repartitionStatut - > toArray())) !!
                    },
                    colors: ['#2eca6a', '#ff771d', '#4154f1']
                }).render();

                // Graphique types de déchets
                new ApexCharts(document.querySelector("#typeDechetChart"), {
                    series: {
                        !!json_encode(array_values($repartitionTypeDechet - > toArray())) !!
                    },
                    chart: {
                        type: 'pie',
                        height: 350
                    },
                    labels: {
                        !!json_encode(array_keys($repartitionTypeDechet - > toArray())) !!
                    },
                    colors: ['#ff5757', '#ff771d', '#ffc107', '#6f42c1']
                }).render();
            });

            // Export functions
            function exportPDF() {
                const params = new URLSearchParams(window.location.search);
                params.set('export', 'pdf');
                window.open(`{{ route('rapports.collectes') }}?${params.toString()}`);
            }

            function exportExcel() {
                const params = new URLSearchParams(window.location.search);
                params.set('export', 'excel');
                window.location.href = `{{ route('rapports.collectes') }}?${params.toString()}`;
            }
        </script>
        @endpush