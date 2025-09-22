@extends('layouts.back')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
        <h1>Détails de la facture</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                <li class="breadcrumb-item"><a href="{{ route('factures.index') }}">Factures</a></li>
                <li class="breadcrumb-item active">Détails</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row justify-content-center">
            <div class="col-lg-10">

                <!-- Informations de la facture -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Facture N° : {{ $facture->numero_facture ?? '#' . $facture->facture_id }}
                        </h5>

                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">
                                        <strong>Date :</strong>
                                        {{ \Carbon\Carbon::parse($facture->date_facture)->format('d/m/Y') }}
                                    </li>
                                    <li class="list-group-item">
                                        <strong>Montant :</strong>
                                        {{ number_format($facture->montant_facture, 2, ',', ' ') }} FCFA
                                    </li>
                                    <li class="list-group-item">
                                        <strong>Statut :</strong>
                                        <span
                                            class="badge bg-{{ $facture->statut === 'payée' ? 'success' : ($facture->statut === 'en attente' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($facture->statut) }}
                                        </span>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">
                                        <strong>Site :</strong> {{ $facture->site?->site_name ?? '—' }}
                                    </li>
                                    <li class="list-group-item">
                                        <strong>Comptable :</strong>
                                        {{ $facture->comptable?->firstname }} {{ $facture->comptable?->lastname }}
                                    </li>
                                    <li class="list-group-item">
                                        <strong>Photo / PDF :</strong>
                                        @if($facture->photo_facture)
                                        @php
                                        $ext = strtolower(pathinfo($facture->photo_facture, PATHINFO_EXTENSION));
                                        @endphp
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#factureModal">
                                            Voir le document
                                        </button>

                                        <!-- Modal -->
                                        <div class="modal fade" id="factureModal" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-xl modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Aperçu de la facture</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Fermer"></button>
                                                    </div>
                                                    <div class="modal-body text-center">
                                                        @if(in_array($ext, ['jpg','jpeg','png','gif']))
                                                        <img src="{{ Storage::url($facture->photo_facture) }}"
                                                            alt="Facture" class="img-fluid">
                                                        @elseif($ext === 'pdf')
                                                        <embed src="{{ Storage::url($facture->photo_facture) }}"
                                                            type="application/pdf" width="100%" height="600px">
                                                        @else
                                                        <p>Type de fichier non pris en charge. <a
                                                                href="{{ Storage::url($facture->photo_facture) }}"
                                                                target="_blank">Télécharger</a></p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @else
                                        <span class="text-muted">Aucun document</span>
                                        @endif
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistiques des collectes -->
                @if(isset($stats) && $stats['total_collectes'] > 0)
                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title">Résumé des collectes</h5>

                        <div class="row text-center">
                            <div class="col-md-3">
                                <div class="bg-primary text-white rounded p-3">
                                    <h4>{{ $stats['total_collectes'] }}</h4>
                                    <small>Collectes</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="bg-success text-white rounded p-3">
                                    <h4>{{ number_format($stats['poids_total'], 2) }} Kg</h4>
                                    <small>Poids total</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="bg-info text-white rounded p-3">
                                    <h4>{{ $stats['types_dechets']->count() }}</h4>
                                    <small>Types de déchets</small>
                                </div>
                            </div>
                        </div>

                        @if($stats['types_dechets']->count() > 0)
                        <div class="mt-3">
                            <strong>Types de déchets :</strong>
                            @foreach($stats['types_dechets'] as $type)
                            <span class="badge bg-light text-dark me-1">{{ $type }}</span>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Liste détaillée des collectes -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title">Collectes concernées par cette facture</h5>

                        @if($facture->collectes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date de collecte</th>
                                        <th>Type de déchet</th>
                                        <th>Poids</th>
                                        <th>Agent</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($facture->collectes as $collecte)
                                    <tr>
                                        <td>
                                            <strong>{{ \Carbon\Carbon::parse($collecte->date_collecte)->format('d/m/Y H:i') }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">
                                                {{ $collecte->typeDechet?->libelle ?? 'Type inconnu' }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong>{{ $collecte->poids }} Kg</strong>
                                        </td>
                                        <td>
                                            {{ $collecte->agent?->firstname ?? '—' }}
                                            {{ $collecte->agent?->lastname ?? '' }}
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $collecte->isValid ? 'success' : 'warning' }}">
                                                {{ $collecte->isValid ? 'Validée' : 'En attente' }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="2">Total</th>
                                        <th>{{ $facture->collectes->sum('poids') }} Kg</th>
                                        <th colspan="2">{{ $facture->collectes->count() }} collectes</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        @else
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            Aucune collecte associée à cette facture.
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Actions -->
                <div class="card mt-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('factures.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Retour à la liste
                            </a>

                            <div>
                                @if($facture->statut !== 'payée')
                                <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                    data-bs-target="#paiementModal">
                                    <i class="bi bi-cash-coin"></i> Enregistrer un paiement
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

</main>

<!-- Modal Paiement (si nécessaire) -->
@if($facture->statut !== 'payée')
<div class="modal fade" id="paiementModal" tabindex="-1" aria-labelledby="paiementModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('paiements.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paiementModalLabel">Enregistrer un Paiement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="facture_id" value="{{ $facture->facture_id }}">

                    <div class="mb-3">
                        <label for="reference" class="form-label">Référence facture</label>
                        <input type="text" class="form-control" id="reference" name="reference"
                            value="{{ $facture->numero_facture }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="montant" class="form-label">Montant payé</label>
                        <input type="number" step="0.01" class="form-control" id="montant" name="montant"
                            value="{{ $facture->montant_facture }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="mode_paiement" class="form-label">Mode de paiement</label>
                        <select class="form-select" id="mode_paiement" name="mode_paiement" required>
                            <option value="">-- Sélectionnez --</option>
                            <option value="especes">Espèces</option>
                            <option value="cheque">Chèque</option>
                            <option value="virement">Virement bancaire</option>
                            <option value="carte bancaire">Carte bancaire</option>
                            <option value="mobile money">Mobile Money</option>
                        </select>
                    </div>

                    <div class="mb-3" id="preuveBlock" style="display:none;">
                        <label for="paiement_photo" class="form-label">Preuve de paiement</label>
                        <input type="file" class="form-control" id="paiement_photo" name="paiement_photo"
                            accept="image/*,application/pdf">
                        <small class="form-text text-muted">Requis pour chèque ou virement.</small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Valider le paiement</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gestion du modal paiement
        const modePaiement = document.getElementById('mode_paiement');
        const preuveBlock = document.getElementById('preuveBlock');
        const preuveInput = document.getElementById('paiement_photo');

        if (modePaiement) {
            modePaiement.addEventListener('change', function() {
                const value = this.value;
                if (value === 'cheque' || value === 'virement') {
                    preuveBlock.style.display = 'block';
                    preuveInput.setAttribute('required', 'required');
                } else {
                    preuveBlock.style.display = 'none';
                    preuveInput.removeAttribute('required');
                }
            });
        }
    });
</script>
@endsection