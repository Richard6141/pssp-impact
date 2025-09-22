@extends('layouts.back')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
        <h1>Détails du paiement</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                <li class="breadcrumb-item"><a href="{{ route('paiements.index') }}">Paiements</a></li>
                <li class="breadcrumb-item active">Détails</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Paiement #{{ $paiement->paiement_id }}</h5>

                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><strong>Facture :</strong>
                                {{ $paiement->facture->numero ?? 'N/A' }}
                            </li>
                            <li class="list-group-item"><strong>Montant :</strong>
                                {{ number_format($paiement->montant, 0, ',', ' ') }} F CFA
                            </li>
                            <li class="list-group-item"><strong>Mode de paiement :</strong>
                                {{ ucfirst($paiement->mode_paiement) }}
                            </li>
                            <li class="list-group-item"><strong>Date :</strong>
                                {{ \Carbon\Carbon::parse($paiement->date_paiement)->format('d/m/Y') }}
                            </li>
                            <li class="list-group-item"><strong>Référence :</strong>
                                {{ $paiement->reference ?? '-' }}
                            </li>
                            <li class="list-group-item"><strong>Statut :</strong>
                                @if($paiement->statut === 'validé')
                                <span class="badge bg-success">Validé</span>
                                @elseif($paiement->statut === 'annulé')
                                <span class="badge bg-danger">Annulé</span>
                                @else
                                <span class="badge bg-warning text-dark">En attente</span>
                                @endif
                            </li>
                            <li class="list-group-item"><strong>Preuve :</strong>
                                @if($paiement->paiement_photo)
                                @php
                                $ext = strtolower(pathinfo($paiement->paiement_photo, PATHINFO_EXTENSION));
                                @endphp
                                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal"
                                    data-bs-target="#preuveModal">
                                    Voir
                                </button>

                                <!-- Modal Preuve -->
                                <div class="modal fade" id="preuveModal" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-xl modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Preuve du paiement</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Fermer"></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                @if(in_array($ext, ['jpg','jpeg','png','gif']))
                                                <img src="{{ asset('storage/' . $paiement->paiement_photo) }}"
                                                    alt="Preuve" class="img-fluid">
                                                @elseif($ext === 'pdf')
                                                <embed src="{{ asset('storage/' . $paiement->paiement_photo) }}"
                                                    type="application/pdf" width="100%" height="600px">
                                                @else
                                                <p>Type de fichier non pris en charge. <a
                                                        href="{{ asset('storage/' . $paiement->paiement_photo) }}"
                                                        target="_blank">Télécharger</a></p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @else
                                -
                                @endif
                            </li>
                        </ul>

                        <div class="mt-3">
                            <a href="{{ route('paiements.index') }}" class="btn btn-secondary">Retour</a>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

</main>
@endsection