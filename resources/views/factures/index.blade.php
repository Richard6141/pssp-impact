@extends('layouts.back')

@section('content')
<main id="main" class="main">

    <div class="pagetitle d-flex justify-content-between align-items-center">
        <h1>Factures</h1>

        <!-- Bouton Créer - Permission: factures.create -->
        @can('factures.create')
        <a href="{{ route('factures.create') }}"
            class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center"
            style="width:45px; height:45px;">
            <i class="bi bi-plus-lg"></i>
        </a>
        @endcan
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Liste des factures</h5>

                        <div class="table-responsive">
                            <table class="table datatable table-hover align-middle text-sm text-nowrap">

                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Numéro</th>
                                        <th>Date</th>
                                        <th>Montant</th>
                                        <th>Statut</th>
                                        <th>Site</th>
                                        <th>Comptable</th>
                                        <th>Photo / PDF</th>
                                        @canany(['factures.view', 'factures.update', 'factures.delete',
                                        'paiements.record'])
                                        <th class="text-center">Actions</th>
                                        @endcanany
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($factures as $index => $facture)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $facture->numero_facture }}</td>
                                        <td>{{ \Carbon\Carbon::parse($facture->date_facture)->format('d/m/Y') }}</td>

                                        {{-- affiche formaté dans la table, mais data-montant en brut plus bas --}}
                                        <td>{{ number_format($facture->montant_facture, 2, ',', ' ') }} FCFA</td>

                                        <td>{{ ucfirst($facture->statut) }}</td>
                                        <td>{{ $facture->site?->site_name ?? '—' }}</td>
                                        <td>{{ $facture->comptable?->firstname ?? '' }}
                                            {{ $facture->comptable?->lastname ?? '' }}
                                        </td>
                                        <td>
                                            @if($facture->photo_facture)
                                            @php $ext = strtolower(pathinfo($facture->photo_facture,
                                            PATHINFO_EXTENSION)); @endphp
                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#factureModal{{ $facture->facture_id }}">
                                                <i class="bi bi-eye"></i>
                                            </button>

                                            <!-- Modal Aperçu Facture -->
                                            <div class="modal fade" id="factureModal{{ $facture->facture_id }}"
                                                tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-xl modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Aperçu de la facture</h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal" aria-label="Fermer"></button>
                                                        </div>
                                                        <div class="modal-body text-center">
                                                            @if(in_array($ext, ['jpg','jpeg','png','gif']))
                                                            <img src="{{ Storage::url($facture->photo_facture) }}"
                                                                alt="Facture" class="img-fluid">
                                                            @elseif($ext === 'pdf')
                                                            <embed src="{{ Storage::url($facture->photo_facture) }}"
                                                                type="application/pdf" width="100%" height="600px">
                                                            @else
                                                            <p>Type de fichier non pris en charge.
                                                                <a href="{{ Storage::url($facture->photo_facture) }}"
                                                                    target="_blank">Télécharger</a>
                                                            </p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @else
                                            —
                                            @endif
                                        </td>

                                        @canany(['factures.view', 'factures.update', 'factures.delete',
                                        'paiements.record'])
                                        <td class="text-center">
                                            <!-- Bouton Voir - Permission: factures.view -->
                                            @can('factures.view')
                                            <a href="{{ route('factures.show', $facture->facture_id) }}"
                                                class="btn btn-sm btn-info" title="Voir"><i class="bi bi-eye"></i></a>
                                            @endcan

                                            @if($facture->statut == 'en attente')
                                            <!-- Bouton Modifier - Permission: factures.update -->
                                            @can('factures.update')
                                            <a href="{{ route('factures.edit', $facture->facture_id) }}"
                                                class="btn btn-sm btn-warning" title="Modifier"><i
                                                    class="bi bi-pencil"></i></a>
                                            @endcan

                                            <!-- Bouton Supprimer - Permission: factures.delete -->
                                            @can('factures.delete')
                                            <form action="{{ route('factures.destroy', $facture->facture_id) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" data-confirm-delete
                                                    data-item-name="Facture #{{ $facture->numero_facture }}"
                                                    data-confirm-title="Supprimer la facture"
                                                    data-confirm-text="Voulez-vous vraiment supprimer cette facture ? Cette action est irréversible."
                                                    title="Supprimer" data-bs-toggle="tooltip">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                            @endcan

                                            <!-- Bouton Paiement - Permission: paiements.record -->
                                            @can('paiements.record')
                                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                                                data-bs-target="#paiementModal"
                                                data-facture="{{ $facture->facture_id }}"
                                                data-montant="{{ $facture->montant_facture }}"
                                                data-numero="{{ $facture->numero_facture }}" title="Payer">
                                                <i class="bi bi-cash-coin"></i>
                                            </button>
                                            @endcan
                                            @endif
                                        </td>
                                        @endcanany
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination si nécessaire --}}
                        <div class="d-flex justify-content-center mt-3">
                            {{ $factures->links() }}
                        </div>

                        <!-- Modal Paiement (unique réutilisable) -->
                        @can('paiements.record')
                        <div class="modal fade" id="paiementModal" tabindex="-1" aria-labelledby="paiementModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog">
                                <form action="{{ route('paiements.store') }}" method="POST"
                                    enctype="multipart/form-data" id="paiementForm">
                                    @csrf
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="paiementModalLabel">Enregistrer un Paiement</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Fermer"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="facture_id" id="facture_id">

                                            <!-- Numéro de facture (pré-rempli et lecture seule) -->
                                            <div class="mb-3">
                                                <label for="numero_facture" class="form-label">Numéro de facture</label>
                                                <input type="text" class="form-control" id="numero_facture"
                                                    name="numero_facture" readonly>
                                            </div>

                                            <!-- Montant (pré-rempli) -->
                                            <div class="mb-3">
                                                <label for="montant" class="form-label">Montant payé</label>
                                                <input type="number" step="0.01" class="form-control" id="montant"
                                                    name="montant" required readonly>
                                            </div>

                                            <!-- Mode de paiement -->
                                            <div class="mb-3">
                                                <label for="mode_paiement" class="form-label">Mode de paiement</label>
                                                <select class="form-select" id="mode_paiement" name="mode_paiement"
                                                    required>
                                                    <option value="">-- Sélectionnez --</option>
                                                    <option value="especes">Espèces</option>
                                                    <option value="cheque">Chèque</option>
                                                    <option value="virement">Virement bancaire</option>
                                                    <option value="carte bancaire">Carte bancaire</option>
                                                    <option value="mobile money">Mobile Money</option>
                                                </select>
                                            </div>

                                            <!-- Preuve (image ou pdf). Obligatoire seulement pour cheque/virement (géré par JS) -->
                                            <div class="mb-3" id="preuveBlock" style="display:none;">
                                                <label for="paiement_photo" class="form-label">Preuve de
                                                    paiement</label>
                                                <input type="file" class="form-control" id="paiement_photo"
                                                    name="paiement_photo" accept="image/*,application/pdf">
                                                <small class="form-text text-muted">Requis pour chèque ou
                                                    virement.</small>
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-primary">Valider</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endcan
                        <!-- Fin modal paiement -->

                    </div>
                </div>

            </div>
        </div>
    </section>

