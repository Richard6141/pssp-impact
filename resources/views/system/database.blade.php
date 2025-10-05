@extends('layouts.back')

@section('title', 'Base de données')

@section('content')
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Gestion de la Base de données</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
                <li class="breadcrumb-item">Système</li>
                <li class="breadcrumb-item active">Base de données</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <!-- Actions rapides -->
        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Actions</h5>
                        <button type="button" class="btn btn-success" onclick="optimizeDatabase()">
                            <i class="bi bi-speedometer"></i> Optimiser la base de données
                        </button>
                        <button type="button" class="btn btn-info" onclick="location.reload()">
                            <i class="bi bi-arrow-clockwise"></i> Actualiser
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques générales -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="bi bi-table text-primary display-6"></i>
                        <h5 class="mt-2">Tables</h5>
                        <h3 class="text-primary">{{ $stats['total_tables'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="bi bi-list-ol text-success display-6"></i>
                        <h5 class="mt-2">Lignes totales</h5>
                        <h3 class="text-success">{{ number_format($stats['total_rows'], 0, ',', ' ') }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="bi bi-hdd text-info display-6"></i>
                        <h5 class="mt-2">Taille totale</h5>
                        <h3 class="text-info">{{ number_format($stats['total_size_mb'], 2) }} MB</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des tables -->
        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-table"></i> Tables de la base de données
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="tablesTable">
                                <thead>
                                    <tr>
                                        <th>Nom de la table</th>
                                        <th class="text-end">Nombre de lignes</th>
                                        <th class="text-end">Taille (MB)</th>
                                        <th class="text-end">Pourcentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tables as $table)
                                    @php
                                    $percentage = $stats['total_size_mb'] > 0
                                    ? round(($table->size_mb / $stats['total_size_mb']) * 100, 2)
                                    : 0;
                                    @endphp
                                    <tr>
                                        <td>
                                            <i class="bi bi-table text-primary"></i>
                                            <strong>{{ $table->name }}</strong>
                                        </td>
                                        <td class="text-end">
                                            {{ number_format($table->rows, 0, ',', ' ') }}
                                        </td>
                                        <td class="text-end">
                                            <span class="badge bg-info">{{ number_format($table->size_mb, 2) }}
                                                MB</span>
                                        </td>
                                        <td class="text-end">
                                            <div class="progress" style="height: 20px; min-width: 100px;">
                                                <div class="progress-bar bg-{{ $percentage > 50 ? 'danger' : ($percentage > 20 ? 'warning' : 'success') }}"
                                                    role="progressbar" style="width: {{ $percentage }}%"
                                                    aria-valuenow="{{ $percentage }}" aria-valuemin="0"
                                                    aria-valuemax="100">
                                                    {{ $percentage }}%
                                                </div>
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

        <!-- Dernières migrations -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-arrow-repeat"></i> Dernières migrations
                        </h5>
                    </div>
                    <div class="card-body">
                        @if(count($migrations) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Migration</th>
                                        <th class="text-end">Batch</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($migrations as $migration)
                                    <tr>
                                        <td><span class="badge bg-secondary">#{{ $migration->id }}</span></td>
                                        <td>
                                            <i class="bi bi-file-earmark-code"></i>
                                            {{ $migration->migration }}
                                        </td>
                                        <td class="text-end">
                                            <span class="badge bg-primary">Batch {{ $migration->batch }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-4">
                            <i class="bi bi-info-circle display-4 text-muted"></i>
                            <p class="text-muted mt-2">Aucune migration trouvée</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Informations -->
        <div class="row mt-4">
            <div class="col-lg-12">
                <div class="alert alert-info">
                    <h5><i class="bi bi-info-circle"></i> Informations</h5>
                    <ul class="mb-0">
                        <li><strong>Optimisation :</strong> L'optimisation réorganise les données pour améliorer les
                            performances</li>
                        <li><strong>Sauvegardes :</strong> Créez régulièrement des sauvegardes depuis la section
                            correspondante</li>
                        <li><strong>Migrations :</strong> Les migrations permettent de versionner la structure de la
                            base de
                            données</li>
                        <li><strong>Attention :</strong> Ne modifiez jamais directement la structure de la base sans
                            migration</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection

@push('scripts')
<script>
    function optimizeDatabase() {
        Swal.fire({
            title: 'Optimiser la base de données ?',
            text: "Cette opération peut prendre quelques minutes selon la taille de votre base.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Oui, optimiser !',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Optimisation en cours...',
                    html: 'Veuillez patienter...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch('/system/database/optimize', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Succès',
                                text: data.message,
                                timer: 2500
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erreur',
                                text: data.message
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur',
                            text: 'Une erreur est survenue lors de l\'optimisation'
                        });
                    });
            }
        });
    }

    // DataTable pour les tables
    @if(count($tables) > 10)
    $(document).ready(function() {
        $('#tablesTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json'
            },
            order: [
                [2, 'desc']
            ], // Trier par taille décroissante
            pageLength: 25,
            dom: 'frtip'
        });
    });
    @endif
</script>
@endpush

@push('styles')
<style>
    .table td {
        vertical-align: middle;
    }

    .progress {
        margin: 0;
    }
</style>
@endpush