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

                {{-- Recapitulatif : l'agent du site doit voir exactement ce qu'il signe --}}
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                            <div>
                                <h5 class="card-title mb-1 pb-0">Collecte
                                    {{ $collecte->numero_collecte ?? '-' }}</h5>
                                <div class="text-muted">{{ $collecte->site?->site_name ?? '-' }}</div>
                            </div>
                            <span class="badge bg-{{ $collecte->statut_badge }} align-self-center">
                                {{ ucfirst(str_replace('_', ' ', $collecte->statut)) }}
                            </span>
                        </div>

                        <div class="row g-3">
                            <div class="col-6 col-md-4">
                                <div class="text-muted small text-uppercase">Date de collecte</div>
                                <div class="fs-5 fw-semibold">
                                    {{ $collecte->date_collecte?->format('d/m/Y') ?? '-' }}
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="text-muted small text-uppercase">Heure</div>
                                <div class="fs-5 fw-semibold">
                                    {{ $collecte->date_collecte?->format('H:i') ?? '-' }}
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="text-muted small text-uppercase">Quantite collectee</div>
                                <div class="fs-3 fw-bold text-success">
                                    {{ \App\Models\Collecte::formatPoids($collecte->poids) }} kg
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="text-muted small text-uppercase">Type de dechet</div>
                                <div class="fw-semibold">{{ $collecte->typeDechet?->libelle ?? '-' }}</div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="text-muted small text-uppercase">Collecteur</div>
                                <div class="fw-semibold">
                                    {{ trim(($collecte->agent?->firstname ?? '') . ' ' . ($collecte->agent?->lastname ?? '')) ?: '-' }}
                                </div>
                            </div>
                        </div>

                        @if($collecte->incident)
                        <div class="alert alert-warning mt-3 mb-0">
                            <strong>Incident signale :</strong> {{ $collecte->incident->description }}
                        </div>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('validations.store') }}" method="POST" id="validationForm">
                            @csrf
                            <input type="hidden" name="collecte_id" value="{{ $collecte->collecte_id }}">
                            <input type="hidden" name="signature" id="signature">

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
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                    <label class="form-label mb-0">Signature <span class="text-danger">*</span></label>
                                    <button type="button" id="fullscreen-signature" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-arrows-fullscreen"></i> Signer en grand
                                    </button>
                                </div>

                                <div id="signature-host" class="signature-host">
                                    <canvas id="signature-pad" class="signature-pad"></canvas>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <small class="form-text text-muted mb-0">Signez avec le doigt ou un stylet dans le
                                        cadre.</small>
                                    <button type="button" id="clear-signature" class="btn btn-sm btn-secondary">
                                        Effacer
                                    </button>
                                </div>
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

{{-- Zone de signature plein ecran --}}
<div id="signature-overlay" class="signature-overlay" hidden>
    <div class="signature-overlay__bar">
        <span class="signature-overlay__title">
            {{ $collecte->numero_collecte ?? 'Collecte' }} &middot;
            {{ \App\Models\Collecte::formatPoids($collecte->poids) }} kg &middot;
            {{ $collecte->date_collecte?->format('d/m/Y H:i') ?? '' }}
        </span>
        <div class="d-flex gap-2">
            <button type="button" id="overlay-clear" class="btn btn-sm btn-secondary">Effacer</button>
            <button type="button" id="overlay-done" class="btn btn-sm btn-primary">Terminer</button>
        </div>
    </div>
    <div id="signature-overlay-host" class="signature-overlay__host"></div>
    <div class="signature-overlay__hint">Pour plus de confort, tournez le telephone en mode paysage.</div>
</div>
@endsection

@push('styles')
<style>
    /* touch-action:none est indispensable : sans lui, le navigateur mobile
       interprete le glissement du doigt comme un defilement de page. */
    .signature-pad {
        display: block;
        width: 100%;
        height: 100%;
        touch-action: none;
        -webkit-user-select: none;
        user-select: none;
        -webkit-touch-callout: none;
        background: #fff;
        cursor: crosshair;
    }

    .signature-host {
        width: 100%;
        height: 280px;
        border: 2px dashed #adb5bd;
        border-radius: .5rem;
        overflow: hidden;
        background: #fff;
    }

    @media (max-width: 767.98px) {
        .signature-host {
            height: 45vh;
            min-height: 240px;
        }
    }

    .signature-overlay {
        position: fixed;
        inset: 0;
        z-index: 20000;
        background: #f8f9fa;
        display: flex;
        flex-direction: column;
        overscroll-behavior: contain;
    }

    .signature-overlay[hidden] {
        display: none;
    }

    .signature-overlay__bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        padding: .5rem .75rem;
        background: #fff;
        border-bottom: 1px solid #dee2e6;
        flex: 0 0 auto;
    }

    .signature-overlay__title {
        font-size: .85rem;
        font-weight: 600;
        color: #495057;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .signature-overlay__host {
        flex: 1 1 auto;
        margin: .5rem;
        border: 2px dashed #adb5bd;
        border-radius: .5rem;
        overflow: hidden;
        background: #fff;
        min-height: 0;
    }

    .signature-overlay__hint {
        flex: 0 0 auto;
        padding: .25rem .75rem .5rem;
        font-size: .75rem;
        color: #6c757d;
        text-align: center;
    }

    @media (orientation: landscape) {
        .signature-overlay__hint {
            display: none;
        }
    }

    /* Empeche le scroll de la page pendant la signature plein ecran */
    body.signature-locked {
        overflow: hidden;
        touch-action: none;
    }
