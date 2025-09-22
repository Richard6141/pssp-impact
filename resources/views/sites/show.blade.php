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
    </div><!-- End Page Title -->

    <section class="section profile">
        <div class="row">
            <div class="col-xl-8">

                <div class="card">
                    <div class="card-body pt-3">

                        <h5 class="card-title">Informations générales</h5>

                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Nom :</div>
                            <div class="col-sm-8">{{ $site->site_name }}</div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Département :</div>
                            <div class="col-sm-8">{{ $site->site_departement }}</div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Commune :</div>
                            <div class="col-sm-8">{{ $site->site_commune }}</div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Localisation :</div>
                            <div class="col-sm-8">{{ $site->localisation }}</div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Responsable :</div>
                            <div class="col-sm-8">
                                {{ $site->responsableUser ? $site->responsableUser->firstname . ' ' . $site->responsableUser->lastname : '—' }}
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <a href="{{ route('sites.edit', $site->site_id) }}" class="btn btn-warning">Modifier</a>
                            <a href="{{ route('sites.index') }}" class="btn btn-secondary">Retour</a>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

</main>
@endsection