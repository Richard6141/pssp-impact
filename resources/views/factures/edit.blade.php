@extends('layouts.back')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
        <h1>Modifier une facture</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                <li class="breadcrumb-item"><a href="{{ route('factures.index') }}">Factures</a></li>
                <li class="breadcrumb-item active">Modifier</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-9">

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Édition de la facture</h5>

                        <form class="row g-3" method="POST" enctype="multipart/form-data"
                            action="{{ route('factures.update', $facture->facture_id) }}">
                            @csrf
                            @method('PUT')

                            {{-- Date --}}
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="date" class="form-control" name="date_facture" id="dateFacture"
                                        value="{{ old('date_facture', $facture->date_facture) }}" required>
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
                                        id="montant" value="{{ old('montant_facture', $facture->montant_facture) }}"
                                        required>
                                    <label for="montant">Montant</label>
                                </div>
                                @error('montant_facture')
                                <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Statut --}}
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select" name="statut" id="statut">
                                        <option value="en attente"
                                            {{ old('statut', $facture->statut) == 'en attente' ? 'selected' : '' }}>En
                                            attente</option>
                                        <option value="payée"
                                            {{ old('statut', $facture->statut) == 'payée' ? 'selected' : '' }}>Payée
                                        </option>
                                        <option value="annulée"
                                            {{ old('statut', $facture->statut) == 'annulée' ? 'selected' : '' }}>Annulée
                                        </option>
                                    </select>
                                    <label for="statut">Statut</label>
                                </div>
                                @error('statut')
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
                                            {{ old('site_id', $facture->site_id) == $site->site_id ? 'selected' : '' }}>
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

                                @if($facture->photo_facture)
                                <div class="mt-2">
                                    @if(Str::endsWith($facture->photo_facture, ['.jpg','.jpeg','.png','.gif']))
                                    <img src="{{ Storage::url($facture->photo_facture) }}" alt="Photo facture"
                                        class="img-thumbnail" style="height:80px; cursor:pointer"
                                        onclick="openModal('{{ Storage::url($facture->photo_facture) }}')">
                                    @else
                                    <a href="{{ Storage::url($facture->photo_facture) }}" target="_blank"
                                        class="btn btn-sm btn-outline-primary">
                                        Voir le fichier
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
                                    @foreach($collectes as $collecte)
                                    <div class="form-check mb-1">
                                        <input class="form-check-input" type="checkbox" name="collecte_ids[]"
                                            id="collecte_{{ $collecte->collecte_id }}"
                                            value="{{ $collecte->collecte_id }}"
                                            {{ in_array($collecte->collecte_id, old('collecte_ids', $facture->collectes->pluck('collecte_id')->toArray())) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="collecte_{{ $collecte->collecte_id }}">
                                            <strong>{{ \Carbon\Carbon::parse($collecte->date_collecte)->format('d/m/Y H:i') }}</strong>
                                            — {{ $collecte->typeDechet?->libelle ?? 'Type inconnu' }} —
                                            {{ $collecte->poids }} Kg
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                                @error('collecte_ids')
                                <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Boutons --}}
                            <div class="text-center mt-3">
                                <button type="submit" class="btn btn-primary">Mettre à jour</button>
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
    function openModal(url) {
        document.getElementById('modalImage').src = url;
        new bootstrap.Modal(document.getElementById('imageModal')).show();
    }
</script>
@endsection