@extends('layouts.back')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
        <h1>Modifier l'observation</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                <li class="breadcrumb-item"><a href="{{ route('observations.index') }}">Observations</a></li>
                <li class="breadcrumb-item active">Modifier</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Observation #{{ $observation->observation_id }}</h5>

                        <form action="{{ route('observations.update', $observation) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="site_id" class="form-label">Site</label>
                                <select class="form-select" id="site_id" name="site_id" required>
                                    <option value="">-- Sélectionnez un site --</option>
                                    @foreach($sites as $site)
                                    <option value="{{ $site->site_id }}" @if($site->site_id == $observation->site_id)
                                        selected @endif>
                                        {{ $site->site_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="contenu" class="form-label">Contenu</label>
                                <textarea class="form-control" id="contenu" name="contenu" rows="4"
                                    required>{{ old('contenu', $observation->contenu) }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label for="date_obs" class="form-label">Date de l'observation</label>
                                <input type="datetime-local" class="form-control" id="date_obs" name="date_obs"
                                    value="{{ old('date_obs', \Carbon\Carbon::parse($observation->date_obs)->format('Y-m-d\TH:i')) }}"
                                    required>
                            </div>

                            <div class="mt-3">
                                <a href="{{ route('observations.index') }}" class="btn btn-secondary">Annuler</a>
                                <button type="submit" class="btn btn-primary">Mettre à jour</button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection