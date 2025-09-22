@extends('layouts.back')

@section('content')
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Éditer le paiement</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                <li class="breadcrumb-item"><a href="{{ route('paiements.index') }}">Paiements</a></li>
                <li class="breadcrumb-item active">Éditer</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('paiements.update', $paiement) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="montant" class="form-label">Montant</label>
                                <input type="number" class="form-control" id="montant" name="montant"
                                    value="{{ old('montant', $paiement->montant) }}" required>
                            </div>
                            <input type="hidden" name="facture_id" value="{{ $paiement->facture_id }}">


                            <div class="mb-3">
                                <label for="mode_paiement" class="form-label">Mode de paiement</label>
                                <select class="form-select" id="mode_paiement" name="mode_paiement" required>
                                    <option value="especes"
                                        {{ $paiement->mode_paiement === 'especes' ? 'selected' : '' }}>Espèces</option>
                                    <option value="cheque"
                                        {{ $paiement->mode_paiement === 'cheque' ? 'selected' : '' }}>Chèque</option>
                                    <option value="virement"
                                        {{ $paiement->mode_paiement === 'virement' ? 'selected' : '' }}>Virement
                                    </option>
                                    <option value="carte bancaire"
                                        {{ $paiement->mode_paiement === 'carte bancaire' ? 'selected' : '' }}>Carte
                                        bancaire</option>
                                    <option value="mobile money"
                                        {{ $paiement->mode_paiement === 'mobile money' ? 'selected' : '' }}>Mobile Money
                                    </option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="paiement_photo" class="form-label">Preuve de paiement</label>
                                <input type="file" class="form-control" id="paiement_photo" name="paiement_photo"
                                    accept="image/*,application/pdf">
                                @if($paiement->paiement_photo)
                                <small class="text-muted">Fichier actuel : <a
                                        href="{{ asset('storage/' . $paiement->paiement_photo) }}"
                                        target="_blank">Voir</a></small>
                                @endif
                            </div>

                            <button type="submit" class="btn btn-primary">Mettre à jour</button>
                            <a href="{{ route('paiements.index') }}" class="btn btn-secondary">Annuler</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection