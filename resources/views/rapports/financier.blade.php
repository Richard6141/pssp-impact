@extends('layouts.back')

@section('title', 'Rapport Financier')

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

.revenue-card .card-icon {
    background: linear-gradient(90deg, #2eca6a, #56d477);
}

.invoice-card .card-icon {
    background: linear-gradient(90deg, #4154f1, #677de9);
}

.payment-card .card-icon {
    background: linear-gradient(90deg, #ff771d, #ff9447);
}

.pending-card .card-icon {
    background: linear-gradient(90deg, #ff5757, #ff7b7b);
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

.stat-comparison {
    font-size: 14px;
    margin-top: 5px;
}

.stat-comparison.positive {
    color: var(--success-color);
}

.stat-comparison.negative {
    color: var(--danger-color);
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
        <h1>Rapport Financier</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bi bi-house"></i> Accueil</a>
                </li>
                <li class="breadcrumb-item">Rapports</li>
                <li class="breadcrumb-item active">Financier</li>
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
                        <form method="GET" action="{{ route('rapports.financier') }}">
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
                                    <label for="statut_facture" class="form-label">Statut Facture</label>
                                    <select class="form-select" id="statut_facture" name="statut_facture">
                                        <option value="">Tous les statuts</option>
                                        <option value="en_attente"
                                            {{ $statutFacture == 'en_attente' ? 'selected' : '' }}>En attente</option>
                                        <option value="payee" {{ $statutFacture == 'payee' ? 'selected' : '' }}>Payée
                                        </option>
                                        <option value="partiellement_payee"
                                            {{ $statutFacture == 'partiellement_payee' ? 'selected' : '' }}>
                                            Partiellement payée</option>
                                        <option value="annulee" {{ $statutFacture == 'annulee' ? 'selected' : '' }}>
                                            Annulée</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="mode_paiement" class="form-label">Mode Paiement</label>
                                    <select class="form-select" id="mode_paiement" name="mode_paiement">
                                        <option value="">Tous les modes</option>
                                        <option value="especes" {{ $modePaiement == 'especes' ? 'selected' : '' }}>
                                            Espèces</option>
                                        <option value="cheque" {{ $modePaiement == 'cheque' ? 'selected' : '' }}>Chèque
                                        </option>
                                        <option value="virement" {{ $modePaiement == 'virement' ? 'selected' : '' }}>
                                            Virement</option>
                                        <option value="mobile_money"
                                            {{ $modePaiement == 'mobile_money' ? 'selected' : '' }}>Mobile Money
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
                <div class="card info-card revenue-card animate-on-scroll">
                    <div class="card-body">
                        <h5 class="card-title">Revenu Total <span>| Ce mois</span></h5>
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-currency-dollar"></i>
                            </div>
                            <div class="ps-3">
                                <h6>{{ number_format($stats['revenu_total'], 0, ',', ' ') }} FCFA</h6>
                                @if($stats['revenu_total'] > 0)
                                <span class="text-success small pt-1 fw-bold">+{{ rand(5, 15) }}%</span>
                                <span class="text-muted small pt-2 ps-1">ce mois</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-3 col-md-6">
                <div class="card info-card invoice-card animate-on-scroll">
                    <div class="card-body">
                        <h5 class="card-title">Total Factures <span>| Ce mois</span></h5>
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-receipt"></i>
                            </div>
                            <div class="ps-3">
                                <h6>{{ number_format($stats['total_factures']) }}</h6>
                                <span class="text-muted small pt-2">
                                    Montant: {{ number_format($stats['montant_facture'], 0, ',', ' ') }} FCFA
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-3 col-md-6">
                <div class="card info-card payment-card animate-on-scroll">
                    <div class="card-body">
                        <h5 class="card-title">Paiements Reçus <span>| Ce mois</span></h5>
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-cash-coin"></i>
                            </div>
                            <div class="ps-3">
                                <h6>{{ number_format($stats['total_paiements']) }}</h6>
                                <span class="text-success small pt-1 fw-bold">
                                    {{ number_format($stats['montant_paiements'], 0, ',', ' ') }} FCFA
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-3 col-md-6">
                <div class="card info-card pending-card animate-on-scroll">
                    <div class="card-body">
                        <h5 class="card-title">Impayés <span>| Ce mois</span></h5>
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-exclamation-triangle"></i>
                            </div>
                            <div class="ps-3">
                                <h6>{{ number_format($stats['montant_impayes'], 0, ',', ' ') }} FCFA</h6>
                                <span
                                    class="text-{{ $stats['factures_impayees'] > 5 ? 'danger' : 'warning' }} small pt-1 fw-bold">
                                    {{ $stats['factures_impayees'] }} facture(s)
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
            <!-- Évolution des revenus -->
            <div class="col-12">
                <div class="card animate-on-scroll">
                    <div class="card-body">
                        <h5 class="card-title">Évolution des Revenus <span>| 6 derniers mois</span></h5>
                        <div id="revenusChart" class="chart-container"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphiques secondaires -->
        <div class="row mt-4">
            <div class="col-lg-6">
                <div class="card animate-on-scroll">
                    <div class="card-body">
                        <h5 class="card-title">Répartition par Mode de Paiement</h5>
                        <div id="modePaiementChart" class="chart-container"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card animate-on-scroll">
                    <div class="card-body">
                        <h5 class="card-title">Statut des Factures</h5>
                        <div id="statutFactureChart" class="chart-container"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tableaux de données -->
        <div class="row mt-4">
            <!-- Factures récentes -->
            <div class="col-lg-12">
                <div class="card animate-on-scroll">
                    <div class="card-body">
                        <h5 class="card-title">Factures Récentes <span>| Aujourd'hui</span></h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Numéro</th>
                                        <th>Date</th>
                                        <th>Site</th>
                                        <th>Montant</th>
                                        <th>Payé</th>
                                        <th>Reste</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($factures->take(10) as $facture)
                                    <tr>
                                        <td>
                                            <a href="{{ route('factures.show', $facture->facture_id) }}"
                                                class="text-primary">
                                                {{ $facture->numero_facture }}
                                            </a>
                                        </td>
                                        <td>{{ $facture->date_facture->format('d/m/Y') }}</td>
                                        <td>{{ $facture->site->site_name ?? 'N/A' }}</td>
                                        <td class="text-end">{{ number_format($facture->montant_facture, 0, ',', ' ') }}
                                            FCFA</td>
                                        <td class="text-end text-success">
                                            {{ number_format($facture->paiements->sum('montant'), 0, ',', ' ') }} FCFA
                                        </td>
                                        <td class="text-end text-danger">
                                            {{ number_format($facture->montant_facture - $facture->paiements->sum('montant'), 0, ',', ' ') }}
                                            FCFA
                                        </td>
                                        <td>
                                            <span
                                                class="badge bg-{{ $facture->statut == 'payee' ? 'success' : ($facture->statut == 'en_attente' ? 'warning' : 'info') }}">
                                                {{ ucfirst(str_replace('_', ' ', $facture->statut)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                @can('factures.view')
                                                <a href="{{ route('factures.show', $facture->facture_id) }}"
                                                    class="btn btn-outline-primary" title="Voir">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                @endcan
                                                @can('paiements.record')
                                                <a href="{{ route('paiements.create', ['facture' => $facture->facture_id]) }}"
                                                    class="btn btn-outline-success" title="Paiement">
                                                    <i class="bi bi-cash"></i>
                                                </a>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="bi bi-inbox display-4 text-muted"></i>
                                            <p class="text-muted mt-2">Aucune facture trouvée pour cette période</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $factures->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Sites par Revenus -->
        <div class="row mt-4">
            <div class="col-lg-6">
                <div class="card animate-on-scroll">
                    <div class="card-body">
                        <h5 class="card-title">Top Sites par Revenus</h5>
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <thead>
                                    <tr>
                                        <th>Site</th>
                                        <th>Factures</th>
                                        <th class="text-end">Montant</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topSites as $top)
                                    <tr>
                                        <td>{{ $top->site_name }}</td>
                                        <td><span class="badge bg-primary">{{ $top->nombre_factures }}</span></td>
                                        <td class="text-end fw-bold">
                                            {{ number_format($top->montant_total, 0, ',', ' ') }} FCFA
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card animate-on-scroll">
                    <div class="card-body">
                        <h5 class="card-title">Derniers Paiements</h5>
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <thead>
                                    <tr>
                                        <th>Numéro</th>
                                        <th>Date</th>
                                        <th>Mode</th>
                                        <th class="text-end">Montant</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($derniersPaiements as $paiement)
                                    <tr>
                                        <td>{{ $paiement->numero_paiement }}</td>
                                        <td>{{ $paiement->date_paiement->format('d/m/Y') }}</td>
                                        <td><span class="badge bg-info">{{ ucfirst($paiement->mode_paiement) }}</span>
                                        </td>
                                        <td class="text-end fw-bold text-success">
                                            {{ number_format($paiement->montant, 0, ',', ' ') }} FCFA
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
    </section>
</main>
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

    // Graphique évolution des revenus
    new ApexCharts(document.querySelector("#revenusChart"), {
        series: [{
            name: 'Factures',
            data: {
                !!json_encode($evolutionMensuelle - > pluck('montant_factures')) !!
            }
        }, {
            name: 'Paiements',
            data: {
                !!json_encode($evolutionMensuelle - > pluck('montant_paiements')) !!
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
        },
        yaxis: {
            labels: {
                formatter: function(value) {
                    return value.toLocaleString() + ' FCFA';
                }
            }
        },
        tooltip: {
            y: {
                formatter: function(value) {
                    return value.toLocaleString() + ' FCFA';
                }
            }
        }
    }).render();

    // Graphique modes de paiement
    new ApexCharts(document.querySelector("#modePaiementChart"), {
        series: {
            !!json_encode(array_values($repartitionModePaiement - > toArray())) !!
        },
        chart: {
            type: 'donut',
            height: 350
        },
        labels: {
            !!json_encode(array_keys($repartitionModePaiement - > toArray())) !!
        },
        colors: ['#2eca6a', '#4154f1', '#ff771d', '#0dcaf0'],
        legend: {
            position: 'bottom'
        }
    }).render();

    // Graphique statuts factures
    new ApexCharts(document.querySelector("#statutFactureChart"), {
        series: {
            !!json_encode(array_values($repartitionStatutFacture - > toArray())) !!
        },
        chart: {
            type: 'pie',
            height: 350
        },
        labels: {
            !!json_encode(array_keys($repartitionStatutFacture - > toArray())) !!
        },
        colors: ['#2eca6a', '#ff771d', '#4154f1', '#ff5757']
    }).render();
});

// Export functions
function exportPDF() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'pdf');
    window.open(`{{ route('rapports.financier') }}?${params.toString()}`);
}

function exportExcel() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'excel');
    window.location.href = `{{ route('rapports.financier') }}?${params.toString()}`;
}
</script>
@endpush