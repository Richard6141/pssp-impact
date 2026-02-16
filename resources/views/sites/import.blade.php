@extends('layouts.back')

@section('content')
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Import Sites</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('sites.index') }}">Sites</a></li>
                <li class="breadcrumb-item active">Import</li>
            </ol>
        </nav>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        {{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <section class="section">
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Importer des sites (Excel/CSV)</h5>
                        <p>Le client peut définir lui-même les codes de site dans le fichier importé.</p>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-1"></i>
                            Colonnes attendues:
                            <code>site_code,site_name,site_departement,site_commune,localisation,latitude,longitude,responsable_email</code>
                        </div>

                        <form action="{{ route('sites.import.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="file" class="form-label">Fichier .xlsx ou .csv</label>
                                <input class="form-control @error('file') is-invalid @enderror" type="file" id="file" name="file" accept=".xlsx,.csv,.txt" required>
                                @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Taille max: 5 Mo</div>
                            </div>

                            <div class="mb-3">
                                <a href="{{ route('sites.import.template') }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-download"></i> Télécharger le modèle CSV
                                </a>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-upload"></i> Lancer l'import
                                </button>
                                <a href="{{ route('sites.index') }}" class="btn btn-light">Retour</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Contrôles appliqués</h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">Le <strong>site_code</strong> est obligatoire et unique.</li>
                            <li class="list-group-item">Les champs nom, département, commune et localisation sont obligatoires.</li>
                            <li class="list-group-item">Le responsable est optionnel (email ou username).</li>
                            <li class="list-group-item">Les lignes invalides sont ignorées et listées en erreur.</li>
                        </ul>

                        @if(session('import_errors') && count(session('import_errors')) > 0)
                        <div class="mt-3">
                            <h6>Erreurs d'import</h6>
                            <div class="alert alert-danger mb-0" style="max-height: 260px; overflow:auto;">
                                <ul class="mb-0 ps-3">
                                    @foreach(session('import_errors') as $lineError)
                                    <li>{{ $lineError }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection
