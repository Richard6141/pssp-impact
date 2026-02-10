@extends('layouts.back')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
        <h1>Import Utilisateurs</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Utilisateurs</a></li>
                <li class="breadcrumb-item active">Importer</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
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
                        <h5 class="card-title">Importer des utilisateurs (CSV)</h5>
                        <p>Importez massivement des utilisateurs à partir d'un fichier CSV. Le mot de passe sera généré aléatoirement si non fourni.</p>
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-1"></i> Format attendu : <code>firstname,lastname,username,email,role,site_name,phone</code>
                        </div>

                        <form action="{{ route('admin.users.import.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <div class="mb-3">
                                <label for="file" class="form-label">Fichier CSV</label>
                                <input class="form-control" type="file" id="file" name="file" accept=".csv" required>
                                <div class="form-text">Taille max : 2Mo. Encodage UTF-8 recommandé.</div>
                            </div>

                            <div class="mb-3">
                                <a href="{{ route('admin.users.import.template') }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-download"></i> Télécharger le modèle CSV
                                </a>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-upload"></i> Lancer l'importation
                                </button>
                                <a href="{{ route('users.index') }}" class="btn btn-light">Annuler</a>
                            </div>
                        </form>

                    </div>
                </div>

            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Instructions</h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">1. Les champs <strong>email</strong> et <strong>username</strong> doivent être uniques.</li>
                            <li class="list-group-item">2. Le champ <strong>role</strong> doit correspondre exactement au nom d'un rôle existant (ex: 'Agent collecte').</li>
                            <li class="list-group-item">3. Le champ <strong>site_name</strong> est optionnel. S'il est fourni, il doit correspondre au nom exact d'un site.</li>
                            <li class="list-group-item">4. Les mots de passe seront générés automatiquement si la colonne n'est pas présente.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

</main><!-- End #main -->
@endsection
