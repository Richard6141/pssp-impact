@extends('layouts.back')

@section('content')
<main id="main" class="main">

    <!-- <div class="pagetitle d-flex justify-content-between align-items-center">
        <h1>Paiements</h1>
        <a href="{{ route('paiements.create') }}"
            class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center"
            style="width:45px; height:45px;">
            <i class="bi bi-plus-lg"></i>
        </a>
    </div> -->

    <section class="section">
        <div class="row">
            <div class="col-lg-12">

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Liste des paiements</h5>

                        <div class="table-responsive">
                            <table class="table datatable table-hover align-middle text-sm"
                                style="white-space: nowrap;">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Facture</th>
                                        <th>Montant</th>
                                        <th>Mode</th>
                                        <th>Date</th>
                                        <th>Référence</th>
                                        <th>Preuve</th>
                                        <th>Statut</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($paiements as $index => $paiement)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $paiement->facture->numero_facture ?? 'N/A' }}</td>
                                        <td>{{ number_format($paiement->montant, 0, ',', ' ') }} F CFA</td>
                                        <td>{{ ucfirst($paiement->mode_paiement) }}</td>
                                        <td>{{ \Carbon\Carbon::parse($paiement->date_paiement)->format('d/m/Y') }}</td>
                                        <td>{{ $paiement->reference ?? '-' }}</td>
                                        <td class="text-center">
                                            @if($paiement->paiement_photo)
                                            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal"
                                                data-bs-target="#preuveModal{{ $paiement->paiement_id }}">
                                                <i class="bi bi-eye"></i>
                                            </button>

                                            <!-- Modal Preuve -->
                                            <div class="modal fade" id="preuveModal{{ $paiement->paiement_id }}"
                                                tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Preuve du paiement</h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal" aria-label="Fermer"></button>
                                                        </div>
                                                        <div class="modal-body text-center">
                                                            @php
                                                            $ext = strtolower(pathinfo($paiement->paiement_photo,
                                                            PATHINFO_EXTENSION));
                                                            @endphp
                                                            @if(in_array($ext, ['jpg','jpeg','png','gif']))
                                                            <img src="{{ asset('storage/' . $paiement->paiement_photo) }}"
                                                                class="img-fluid" alt="Preuve paiement">
                                                            @elseif($ext === 'pdf')
                                                            <embed
                                                                src="{{ asset('storage/' . $paiement->paiement_photo) }}"
                                                                type="application/pdf" width="100%" height="500px">
                                                            @else
                                                            <p>Fichier non pris en charge.
                                                                <a href="{{ asset('storage/' . $paiement->paiement_photo) }}"
                                                                    target="_blank">Télécharger</a>
                                                            </p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @else
                                            -
                                            @endif
                                        </td>
                                        <td>
                                            @if($paiement->statut === 'validé')
                                            <span class="badge bg-success">Validé</span>
                                            @elseif($paiement->statut === 'annulé')
                                            <span class="badge bg-danger">Annulé</span>
                                            @else
                                            <span class="badge bg-warning text-dark">En attente</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('paiements.show', $paiement) }}"
                                                class="btn btn-sm btn-secondary" title="Détails"><i
                                                    class="bi bi-eye"></i></a>
                                            <a href="{{ route('paiements.edit', $paiement) }}"
                                                class="btn btn-sm btn-warning" title="Éditer"><i
                                                    class="bi bi-pencil"></i></a>

                                            @if($paiement->statut !== 'validé')
                                            <form action="{{ route('paiements.valider', $paiement) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" data-confirm-delete
                                                    data-item-name="Facture #{{ $paiement->paiement_id ?? $paiement->numero_paiement }}"
                                                    data-confirm-title="Suppression paiement"
                                                    data-confirm-text="'Voulez-vous vraiment valider ce paiement ?'"
                                                    title="Supprimer" data-bs-toggle="tooltip">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                            @endif
                                            @if($paiement->statut !== 'annulé')
                                            <form action="{{ route('paiements.annuler', $paiement) }}" method="POST"
                                                style="display:inline;">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Annuler">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </td>
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
        // Initialiser DataTable si non déjà
        const table = document.querySelector('.datatable');
        if (table) {
            new simpleDatatables.DataTable(table, {
                searchable: true,
                fixedHeight: false,
                perPageSelect: true,
                perPage: 10
            });
        }
    });
</script>
@endsection