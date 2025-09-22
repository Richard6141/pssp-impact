@extends('layouts.back')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
        <h1>Modifier la collecte</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                <li class="breadcrumb-item"><a href="{{ route('collectes.index') }}">Collectes</a></li>
                <li class="breadcrumb-item active">Modifier</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="row">
            <div class="col-lg-12">

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Formulaire d’édition</h5>

                        <!-- Formulaire -->
                        <form class="row g-3" method="POST"
                            action="{{ route('collectes.update', $collecte->collecte_id) }}">
                            @csrf
                            @method('PUT')

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="datetime-local" class="form-control" name="date_collecte"
                                        id="dateCollecte"
                                        value="{{ old('date_collecte', \Carbon\Carbon::parse($collecte->date_collecte)->format('Y-m-d\TH:i')) }}"
                                        required>
                                    <label for="dateCollecte">Date et heure de collecte</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="number" step="0.01" class="form-control" name="poids" id="poids"
                                        value="{{ old('poids', $collecte->poids) }}" required>
                                    <label for="poids">Poids (Kg)</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select" name="type_dechet_id" id="typeDechetSelect" required>
                                        <option value="">-- Choisir un type de déchet --</option>
                                        @foreach($types as $type)
                                        <option value="{{ $type->type_dechet_id }}"
                                            {{ old('type_dechet_id', $collecte->type_dechet_id) == $type->type_dechet_id ? 'selected' : '' }}>
                                            {{ $type->libelle }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <label for="typeDechetSelect">Type de déchet</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select" name="agent_id" id="agentSelect" required>
                                        <option value="{{ $collecte->agent->user_id }}" selected>
                                            {{ $collecte->agent->firstname }} {{ $collecte->agent->lastname }}
                                        </option>
                                    </select>
                                    <label for="agentSelect">Agent collecteur</label>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-floating">
                                    <select class="form-select" name="site_id" id="siteSelect" required>
                                        <option value="">-- Choisir un site --</option>
                                        @foreach($sites as $site)
                                        <option value="{{ $site->site_id }}"
                                            {{ old('site_id', $collecte->site_id) == $site->site_id ? 'selected' : '' }}>
                                            {{ $site->site_name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <label for="siteSelect">Site</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="signature_responsable_site"
                                        id="signatureSite" value="1"
                                        {{ old('signature_responsable_site', $collecte->signature_responsable_site) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="signatureSite">
                                        Signature responsable du site
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="isValid" id="isValid"
                                        value="1" {{ old('isValid', $collecte->isValid) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="isValid">
                                        Validation de la collecte
                                    </label>
                                </div>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">Mettre à jour</button>
                                <a href="{{ route('collectes.index') }}" class="btn btn-secondary">Annuler</a>
                            </div>
                        </form><!-- End floating Labels Form -->

                    </div>
                </div>

            </div>
        </div>
    </section>

</main>
@endsection