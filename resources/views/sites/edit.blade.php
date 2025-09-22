@extends('layouts.back')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
        <h1>Modifier un site</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                <li class="breadcrumb-item"><a href="{{ route('sites.index') }}">Sites</a></li>
                <li class="breadcrumb-item active">Modifier</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="row">
            <div class="col-lg-12">

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Formulaire de modification</h5>

                        <!-- Formulaire -->
                        <form class="row g-3" method="POST" action="{{ route('sites.update', $site->site_id) }}">
                            @csrf
                            @method('PUT')

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="site_name" id="siteName"
                                        value="{{ old('site_name', $site->site_name) }}" required>
                                    <label for="siteName">Nom du site</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="site_departement" id="siteDepartement"
                                        value="{{ old('site_departement', $site->site_departement) }}" required>
                                    <label for="siteDepartement">Département</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="site_commune" id="siteCommune"
                                        value="{{ old('site_commune', $site->site_commune) }}" required>
                                    <label for="siteCommune">Commune</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="localisation" id="localisation"
                                        value="{{ old('localisation', $site->localisation) }}">
                                    <label for="localisation">Localisation</label>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-floating">
                                    <select name="responsable_id" class="form-select" id="responsableSelect">
                                        <option value="">-- Sélectionner un responsable --</option>
                                        @foreach($users as $user)
                                        <option value="{{ $user->user_id }}"
                                            {{ $site->responsable_id == $user->user_id ? 'selected' : '' }}>
                                            {{ $user->firstname }} {{ $user->lastname }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <label for="responsableSelect">Responsable</label>
                                </div>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">Mettre à jour</button>
                                <a href="{{ route('sites.index') }}" class="btn btn-secondary">Annuler</a>
                            </div>
                        </form><!-- End floating Labels Form -->

                    </div>
                </div>

            </div>
        </div>
    </section>

</main>
@endsection