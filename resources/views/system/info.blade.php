@extends('layouts.back')

@section('title', 'Informations Système')

@section('content')
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Informations Système</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
                <li class="breadcrumb-item">Système</li>
                <li class="breadcrumb-item active">Informations</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <!-- Actions rapides -->
        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Actions rapides</h5>
                        <button type="button" class="btn btn-primary" onclick="clearCache()">
                            <i class="bi bi-trash"></i> Vider les caches
                        </button>
                        <button type="button" class="btn btn-info" onclick="location.reload()">
                            <i class="bi bi-arrow-clockwise"></i> Actualiser
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informations PHP et Laravel -->
        <div class="row mb-4">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-code-square"></i> Informations PHP
                        </h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tbody>
                                <tr>
                                    <td><strong>Version PHP</strong></td>
                                    <td>{{ $systemInfo['php_version'] }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Système d'exploitation</strong></td>
                                    <td>{{ $systemInfo['os'] }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Serveur Web</strong></td>
                                    <td>{{ $systemInfo['server_software'] }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Limite mémoire</strong></td>
                                    <td><span class="badge bg-info">{{ $systemInfo['memory_limit'] }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Temps d'exécution max</strong></td>
                                    <td><span class="badge bg-info">{{ $systemInfo['max_execution_time'] }}s</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Taille max upload</strong></td>
                                    <td><span class="badge bg-info">{{ $systemInfo['upload_max_filesize'] }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Taille max POST</strong></td>
                                    <td><span class="badge bg-info">{{ $systemInfo['post_max_size'] }}</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-stack"></i> Informations Laravel
                        </h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tbody>
                                <tr>
                                    <td><strong>Version Laravel</strong></td>
                                    <td>{{ $systemInfo['laravel_version'] }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Environnement</strong></td>
                                    <td>
                                        <span
                                            class="badge bg-{{ config('app.env') === 'production' ? 'success' : 'warning' }}">
                                            {{ strtoupper(config('app.env')) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Mode Debug</strong></td>
                                    <td>
                                        <span class="badge bg-{{ config('app.debug') ? 'warning' : 'success' }}">
                                            {{ config('app.debug') ? 'Activé' : 'Désactivé' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>URL de l'application</strong></td>
                                    <td><small>{{ config('app.url') }}</small></td>
                                </tr>
                                <tr>
                                    <td><strong>Fuseau horaire</strong></td>
                                    <td>{{ config('app.timezone') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Locale</strong></td>
                                    <td>{{ config('app.locale') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Base de données et Cache -->
        <div class="row mb-4">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-database"></i> Base de données
                        </h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tbody>
                                <tr>
                                    <td><strong>Type de connexion</strong></td>
                                    <td><span class="badge bg-success">{{ strtoupper($dbInfo['connection']) }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Hôte</strong></td>
                                    <td>{{ $dbInfo['host'] }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Base de données</strong></td>
                                    <td>{{ $dbInfo['database'] }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Version</strong></td>
                                    <td>{{ $dbInfo['version'] }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <a href="{{ route('system.database') }}" class="btn btn-sm btn-success">
                            <i class="bi bi-eye"></i> Voir les détails
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-lightning"></i> Cache
                        </h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tbody>
                                <tr>
                                    <td><strong>Driver par défaut</strong></td>
                                    <td><span
                                            class="badge bg-warning text-dark">{{ strtoupper($cacheInfo['driver']) }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Stores disponibles</strong></td>
                                    <td>
                                        @foreach($cacheInfo['stores'] as $store)
                                        <span class="badge bg-secondary">{{ $store }}</span>
                                        @endforeach
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-sm btn-warning" onclick="clearCache()">
                            <i class="bi bi-trash"></i> Vider le cache
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stockage -->
        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-hdd"></i> Espace disque
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Espace total</h6>
                                <h3 class="text-primary">{{ $systemInfo['disk_total_space'] }}</h3>
                            </div>
                            <div class="col-md-6">
                                <h6>Espace libre</h6>
                                <h3 class="text-success">{{ $systemInfo['disk_free_space'] }}</h3>
                            </div>
                        </div>
                        @php
                        $totalBytes = disk_total_space('/');
                        $freeBytes = disk_free_space('/');
                        $usedPercent = round((($totalBytes - $freeBytes) / $totalBytes) * 100, 2);
                        @endphp
                        <div class="progress mt-3" style="height: 30px;">
                            <div class="progress-bar bg-{{ $usedPercent > 90 ? 'danger' : ($usedPercent > 75 ? 'warning' : 'success') }}"
                                role="progressbar" style="width: {{ $usedPercent }}%" aria-valuenow="{{ $usedPercent }}"
                                aria-valuemin="0" aria-valuemax="100">
                                {{ $usedPercent }}% utilisé
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Extensions PHP -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-plugin"></i> Extensions PHP chargées ({{ count($extensions) }})
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($extensions as $extension)
                            <div class="col-md-3 col-sm-4 col-6 mb-2">
                                <span class="badge bg-light text-dark border">
                                    <i class="bi bi-check-circle text-success"></i> {{ $extension }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Avertissements -->
        @if(config('app.debug') && config('app.env') === 'production')
        <div class="row mt-4">
            <div class="col-lg-12">
                <div class="alert alert-danger">
                    <h5><i class="bi bi-exclamation-triangle"></i> Avertissement de sécurité</h5>
                    <p class="mb-0">Le mode debug est activé en production ! Cela peut exposer des informations
                        sensibles.
                        Veuillez le désactiver dans le fichier .env (APP_DEBUG=false).</p>
                </div>
            </div>
        </div>
        @endif
    </section>
</main>
@endsection

@push('scripts')
<script>
    function clearCache() {
        Swal.fire({
            title: 'Vider les caches ?',
            text: "Cette action videra tous les caches de l'application.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui, vider !',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Traitement en cours...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch('/system/cache/clear', {
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
                                timer: 2000
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
                            text: 'Une erreur est survenue'
                        });
                    });
            }
        });
    }
</script>
@endpush

@push('styles')
<style>
    .table td {
        vertical-align: middle;
    }

    .card-header {
        padding: 1rem;
    }

    .badge {
        font-size: 0.85rem;
        padding: 0.5rem 0.75rem;
        margin: 0.2rem;
    }
</style>
@endpush