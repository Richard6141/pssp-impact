@extends('layouts.back')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
        <h1>Détails du site</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                <li class="breadcrumb-item"><a href="{{ route('sites.index') }}">Sites</a></li>
                <li class="breadcrumb-item active">{{ $site->site_name }}</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-10 mx-auto">

                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title m-0">{{ $site->site_name }}</h5>
                            <div class="d-flex gap-2">
                                @can('sites.update')
                                <a href="{{ route('sites.edit', $site->site_id) }}"
                                    class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil me-1"></i> Modifier
                                </a>
                                @endcan
                                <a href="{{ route('sites.index') }}" class="btn btn-sm btn-secondary">
                                    <i class="bi bi-arrow-left me-1"></i> Retour
                                </a>
                            </div>
                        </div>

                        <div class="row g-3">
                            <!-- Informations générales -->
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2 mb-3">
                                    <i class="bi bi-info-circle me-2"></i>Informations générales
                                </h6>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small">Nom du site</label>
                                    <p class="fw-bold mb-0">{{ $site->site_name }}</p>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small">Département</label>
                                    <p class="mb-0">{{ $site->site_departement }}</p>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small">Commune</label>
                                    <p class="mb-0">{{ $site->site_commune }}</p>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small">Localisation</label>
                                    <p class="mb-0">{{ $site->localisation }}</p>
                                </div>
                            </div>

                            <!-- Coordonnées géographiques -->
                            @if($site->longitude && $site->latitude)
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2 mb-3 mt-3">
                                    <i class="bi bi-geo-alt me-2"></i>Coordonnées GPS
                                </h6>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="text-muted small">Latitude</label>
                                    <p class="mb-0 fw-bold">{{ $site->latitude }}</p>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="text-muted small">Longitude</label>
                                    <p class="mb-0 fw-bold">{{ $site->longitude }}</p>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="text-muted small">Navigation</label>
                                    <div>
                                        <a href="https://www.google.com/maps?q={{ $site->latitude }},{{ $site->longitude }}"
                                            target="_blank"
                                            class="btn btn-sm btn-primary">
                                            <i class="bi bi-map me-1"></i> Ouvrir dans Google Maps
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="alert alert-info mb-0">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>Itinéraire :</strong> Cliquez sur "Ouvrir dans Google Maps" pour obtenir les directions
                                    vers ce site depuis votre position actuelle.
                                </div>
                            </div>
                            @else
                            <div class="col-12">
                                <div class="alert alert-warning mb-0">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    Les coordonnées GPS de ce site ne sont pas encore enregistrées.
                                    <a href="{{ route('sites.edit', $site->site_id) }}" class="alert-link">Modifier le site</a>
                                    pour ajouter les coordonnées.
                                </div>
                            </div>
                            @endif

                            <!-- Responsable -->
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2 mb-3 mt-3">
                                    <i class="bi bi-person-badge me-2"></i>Responsable
                                </h6>
                            </div>

                            <div class="col-12">
                                @if($site->responsableUser)
                                <div class="card border-start border-4 border-info">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <div
                                                    class="bg-info bg-opacity-10 rounded-circle p-3 text-info">
                                                    <i class="bi bi-person-fill fs-4"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6 class="mb-1">
                                                    {{ $site->responsableUser->firstname }}
                                                    {{ $site->responsableUser->lastname }}
                                                </h6>
                                                <p class="text-muted mb-1">
                                                    <i
                                                        class="bi bi-envelope me-1"></i>{{ $site->responsableUser->email }}
                                                </p>
                                                <p class="text-muted mb-0 small">
                                                    <i
                                                        class="bi bi-person-circle me-1"></i>{{ $site->responsableUser->username }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @else
                                <div class="alert alert-secondary mb-0">
                                    <i class="bi bi-info-circle me-2"></i>Aucun responsable assigné à ce
                                    site
                                </div>
                                @endif
                            </div>

                            <!-- Métadonnées -->
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2 mb-3 mt-4">
                                    <i class="bi bi-clock-history me-2"></i>Historique
                                </h6>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small">Date de création</label>
                                    <p class="mb-0">{{ $site->created_at->format('d/m/Y à H:i') }}</p>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small">Dernière modification</label>
                                    <p class="mb-0">{{ $site->updated_at->format('d/m/Y à H:i') }}</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

</main>

<style>
    .card-body label.text-muted {
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
        display: block;
    }

    .border-4 {
        border-width: 4px !important;
    }
</style>
@endsection