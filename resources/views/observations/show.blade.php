@extends('layouts.back')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
        <h1>Détails de l'observation</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                <li class="breadcrumb-item"><a href="{{ route('observations.index') }}">Observations</a></li>
                <li class="breadcrumb-item active">Détails</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Observation #{{ $observation->observation_id }}</h5>

                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><strong>Site :</strong>
                                {{ $observation->site?->site_name ?? '—' }}
                            </li>
                            <li class="list-group-item"><strong>Utilisateur :</strong>
                                {{ $observation->user?->firstname }} {{ $observation->user?->lastname }}
                            </li>
                            <li class="list-group-item"><strong>Contenu :</strong> {{ $observation->contenu }}</li>
                            <li class="list-group-item"><strong>Date :</strong>
                                {{ \Carbon\Carbon::parse($observation->date_obs)->format('d/m/Y H:i') }}
                            </li>
                        </ul>

                        <div class="mt-3">
                            <a href="{{ route('observations.index') }}" class="btn btn-secondary">Retour</a>
                            <a href="{{ route('observations.edit', $observation) }}" class="btn btn-warning">Éditer</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection