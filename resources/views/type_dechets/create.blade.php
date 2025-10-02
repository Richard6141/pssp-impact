@extends('layouts.back')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
        <h1>Type de Collecte</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
                <li class="breadcrumb-item"><a href="{{ route('type_dechets.index') }}">Types de Collectes</a></li>
                <li class="breadcrumb-item active">{{ isset($type) ? 'Modification' : 'Enregistrement' }}</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="row">
            <div class="col-lg-8">

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            {{ isset($type) ? 'Modifier le type de collecte' : 'Ajouter un type de collecte' }}
                        </h5>

                        <!-- Formulaire -->
                        <form
                            action="{{ isset($type) ? route('type_dechets.update', $type->type_dechet_id) : route('type_dechets.store') }}"
                            method="POST" id="typeDechetForm">
                            @csrf
                            @if(isset($type))
                            @method('PUT')
                            @endif

                            <div class="row mb-3">
                                <label for="libelle" class="col-sm-2 col-form-label">Libellé <span
                                        class="text-danger">*</span></label>
                                <div class="col-sm-10">
                                    <input type="text" name="libelle" id="libelle"
                                        value="{{ old('libelle', $type->libelle ?? '') }}"
                                        class="form-control @error('libelle') is-invalid @enderror" required>
                                    @error('libelle')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="description" class="col-sm-2 col-form-label">Description</label>
                                <div class="col-sm-10">
                                    <textarea name="description" id="description"
                                        class="form-control @error('description') is-invalid @enderror"
                                        rows="3">{{ old('description', $type->description ?? '') }}</textarea>
                                    @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <span id="btnText">
                                        {{ isset($type) ? 'Mettre à jour' : 'Enregistrer' }}
                                    </span>
                                    <span id="btnLoader" class="d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status"
                                            aria-hidden="true"></span>
                                        {{ isset($type) ? 'Mise à jour en cours...' : 'Enregistrement en cours...' }}
                                    </span>
                                </button>
                                <a href="{{ route('type_dechets.index') }}" class="btn btn-secondary">Annuler</a>
                            </div>
                        </form><!-- End Form -->

                    </div>
                </div>

            </div>
        </div>
    </section>

</main>

<style>
    /* Style pour le loader */
    .spinner-border-sm {
        width: 1rem;
        height: 1rem;
    }

    /* Animation personnalisée pour le bouton */
    #submitBtn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    /* Transition douce */
    #submitBtn span {
        transition: all 0.3s ease;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('typeDechetForm');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const btnLoader = document.getElementById('btnLoader');

        form.addEventListener('submit', function(e) {
            // Vérifier si le formulaire est valide avant d'afficher le loader
            if (form.checkValidity()) {
                // Désactiver le bouton pour éviter les soumissions multiples
                submitBtn.disabled = true;

                // Masquer le texte normal et afficher le loader
                btnText.classList.add('d-none');
                btnLoader.classList.remove('d-none');

                // Changer la couleur du bouton pour indiquer le loading
                submitBtn.classList.remove('btn-primary');
                submitBtn.classList.add('btn-info');
            }
        });

        // Optionnel : Réactiver le bouton si il y a une erreur de validation
        form.addEventListener('invalid', function() {
            resetButton();
        }, true);

        function resetButton() {
            submitBtn.disabled = false;
            btnText.classList.remove('d-none');
            btnLoader.classList.add('d-none');
            submitBtn.classList.remove('btn-info');
            submitBtn.classList.add('btn-primary');
        }

        // Réinitialiser le bouton si l'utilisateur revient sur la page (par exemple avec le bouton retour)
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                resetButton();
            }
        });
    });
</script>

@endsection