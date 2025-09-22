@extends('layouts.back')

@section('content')
<main id="main" class="main">

    <div class="pagetitle d-flex justify-content-between align-items-center">
        <h1>Valider la collecte</h1>
    </div>

    <section class="section">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                @if(session('warning'))
                <div class="alert alert-warning">{{ session('warning') }}</div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Collecte : {{ $collecte->titre }}</h5>
                        <p><strong>Site :</strong> {{ $collecte->site?->site_name ?? '-' }}</p>

                        <form action="{{ route('validations.store') }}" method="POST" id="validationForm">
                            @csrf
                            <input type="hidden" name="collecte_id" value="{{ $collecte->collecte_id }}">
                            <input type="hidden" name="signature" id="signature">

                            <div class="mb-3">
                                <label for="type_validation" class="form-label">Type de validation</label>
                                <select name="type_validation" id="type_validation"
                                    class="form-select @error('type_validation') is-invalid @enderror" required>
                                    <option value="">-- Sélectionnez --</option>
                                    <option value="partielle"
                                        {{ old('type_validation') == 'partielle' ? 'selected' : '' }}>Partielle</option>
                                    <option value="totale" {{ old('type_validation') == 'totale' ? 'selected' : '' }}>
                                        Totale</option>
                                </select>
                                @error('type_validation')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="commentaire" class="form-label">Commentaire (optionnel)</label>
                                <textarea name="commentaire" id="commentaire"
                                    class="form-control @error('commentaire') is-invalid @enderror"
                                    rows="3">{{ old('commentaire') }}</textarea>
                                @error('commentaire')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Signature <span class="text-danger">*</span></label>
                                <canvas id="signature-pad" class="border" style="width:100%; height:200px;"></canvas>
                                <button type="button" id="clear-signature"
                                    class="btn btn-sm btn-secondary mt-2">Effacer</button>
                                <small class="form-text text-muted">Veuillez signer dans la zone ci-dessus</small>
                            </div>

                            <div class="mt-3">
                                <a href="{{ route('validations.index') }}" class="btn btn-secondary">Annuler</a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <span id="submitText">Valider la collecte</span>
                                    <span id="submitSpinner" class="spinner-border spinner-border-sm d-none"
                                        role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </span>
                                </button>
                            </div>
                        </form>

                    </div>
                </div>

            </div>
        </div>
    </section>

</main>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const canvas = document.getElementById('signature-pad');
        const signaturePad = new SignaturePad(canvas);
        const clearBtn = document.getElementById('clear-signature');
        const form = document.getElementById('validationForm');
        const signatureInput = document.getElementById('signature');
        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        const submitSpinner = document.getElementById('submitSpinner');

        // Ajuster la taille du canvas
        function resizeCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext('2d').scale(ratio, ratio);
            signaturePad.clear();
        }

        window.addEventListener('resize', resizeCanvas);
        resizeCanvas();

        clearBtn.addEventListener('click', () => signaturePad.clear());

        form.addEventListener('submit', function(e) {
            if (signaturePad.isEmpty()) {
                e.preventDefault();
                alert('Veuillez signer avant de valider la collecte.');
                return false;
            }

            // Désactiver le bouton et afficher le spinner
            submitBtn.disabled = true;
            submitText.classList.add('d-none');
            submitSpinner.classList.remove('d-none');

            signatureInput.value = signaturePad.toDataURL('image/png');
        });
    });
</script>
@endsection