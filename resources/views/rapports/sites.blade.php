@extends('layouts.back')

@section('title', 'Rapport par Sites')

@push('styles')
<style>
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
    .sites-card .card-icon { background: linear-gradient(90deg, #4154f1, #677de9); }
    .active-card .card-icon { background: linear-gradient(90deg, #2eca6a, #56d477); }
    .weight-card .card-icon { background: linear-gradient(90deg, #ff771d, #ff9447); }
    .incidents-card .card-icon { background: linear-gradient(90deg, #ff5757, #ff7b7b); }
</style>
@endpush

@section('content')
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Rapport par Sites</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bi bi-house"></i> Accueil</a></li>
                <li class="breadcrumb-item">Rapports</li>
                <li class="breadcrumb-item active">Sites</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        <!-- Filtres -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Filtres</h5>
                <form method="GET" action="{{ route('rapports.sites') }}">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="date_debut" class="form-label">Date début</label>
                            <input type="date" class="form-control" id="date_debut" name="date_debut" value="{{ $dateDebut }}">
                        </div>
                        <div class="col-md-3">
                            <label for="date_fin" class="form-label">Date fin</label>
                            <input type="date" class="form-control" id="date_fin" name="date_fin" value="{{ $dateFin }}">
                        </div>
                        <div class="col-md-3 align-self-end">
                            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Filtrer</button>
                        </div>
                        <div class="col-md-3 align-self-end">
                            @can('rapports.export')
                            <button type="button" class="btn btn-success w-100" onclick="exportPDF()">
                                <i class="bi bi-file-earmark-pdf"></i> Exporter PDF
                            </button>
                            @endcan
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Stats Générales -->
        <div class="row">
            <div class="col-xxl-3 col-md-6">
                <div class="card info-card sites-card">
                    <div class="card-body">
                        <h5 class="card-title">Total Sites</h5>
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-geo-alt"></i>
                            </div>
                            <div class="ps-3">
                                <h6>{{ $statsGenerales['total_sites'] }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-3 col-md-6">
                <div class="card info-card active-card">
                    <div class="card-body">
                        <h5 class="card-title">Sites Actifs <span>| Période</span></h5>
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <div class="ps-3">
                                <h6>{{ $statsGenerales['sites_actifs'] }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-3 col-md-6">
                <div class="card info-card weight-card">
                    <div class="card-body">
                        <h5 class="card-title">Moyenne Poids/Site</h5>
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-speedometer2"></i>
                            </div>
                            <div class="ps-3">
                                <h6>{{ \App\Models\Collecte::formatPoids($statsGenerales['moyenne_poids_par_site']) }} kg
                                </h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-3 col-md-6">
                <div class="card info-card incidents-card">
                    <div class="card-body">
                        <h5 class="card-title">Incidents</h5>
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-exclamation-triangle"></i>
                            </div>
                            <div class="ps-3">
                                <h6>{{ count($sitesAvecIncidents) }} sites</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <!-- Liste des Sites -->
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Performance par Site</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Site</th>
                                        <th>Département</th>
                                        <th class="text-center">Collectes (Période)</th>
                                        <th class="text-end">Poids Total (kg)</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sites as $site)
                                    <tr>
                                        <td>{{ $site->site_name }}</td>
                                        <td>{{ $site->site_departement }}</td>
                                        <td class="text-center">{{ $site->collectes_count }}</td>
                                        <td class="text-end fw-bold">{{ \App\Models\Collecte::formatPoids($site->collectes_sum_poids ?? 0) }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('sites.show', $site->site_id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-3">
                            {{ $sites->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </section>
</main>
@endsection

@push('scripts')
<script>
    function exportPDF() {
        console.log('Exportation PDF lancée...');
        const params = new URLSearchParams(window.location.search);
        const url = "{{ route('rapports.export.pdf', ['type' => 'sites']) }}?" + params.toString();
        window.location.href = url;
    }
</script>
@endpush
