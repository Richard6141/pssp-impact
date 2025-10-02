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

            <!-- Collectes -->
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

            <!-- Factures -->
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

            <!-- Revenus -->
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

            <!-- Sites Actifs -->
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

            <!-- ===== INDICATEURS SUPPLÉMENTAIRES ===== -->

            <!-- Taux de validation -->
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

            <!-- Factures impayées -->
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

            <!-- ===== GRAPHIQUES ===== -->

            <!-- Évolution des Collectes -->
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

            <!-- Activités Récentes DYNAMIQUES -->
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

            <!-- Répartition par Type de Déchet -->
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

            <!-- Top Sites -->
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

            <!-- Actions Rapides -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Actions Rapides</h5>
                        <div class="row g-3">
                            <div class="col-6">
                                <a href="{{ route('collectes.create') }}"
                                    class="btn btn-primary w-100 d-flex flex-column align-items-center p-3">
                                    <i class="bi bi-plus-circle fs-2 mb-2"></i>
                                    <span>Nouvelle Collecte</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('factures.create') }}"
                                    class="btn btn-success w-100 d-flex flex-column align-items-center p-3">
                                    <i class="bi bi-receipt fs-2 mb-2"></i>
                                    <span>Créer Facture</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('sites.index') }}"
                                    class="btn btn-warning w-100 d-flex flex-column align-items-center p-3">
                                    <i class="bi bi-hospital fs-2 mb-2"></i>
                                    <span>Gérer Sites</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('rapports.collectes') }}"
                                    class="btn btn-info w-100 d-flex flex-column align-items-center p-3">
                                    <i class="bi bi-bar-chart fs-2 mb-2"></i>
                                    <span>Rapports</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Évolution Mensuelle -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Évolution Mensuelle</h5>
                        <div id="evolutionChart" style="min-height: 300px;"></div>
                    </div>
                </div>
            </div>

            <!-- Collectes Récentes avec Plus de Détails -->
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
                                        <th scope="col">Actions</th>
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
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('collectes.show', $collecte['numero_collecte']) }}"
                                                    class="btn btn-outline-primary" title="Voir détails">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('collectes.edit', $collecte['numero_collecte']) }}"
                                                    class="btn btn-outline-warning" title="Modifier">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
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
    </section>

    <!-- ===== STYLES PERSONNALISÉS ===== -->
    <style>
        /* Cartes de statistiques */
        .collectes-card .card-icon {
            background: linear-gradient(60deg, #4f46e5, #7c3aed);
        }

        .factures-card .card-icon {
            background: linear-gradient(60deg, #059669, #0d9488);
        }

        .revenue-card .card-icon {
            background: linear-gradient(60deg, #dc2626, #ea580c);
        }

        .customers-card .card-icon {
            background: linear-gradient(60deg, #7c2d12, #a16207);
        }

        .validation-card .card-icon {
            background: linear-gradient(60deg, #16a34a, #15803d);
        }

        .impayees-card .card-icon {
            background: linear-gradient(60deg, #ea580c, #dc2626);
        }

        .card-icon {
            color: white;
            width: 60px;
            height: 60px;
            box-shadow: 0 4px 20px 0px rgba(0, 0, 0, .14), 0 7px 10px -5px rgba(0, 0, 0, .4);
        }

        /* Activités récentes améliorées */
        .activity-item {
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }

        .activity-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .activity-badge {
            font-size: 0.8rem;
            margin: 0 1rem;
        }

        .activite-label {
            font-size: 0.75rem;
            color: #6c757d;
            white-space: nowrap;
            min-width: 50px;
        }

        /* Améliorations visuelles */
        .card-title span {
            font-size: 0.875rem;
            color: #6c757d;
        }

        .table-responsive {
            border-radius: 0.375rem;
        }

        .btn-group-sm .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        /* Animation pour les cartes */
        .info-card:hover {
            transform: translateY(-2px);
            transition: transform 0.2s ease;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.1);
        }
    </style>

    <!-- ===== SCRIPTS POUR GRAPHIQUES ET INTERACTIVITÉ ===== -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script>
        // Variables globales pour les graphiques
        let collectesChart, typesChart, evolutionChart;

        document.addEventListener("DOMContentLoaded", () => {
            initializeCharts();

            // Rafraîchissement automatique toutes les 5 minutes
            setInterval(refreshData, 300000);
        });

        function initializeCharts() {
            // ===== GRAPHIQUE D'ÉVOLUTION DES COLLECTES =====
            const collectesCtx = document.querySelector("#collectesChart");
            collectesChart = new Chart(collectesCtx, {
                type: 'line',
                data: {
                    labels: {
                        !!json_encode($evolutionCollectes - > pluck('label')) !!
                    },
                    datasets: [{
                        label: 'Nombre de collectes',
                        data: {
                            !!json_encode($evolutionCollectes - > pluck('collectes')) !!
                        },
                        borderColor: 'rgb(79, 70, 229)',
                        backgroundColor: 'rgba(79, 70, 229, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: 'rgb(79, 70, 229)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 6
                    }, {
                        label: 'Poids total (kg)',
                        data: {
                            !!json_encode($evolutionCollectes - > pluck('poids')) !!
                        },
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.4,
                        yAxisID: 'y1'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                        }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Nombre de collectes'
                            },
                            beginAtZero: true
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Poids (kg)'
                            },
                            grid: {
                                drawOnChartArea: false
                            },
                            beginAtZero: true
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            // ===== GRAPHIQUE TYPES DE DÉCHETS =====
            const typesCtx = document.querySelector("#typesChart");
            const typesData = {
                !!json_encode($typesDechets) !!
            };

            typesChart = new Chart(typesCtx, {
                type: 'doughnut',
                data: {
                    labels: typesData.map(item => item.libelle),
                    datasets: [{
                        data: typesData.map(item => item.nombre),
                        backgroundColor: [
                            'rgba(79, 70, 229, 0.8)',
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(245, 101, 101, 0.8)',
                            'rgba(251, 191, 36, 0.8)',
                            'rgba(139, 92, 246, 0.8)',
                            'rgba(236, 72, 153, 0.8)'
                        ],
                        borderWidth: 3,
                        borderColor: '#fff',
                        hoverBorderWidth: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }, // Légendes dans le HTML
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const type = typesData[context.dataIndex];
                                    return `${context.label}: ${context.parsed} collectes (${type.poids_total}kg)`;
                                }
                            }
                        }
                    }
                }
            });

            // ===== GRAPHIQUE ÉVOLUTION MENSUELLE =====
            const evolutionCtx = document.querySelector("#evolutionChart");
            const evolutionData = {
                !!json_encode($evolutionMensuelle) !!
            };

            evolutionChart = new Chart(evolutionCtx, {
                type: 'bar',
                data: {
                    labels: evolutionData.map(item => item.mois),
                    datasets: [{
                        label: 'Collectes',
                        data: evolutionData.map(item => item.collectes),
                        backgroundColor: 'rgba(79, 70, 229, 0.6)',
                        borderColor: 'rgba(79, 70, 229, 1)',
                        borderWidth: 1,
                        yAxisID: 'y'
                    }, {
                        label: 'Revenus (FCFA)',
                        data: evolutionData.map(item => item.revenus),
                        type: 'line',
                        borderColor: 'rgba(16, 185, 129, 1)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.4,
                        yAxisID: 'y1'
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
                            type: 'linear',
                            display: true,
                            position: 'left',
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Collectes'
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            beginAtZero: true,
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

        // ===== FONCTIONS D'INTERACTIVITÉ =====

        function updateChart(period) {
            fetch(`/dashboard/chart-data?period=${period}`)
                .then(response => response.json())
                .then(data => {
                    collectesChart.data.labels = data.labels;
                    collectesChart.data.datasets[0].data = data.collectes;
                    if (data.poids) {
                        collectesChart.data.datasets[1].data = data.poids;
                    }
                    collectesChart.update();

                    // Mise à jour du titre
                    const periodText = {
                        '7days': '/7 jours',
                        '1month': '/1 mois',
                        '3months': '/3 mois'
                    };
                    document.getElementById('chart-period').textContent = periodText[period];
                })
                .catch(error => console.error('Erreur:', error));
        }

        function filterData(type, period) {
            // Implémentation du filtrage par période
            console.log(`Filtrage ${type} pour ${period}`);
            // Ici vous pouvez ajouter l'AJAX pour filtrer les données
        }

        function filterCollectes(period) {
            const filterText = {
                'today': '| Aujourd\'hui',
                'week': '| Cette semaine',
                'month': '| Ce mois'
            };
            document.getElementById('collectes-filter').textContent = filterText[period];

            // Ici vous pouvez ajouter l'AJAX pour filtrer le tableau
        }

        function refreshData() {
            // Rafraîchissement automatique des données
            fetch('/dashboard/refresh')
                .then(response => response.json())
                .then(data => {
                    // Mise à jour des statistiques
                    document.getElementById('collectesTotal').textContent = data.collectesTotal;
                    document.getElementById('facturesTotal').textContent = data.facturesTotal;
                    document.getElementById('montantTotal').textContent =
                        new Intl.NumberFormat('fr-FR').format(data.montantTotal) + ' FCFA';
                    document.getElementById('sitesActifs').textContent = data.sitesActifs;
                })
                .catch(error => console.error('Erreur de rafraîchissement:', error));
        }

        // ===== ANIMATIONS D'ENTRÉE =====

        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observer toutes les cartes
        document.querySelectorAll('.card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });
    </script>
    @endrole
</main>
@endsection