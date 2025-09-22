@extends('layouts.back')

@section('content')
<main id="main" class="main">

    <div class="pagetitle text-center">
        <h1>{{ isset($collecte) ? 'Modifier la collecte' : 'Nouvelle collecte' }}</h1>
        <nav>
            <ol class="breadcrumb justify-content-center">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                <li class="breadcrumb-item"><a href="{{ route('collectes.index') }}">Collectes</a></li>
                <li class="breadcrumb-item active">{{ isset($collecte) ? 'Modifier' : 'Créer' }}</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <section class="section d-flex justify-content-center">
        <div class="col-lg-8">

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title text-center">
                        {{ isset($collecte) ? 'Formulaire de modification' : 'Formulaire de collecte' }}
                    </h5>

                    <form class="row g-3" method="POST"
                        action="{{ isset($collecte) ? route('collectes.update', $collecte->collecte_id) : route('collectes.store') }}">
                        @csrf
                        @if(isset($collecte))
                        @method('PUT')
                        @endif

                        {{-- Date et poids --}}
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="datetime-local" class="form-control" name="date_collecte" id="dateCollecte"
                                    value="{{ old('date_collecte', isset($collecte) ? date('Y-m-d\TH:i', strtotime($collecte->date_collecte)) : '') }}"
                                    required>
                                <label for="dateCollecte">Date et heure de collecte</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="number" step="0.01" class="form-control" name="poids" id="poids"
                                    value="{{ old('poids', isset($collecte) ? $collecte->poids : '') }}" required>
                                <label for="poids">Poids (Kg)</label>
                            </div>
                        </div>

                        {{-- Type de déchet et agent --}}
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select class="form-select" name="type_dechet_id" id="typeDechetSelect" required>
                                    <option value="">-- Choisir un type de déchet --</option>
                                    @foreach($types as $type)
                                    <option value="{{ $type->type_dechet_id }}"
                                        {{ old('type_dechet_id', isset($collecte) ? $collecte->type_dechet_id : '') == $type->type_dechet_id ? 'selected' : '' }}>
                                        {{ $type->libelle }}
                                    </option>
                                    @endforeach
                                </select>
                                <label for="typeDechetSelect">Type de déchet</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <select class="form-select" name="site_id" id="siteSelect" required>
                                    <option value="">-- Choisir un site --</option>
                                    @foreach($sites as $site)
                                    <option value="{{ $site->site_id }}"
                                        {{ old('site_id', isset($collecte) ? $collecte->site_id : '') == $site->site_id ? 'selected' : '' }}>
                                        {{ $site->site_name }}
                                    </option>
                                    @endforeach
                                </select>
                                <label for="agentSelect">Site de collecte</label>
                            </div>
                        </div>

                        {{-- Signatures --}}
                        <!-- <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="signature_responsable_site"
                                    id="signatureSite" value="1"
                                    {{ old('signature_responsable_site', isset($collecte) ? $collecte->signature_responsable_site : false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="signatureSite">
                                    Signature responsable du site
                                </label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="isValid" id="isValid" value="1"
                                    {{ old('isValid', isset($collecte) ? $collecte->isValid : false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="isValid">
                                    Validation de la collecte
                                </label>
                            </div>
                        </div> -->

                        {{-- Incident toggle --}}
                        <div class="col-md-12 mt-3">
                            <label class="form-label">Voulez-vous enregistrer un incident ?</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="has_incident" id="incident_yes"
                                    value="1" onclick="toggleIncident()"
                                    {{ old('has_incident', isset($collecte) && $collecte->incident ? '1' : '0') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="incident_yes">Oui</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="has_incident" id="incident_no"
                                    value="0" onclick="toggleIncident()"
                                    {{ old('has_incident', isset($collecte) && $collecte->incident ? '1' : '0') == '0' ? 'checked' : '' }}>
                                <label class="form-check-label" for="incident_no">Non</label>
                            </div>
                        </div>

                        {{-- Champs incident --}}
                        <div id="incidentFields" class="col-md-12 mt-2"
                            style="display: {{ old('has_incident', isset($collecte) && $collecte->incident ? '1' : '0') == '1' ? 'block' : 'none' }}; border:1px solid #ddd; padding:15px; border-radius:5px;">
                            <h6>Informations sur l'incident</h6>
                            <div class="mb-3">
                                <label for="incident_description" class="form-label">Description</label>
                                <textarea class="form-control" name="incident_description"
                                    id="incident_description">{{ old('incident_description', isset($collecte) && $collecte->incident ? $collecte->incident->description : '') }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label for="incident_date" class="form-label">Date de l'incident</label>
                                <input type="datetime-local" class="form-control" name="incident_date"
                                    id="incident_date"
                                    value="{{ old('incident_date', isset($collecte) && $collecte->incident ? date('Y-m-d\TH:i', strtotime($collecte->incident->incident_date)) : '') }}">
                            </div>
                        </div>

                        {{-- Script directement dans le HTML --}}
                        <script>
                            function toggleIncident() {
                                console.log('toggleIncident called!');
                                var incidentYes = document.getElementById('incident_yes');
                                var incidentFields = document.getElementById('incidentFields');

                                if (incidentYes && incidentFields) {
                                    if (incidentYes.checked) {
                                        incidentFields.style.display = 'block';
                                        console.log('Showing incident fields');
                                    } else {
                                        incidentFields.style.display = 'none';
                                        console.log('Hiding incident fields');
                                    }
                                } else {
                                    console.log('Elements not found!');
                                }
                            }
                            console.log('Script loaded in HTML!');

                            // Appel initial pour s'assurer que l'affichage est correct au chargement
                            document.addEventListener('DOMContentLoaded', function() {
                                toggleIncident();
                            });
                        </script>

                        {{-- Boutons --}}
                        <div class="text-center mt-3">
                            <button type="submit" class="btn btn-primary">
                                {{ isset($collecte) ? 'Modifier' : 'Enregistrer' }}
                            </button>
                            <a href="{{ route('collectes.index') }}" class="btn btn-secondary">Annuler</a>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </section>

</main>
@endsection