</main>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const paiementModal = document.getElementById('paiementModal');

    // Vérifier si le modal existe (permission paiements.record)
    if (!paiementModal) return;

    const factureInput = document.getElementById('facture_id');
    const montantInput = document.getElementById('montant');
    const numeroFactureInput = document.getElementById('numero_facture');
    const modePaiement = document.getElementById('mode_paiement');
    const preuveBlock = document.getElementById('preuveBlock');
    const preuveInput = document.getElementById('paiement_photo');
    const paiementForm = document.getElementById('paiementForm');

    // Au moment où le modal s'ouvre, bootstrap fournit event.relatedTarget = bouton qui l'a déclenché
    paiementModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        if (!button) return;

        // lire data-attributes
        const factureId = button.getAttribute('data-facture') || '';
        const montant = button.getAttribute('data-montant') || '';
        const numeroFacture = button.getAttribute('data-numero') || '';

        // injecter dans le formulaire
        factureInput.value = factureId;
        montantInput.value = montant;
        numeroFactureInput.value = numeroFacture;

        // reset mode & preuve
        modePaiement.value = '';
        preuveBlock.style.display = 'none';
        preuveInput.removeAttribute('required');
        preuveInput.value = '';
    });

    // rendre la preuve obligatoire si mode = cheque || virement
    modePaiement.addEventListener('change', function() {
        const v = this.value;
        if (v === 'cheque' || v === 'virement') {
            preuveBlock.style.display = 'block';
            preuveInput.setAttribute('required', 'required');
        } else {
            preuveBlock.style.display = 'none';
            preuveInput.removeAttribute('required');
        }
    });

    // Optionnel : empêcher le submit si preuve requise mais non fournie (sécurité JS supplémentaire)
    paiementForm.addEventListener('submit', function(e) {
        const mode = modePaiement.value;
        if ((mode === 'cheque' || mode === 'virement') && !preuveInput.value) {
            e.preventDefault();
            alert('Veuillez joindre la preuve de paiement (image ou PDF) pour ce mode de paiement.');
            return false;
        }
    });
});
</script>
@endsection