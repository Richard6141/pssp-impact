@extends('layouts.back')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
        <h1>Nouvelle observation</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                <li class="breadcrumb-item"><a href="{{ route('observations.index') }}">Observations</a></li>
                <li class="breadcrumb-item active">Nouvelle</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Ajouter une observation</h5>

                        <form action="{{ route('observations.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="site_id" class="form-label">Site</label>
                                <select class="form-select" id="site_id" name="site_id" required>
                                    <option value="">-- Sélectionnez un site --</option>
                                    @foreach($sites as $site)
                                    <option value="{{ $site->site_id }}">{{ $site->site_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="contenu" class="form-label">Contenu</label>
                                <textarea class="form-control" id="contenu" name="contenu" rows="4"
                                    required>{{ old('contenu') }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label for="date_obs" class="form-label">Date de l'observation</label>
                                <input type="datetime-local" class="form-control" id="date_obs" name="date_obs"
                                    value="{{ old('date_obs', now()->format('Y-m-d\TH:i')) }}" required>
                            </div>

                            <div class="mt-3">
                                <a href="{{ route('observations.index') }}" class="btn btn-secondary">Annuler</a>
                                <button type="submit" class="btn btn-primary">Enregistrer</button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

</main>
@endsection