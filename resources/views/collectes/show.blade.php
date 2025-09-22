@extends('layouts.back')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
        <h1>Détails de la collecte</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                <li class="breadcrumb-item"><a href="{{ route('collectes.index') }}">Collectes</a></li>
                <li class="breadcrumb-item active">Détails</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Informations sur la collecte</h5>

                <ul class="list-group">
                    <li class="list-group-item"><strong>Date :</strong> {{ $collecte->date_collecte }}</li>
                    <li class="list-group-item"><strong>Poids :</strong> {{ $collecte->poids }} Kg</li>
                    <li class="list-group-item"><strong>Type de déchet :</strong>
                        {{ $collecte->typeDechet->libelle ?? '' }}
                    </li>
                    <li class="list-group-item"><strong>Agent :</strong> {{ $collecte->agent->firstname ?? '' }}
                        {{ $collecte->agent->lastname ?? '' }}
                    </li>
                    <li class="list-group-item"><strong>Site :</strong> {{ $collecte->site->site_name ?? '' }}</li>
                    <li class="list-group-item"><strong>Responsable signé :</strong>
                        {{ $collecte->signature_responsable_site ? 'Oui' : 'Non' }}
                    </li>
                    <li class="list-group-item"><strong>Validation :</strong> {{ $collecte->isValid ? 'Oui' : 'Non' }}
                    </li>
                </ul>

                <div class="mt-3">
                    <a href="{{ route('collectes.index') }}" class="btn btn-secondary">Retour à la liste</a>
                </div>
            </div>
        </div>
    </section>

</main>
@endsection