</style>
@endpush

@section('scripts')
<script src="{{ asset('backend/assets/vendor/signature-pad/signature_pad.umd.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const canvas = document.getElementById('signature-pad');
        const form = document.getElementById('validationForm');
        const signatureInput = document.getElementById('signature');
        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        const submitSpinner = document.getElementById('submitSpinner');
        const inlineHost = document.getElementById('signature-host');
        const overlay = document.getElementById('signature-overlay');
        const overlayHost = document.getElementById('signature-overlay-host');

        // Le script est servi en local : s'il manque quand meme, on previent
        // l'agent plutot que de laisser un bouton inerte.
        if (typeof SignaturePad === 'undefined') {
            inlineHost.innerHTML =
                '<div class="alert alert-danger m-2 mb-0">Le module de signature n\'a pas pu etre charge. ' +
                'Rechargez la page.</div>';
            submitBtn.disabled = true;
            return;
        }

        const signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255, 255, 255)',
            penColor: 'rgb(17, 17, 17)',
            minWidth: 1.2,
            maxWidth: 3.2,
            throttle: 8
        });

        // Taille CSS courante du canvas, pour ne redimensionner que lorsque
        // c'est reellement necessaire et pour remettre le trace a l'echelle.
        let currentSize = { w: 0, h: 0 };

        function syncCanvas() {
            const cssW = Math.round(canvas.offsetWidth);
            const cssH = Math.round(canvas.offsetHeight);

            if (!cssW || !cssH) {
                return;
            }

            // Sur mobile, masquer/afficher la barre d'adresse declenche un
            // `resize` sans changer la taille du canvas : on ne touche a rien,
            // sinon la signature en cours serait effacee.
            if (cssW === currentSize.w && cssH === currentSize.h) {
                return;
            }

            const data = signaturePad.toData();
            const ratio = Math.max(window.devicePixelRatio || 1, 1);

            canvas.width = cssW * ratio;
            canvas.height = cssH * ratio;

            const ctx = canvas.getContext('2d');
            ctx.setTransform(1, 0, 0, 1, 0, 0);
            ctx.scale(ratio, ratio);

            signaturePad.clear();

            // On conserve le trace deja realise en le remettant a l'echelle.
            if (data.length && currentSize.w && currentSize.h) {
                const scale = Math.min(cssW / currentSize.w, cssH / currentSize.h);
                signaturePad.fromData(data.map(function(group) {
                    return Object.assign({}, group, {
                        points: group.points.map(function(point) {
                            return Object.assign({}, point, {
                                x: point.x * scale,
                                y: point.y * scale
                            });
                        })
                    });
                }));
            }

            currentSize = { w: cssW, h: cssH };
        }

        let resizeTimer = null;
        function scheduleSync() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(syncCanvas, 120);
        }

        window.addEventListener('resize', scheduleSync);
        window.addEventListener('orientationchange', scheduleSync);
        syncCanvas();

        document.getElementById('clear-signature').addEventListener('click', function() {
            signaturePad.clear();
        });

        // --- Mode plein ecran : on deplace le meme canvas, donc une seule
        // source de verite pour la signature. ---
        function openFullscreen() {
            overlay.hidden = false;
            document.body.classList.add('signature-locked');
            overlayHost.appendChild(canvas);
            syncCanvas();
        }

        function closeFullscreen() {
            inlineHost.appendChild(canvas);
            overlay.hidden = true;
            document.body.classList.remove('signature-locked');
            syncCanvas();
        }

        document.getElementById('fullscreen-signature').addEventListener('click', openFullscreen);
        document.getElementById('overlay-done').addEventListener('click', closeFullscreen);
        document.getElementById('overlay-clear').addEventListener('click', function() {
            signaturePad.clear();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !overlay.hidden) {
                closeFullscreen();
            }
        });

        form.addEventListener('submit', function(e) {
            if (signaturePad.isEmpty()) {
                e.preventDefault();
                if (!overlay.hidden) {
                    closeFullscreen();
                }
                alert('Veuillez signer avant de valider la collecte.');
                return false;
            }

            // On capture le trace AVANT de refermer le plein ecran : refermer
            // redimensionne le canvas et degraderait la signature exportee.
            signatureInput.value = signaturePad.toDataURL('image/png');

            if (!overlay.hidden) {
                closeFullscreen();
            }

            submitBtn.disabled = true;
            submitText.classList.add('d-none');
            submitSpinner.classList.remove('d-none');
        });
    });
</script>
@endsection
