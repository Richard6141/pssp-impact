@extends('layouts.back')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
        <h1>Ajouter un site</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                <li class="breadcrumb-item"><a href="{{ route('sites.index') }}">Sites</a></li>
                <li class="breadcrumb-item active">Nouveau</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="row">
            <div class="col-lg-12">

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Formulaire d’ajout d’un site</h5>

                        <!-- Floating Labels Form -->
                        <form class="row g-3" method="POST" action="{{ route('sites.store') }}">
                            @csrf

                            <div class="col-md-12">
                                <div class="form-floating">
                                    <input type="text" name="site_name" class="form-control" id="siteName"
                                        placeholder="Nom du site" required>
                                    <label for="siteName">Nom du site</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" name="site_departement" class="form-control" id="siteDepartement"
                                        placeholder="Département" required>
                                    <label for="siteDepartement">Département</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" name="site_commune" class="form-control" id="siteCommune"
                                        placeholder="Commune" required>
                                    <label for="siteCommune">Commune</label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="text" name="localisation" class="form-control" id="localisation"
                                        placeholder="Localisation" required>
                                    <label for="localisation">Localisation</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" name="longitude" class="form-control" id="longitude"
                                        placeholder="Longitude">
                                    <label for="longitude">Longitude</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" name="latitude" class="form-control" id="latitude"
                                        placeholder="Latitude">
                                    <label for="latitude">Latitude</label>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="responsable" name="responsable">
                                        <option value="">-- Aucun --</option>
                                        @foreach($users as $user)
                                        <option value="{{ $user->user_id }}">{{ $user->firstname }}
                                            {{ $user->lastname }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <label for="responsable">Responsable</label>
                                </div>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">Enregistrer</button>
                                <button type="reset" class="btn btn-secondary">Annuler</button>
                            </div>
                        </form><!-- End floating Labels Form -->

                    </div>
                </div>

            </div>
        </div>
    </section>

</main>
@endsection