@extends('layouts.back')

@section('content')
<main id="main" class="main">
    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Liste des paiements</h5>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle text-sm"
                                data-server-search data-total="{{ $paiements->total() }}" style="white-space: nowrap;">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Facture</th>
                                        <th>Statut facture</th>
                                        <th>Site</th>
                                        <th>Montant</th>
                                        <th>Mode</th>
                                        <th>Date</th>
                                        <th>Référence</th>
                                        <th>Preuve</th>
                                        <th>Recu comptable</th>
                                        <th>Statut</th>
                                        @canany(['paiements.view', 'paiements.update', 'paiements.validate', 'paiements.delete'])
                                        <th class="text-center">Actions</th>
                                        @endcanany
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($paiements as $index => $paiement)
                                    @php
                                        $statutPaiement = \Illuminate\Support\Str::of($paiement->statut ?? '')->replace('?', 'e')->ascii()->lower()->toString();
                                        $statutFacture = \Illuminate\Support\Str::of($paiement->facture->statut ?? '')->replace('?', 'e')->ascii()->lower()->toString();
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $paiement->facture->numero_facture ?? 'N/A' }}</td>
                                        <td>
                                            @if(in_array($statutFacture, ['payee', 'paye', 'paid']))
                                            <span class="badge bg-success">Payee</span>
                                            @else
                                            <span class="badge bg-warning text-dark">En attente</span>
                                            @endif
                                        </td>
                                        <td>{{ $paiement->facture?->site?->site_name ?? '-' }}</td>
                                        <td>{{ number_format($paiement->montant, 0, ',', ' ') }} F CFA</td>
                                        <td>{{ ucfirst($paiement->mode_paiement) }}</td>
                                        <td>{{ \Carbon\Carbon::parse($paiement->date_paiement)->format('d/m/Y') }}</td>
                                        <td>{{ $paiement->reference ?? '-' }}</td>
                                        <td class="text-center">
                                            @if($paiement->paiement_photo)
                                            <a class="btn btn-sm btn-info" href="{{ asset('storage/' . $paiement->paiement_photo) }}" target="_blank" title="Voir la preuve">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @else
                                            -
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($paiement->recu_comptable)
                                            <a href="{{ route('paiements.receipt.download', $paiement) }}" class="btn btn-sm btn-outline-success" title="Telecharger le recu">
                                                <i class="bi bi-download"></i>
                                            </a>
                                            @else
                                            -
                                            @endif
                                        </td>
                                        <td>
                                            @if($statutPaiement === 'valide')
                                            <span class="badge bg-success">Validé</span>
                                            @elseif($statutPaiement === 'modifie')
                                            <span class="badge bg-info">Modifié</span>
                                            @elseif(in_array($statutPaiement, ['rejete', 'annule']))
                                            <span class="badge bg-danger">Rejeté</span>
                                            @else
                                            <span class="badge bg-warning text-dark">En attente</span>
                                            @endif
                                        </td>

                                        @canany(['paiements.view', 'paiements.update', 'paiements.validate', 'paiements.delete'])
                                        <td class="text-center">
                                            @can('paiements.view')
                                            <a href="{{ route('paiements.show', $paiement) }}" class="btn btn-sm btn-secondary" title="Détails">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @endcan

                                            @can('paiements.update')
                                            @if($statutPaiement !== 'valide')
                                            <a href="{{ route('paiements.edit', $paiement) }}" class="btn btn-sm btn-warning" title="Éditer">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            @endif
                                            @endcan

                                            @can('paiements.validate')
                                            @if($statutPaiement !== 'valide')
                                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#validatePaiementModal{{ $paiement->paiement_id }}" title="Valider le paiement">
                                                <i class="bi bi-check-circle-fill"></i>
                                            </button>

                                            <div class="modal fade" id="validatePaiementModal{{ $paiement->paiement_id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <form action="{{ route('paiements.valider', $paiement) }}" method="POST" enctype="multipart/form-data" class="validate-payment-form">
                                                        @csrf
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Validation du paiement</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p class="mb-3">Avez-vous un recu fourni par le comptable ?</p>
                                                                <div class="form-check">
                                                                    <input class="form-check-input has-receipt-toggle" type="radio" name="has_receipt" value="0" id="hasReceiptNo{{ $paiement->paiement_id }}" checked>
                                                                    <label class="form-check-label" for="hasReceiptNo{{ $paiement->paiement_id }}">Non, generer automatiquement</label>
                                                                </div>
                                                                <div class="form-check mb-3">
                                                                    <input class="form-check-input has-receipt-toggle" type="radio" name="has_receipt" value="1" id="hasReceiptYes{{ $paiement->paiement_id }}">
                                                                    <label class="form-check-label" for="hasReceiptYes{{ $paiement->paiement_id }}">Oui, televerser un recu</label>
                                                                </div>
                                                                <div class="receipt-upload-wrap d-none">
                                                                    <label class="form-label">Recu comptable (optionnel)</label>
                                                                    <input type="file" name="recu_comptable" class="form-control receipt-upload-input" accept="image/*,application/pdf">
                                                                    <small class="text-muted">Formats autorises : JPG, PNG, PDF (max 5 Mo).</small>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                                <button type="submit" class="btn btn-success">Valider</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                            @endif
                                            @endcan

                                            @can('paiements.update')
                                            @if($statutPaiement !== 'rejete' && $statutPaiement !== 'valide')
                                            <form action="{{ route('paiements.annuler', $paiement) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Annuler le paiement">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            </form>
                                            @endif
                                            @endcan

                                            @can('paiements.delete')
                                            @if($statutPaiement !== 'valide')
                                            <form action="{{ route('paiements.destroy', $paiement) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                            @endif
                                            @endcan
                                        </td>
                                        @endcanany
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-3">
                            {{ $paiements->links() }}
                        </div>
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
    const table = document.querySelector('.datatable');
    if (table) {
        new simpleDatatables.DataTable(table, {
            searchable: true,
            fixedHeight: false,
            perPageSelect: true,
            perPage: 10
        });
    }

    document.querySelectorAll('.validate-payment-form').forEach(function(form) {
        const radios = form.querySelectorAll('.has-receipt-toggle');
        const wrap = form.querySelector('.receipt-upload-wrap');
        const input = form.querySelector('.receipt-upload-input');

        const refresh = function() {
            const yes = form.querySelector('.has-receipt-toggle[value="1"]');
            const useUpload = yes && yes.checked;
            if (useUpload) {
                wrap.classList.remove('d-none');
            } else {
                wrap.classList.add('d-none');
                if (input) input.value = '';
            }
        };

        radios.forEach(function(radio) { radio.addEventListener('change', refresh); });
        refresh();
    });
});
</script>
@endsection
