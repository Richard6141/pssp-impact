@extends('layouts.back')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
        <h1>{{ isset($facture) ? 'Modifier' : 'Ajouter' }} une facture</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                <li class="breadcrumb-item"><a href="{{ route('factures.index') }}">Factures</a></li>
                <li class="breadcrumb-item active">{{ isset($facture) ? 'Modifier' : 'Ajouter' }}</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">{{ isset($facture) ? 'Édition' : 'Création' }} de facture</h5>

                        <form class="row g-3" method="POST" enctype="multipart/form-data"
                            action="{{ isset($facture) ? route('factures.update', $facture->facture_id) : route('factures.store') }}">
                            @csrf
                            @if(isset($facture)) @method('PUT') @endif

                            {{-- Date --}}
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="date" class="form-control" name="date_facture" id="dateFacture"
                                        value="{{ old('date_facture', $facture->date_facture ?? '') }}" required>
                                    <label for="dateFacture">Date de facture</label>
                                </div>
                                @error('date_facture')
                                <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Montant --}}
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="number" step="0.01" class="form-control" name="montant_facture"
                                        id="montant"
                                        value="{{ old('montant_facture', $facture->montant_facture ?? '') }}" required>
                                    <label for="montant">Montant</label>
                                </div>
                                @error('montant_facture')
                                <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Site --}}
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select" name="site_id" id="siteSelect" required>
                                        <option value="">-- Choisir un site --</option>
                                        @foreach($sites as $site)
                                        <option value="{{ $site->site_id }}"
                                            {{ old('site_id', $facture->site_id ?? '') == $site->site_id ? 'selected' : '' }}>
                                            {{ $site->site_name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <label for="siteSelect">Site</label>
                                </div>
                                @error('site_id')
                                <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Photo facture --}}
                            <div class="col-md-12">
                                <label for="photo_facture" class="form-label">Photo ou PDF de la facture</label>
                                <input class="form-control" type="file" name="photo_facture" id="photo_facture">
                                @error('photo_facture')
                                <div class="text-danger small">{{ $message }}</div>
                                @enderror

                                @if(isset($facture) && $facture->photo_facture)
                                <div class="mt-2">
                                    @if(Str::endsWith($facture->photo_facture, ['.jpg','.jpeg','.png','.gif']))
                                    <img src="{{ Storage::url($facture->photo_facture) }}" alt="Photo facture"
                                        class="img-thumbnail" style="height:80px; cursor:pointer"
                                        onclick="openModal('{{ Storage::url($facture->photo_facture) }}')">
                                    @else
                                    <a href="{{ Storage::url($facture->photo_facture) }}" target="_blank"
                                        class="btn btn-sm btn-outline-primary">
                                        Voir le PDF
                                    </a>
                                    @endif
                                </div>
                                @endif
                            </div>

                            {{-- Collectes --}}
                            <div class="col-md-12">
                                <label class="form-label">Collectes disponibles</label>
                                <div id="collectesContainer" class="border rounded p-3"
                                    style="max-height: 250px; overflow-y:auto;">
                                    @if(isset($site_id) && $collectes->count() > 0)
                                    @foreach($collectes as $collecte)
                                    <div class="form-check mb-1">
                                        <input class="form-check-input" type="checkbox" name="collecte_ids[]"
                                            id="collecte_{{ $collecte->collecte_id }}"
                                            value="{{ $collecte->collecte_id }}"
                                            {{ isset($facture) && $facture->collectes->pluck('collecte_id')->contains($collecte->collecte_id) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="collecte_{{ $collecte->collecte_id }}">
                                            <strong>{{ \Carbon\Carbon::parse($collecte->date_collecte)->format('d/m/Y H:i') }}</strong>
                                            —
                                            {{ $collecte->typeDechet?->libelle ?? 'Type inconnu' }} —
                                            {{ $collecte->poids }} Kg
                                        </label>
                                    </div>
                                    @endforeach
                                    @else
                                    <p class="text-muted">Sélectionnez un site pour voir les collectes disponibles.</p>
                                    @endif
                                </div>
                                @error('collecte_ids')
                                <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Boutons --}}
                            <div class="text-center mt-3">
                                <button type="submit" class="btn btn-primary">
                                    {{ isset($facture) ? 'Mettre à jour' : 'Ajouter' }}
                                </button>
                                <a href="{{ route('factures.index') }}" class="btn btn-secondary">Annuler</a>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </section>
</main>

{{-- Modal pour l'aperçu d'image --}}
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-body text-center">
                <img id="modalImage" src="" class="img-fluid rounded">
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Ouvrir modal pour afficher image
    function openModal(url) {
        document.getElementById('modalImage').src = url;
        new bootstrap.Modal(document.getElementById('imageModal')).show();
    }

    // Charger collectes dynamiquement selon le site
    document.getElementById('siteSelect').addEventListener('change', function() {
        let siteId = this.value;
        let container = document.getElementById('collectesContainer');

        // Afficher un message de chargement
        container.innerHTML =
            '<div class="d-flex align-items-center"><div class="spinner-border spinner-border-sm me-2" role="status"></div><span class="text-muted">Chargement des collectes...</span></div>';

        if (siteId) {
            // Utiliser la route correcte définie dans web.php
            fetch(`{{ url('factures/collectes-by-site') }}/${siteId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur réseau');
                    }
                    return response.json();
                })
                .then(collectes => {
                    if (collectes.length > 0) {
                        container.innerHTML = '';
                        collectes.forEach(collecte => {
                            let div = document.createElement('div');
                            div.classList.add('form-check', 'mb-1');
                            div.innerHTML = `
                                <input class="form-check-input" type="checkbox" 
                                       name="collecte_ids[]" 
                                       value="${collecte.collecte_id}" 
                                       id="collecte_${collecte.collecte_id}">
                                <label class="form-check-label" for="collecte_${collecte.collecte_id}">
                                    <strong>${collecte.date_collecte}</strong> — 
                                    ${collecte.type_dechet} — 
                                    ${collecte.poids} Kg
                                </label>
                            `;
                            container.appendChild(div);
                        });
                    } else {
                        container.innerHTML =
                            '<p class="text-muted"><i class="bi bi-info-circle me-1"></i>Aucune collecte disponible pour ce site.</p>';
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    container.innerHTML =
                        '<p class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Erreur lors du chargement des collectes.</p>';
                });
        } else {
            container.innerHTML =
                '<p class="text-muted">Sélectionnez un site pour voir les collectes disponibles.</p>';
        }
    });

    // Charger les collectes au chargement de la page si un site est déjà sélectionné
    document.addEventListener('DOMContentLoaded', function() {
        let siteSelect = document.getElementById('siteSelect');
        if (siteSelect.value) {
            siteSelect.dispatchEvent(new Event('change'));
        }
    });
</script>
@endsection