@extends('layouts.back')

@section('content')
<main id="main" class="main">
    <div class="container">
        <h2 class="mb-4">Ajouter un paiement</h2>

        {{-- Affichage des erreurs --}}
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('paiements.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Facture --}}
            <div class="mb-3">
                <label for="facture_id" class="form-label">Facture</label>
                <select name="facture_id" id="facture_id" class="form-select" required>
                    <option value="">-- Sélectionner une facture --</option>
                    @foreach($factures as $facture)
                    <option value="{{ $facture->facture_id }}"
                        {{ old('facture_id') == $facture->facture_id ? 'selected' : '' }}>
                        {{ $facture->numero }} - {{ number_format($facture->montant_facture, 0, ',', ' ') }} F CFA
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Montant --}}
            <div class="mb-3">
                <label for="montant" class="form-label">Montant</label>
                <input type="number" step="0.01" name="montant" id="montant" class="form-control"
                    value="{{ old('montant') }}" required>
            </div>

            {{-- Mode de paiement --}}
            <div class="mb-3">
                <label for="mode_paiement" class="form-label">Mode de paiement</label>
                <select name="mode_paiement" id="mode_paiement" class="form-select" required>
                    <option value="">-- Sélectionner un mode --</option>
                    <option value="espèces" {{ old('mode_paiement') == 'espèces' ? 'selected' : '' }}>Espèces</option>
                    <option value="virement" {{ old('mode_paiement') == 'virement' ? 'selected' : '' }}>Virement
                    </option>
                    <option value="chèque" {{ old('mode_paiement') == 'chèque' ? 'selected' : '' }}>Chèque</option>
                </select>
            </div>

            {{-- Date de paiement --}}
            <div class="mb-3">
                <label for="date_paiement" class="form-label">Date de paiement</label>
                <input type="datetime-local" name="date_paiement" id="date_paiement" class="form-control"
                    value="{{ old('date_paiement') }}" required>
            </div>

            {{-- Référence --}}
            <div class="mb-3">
                <label for="reference" class="form-label">Référence (optionnelle)</label>
                <input type="text" name="reference" id="reference" class="form-control" value="{{ old('reference') }}">
            </div>

            {{-- Preuve de paiement --}}
            <div class="mb-3">
                <label for="paiement_photo" class="form-label">Preuve de paiement (photo ou PDF)</label>
                <input type="file" name="paiement_photo" id="paiement_photo" class="form-control" accept="image/*,.pdf">
            </div>

            {{-- Boutons --}}
            <div class="mb-3">
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="{{ route('paiements.index') }}" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</main>
@endsection