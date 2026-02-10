@extends('layouts.back')

@section('content')
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Activer l'Authentification à Deux Facteurs</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
                <li class="breadcrumb-item">Sécurité</li>
                <li class="breadcrumb-item active">2FA</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <x-premium-card title="Configuration de l'authentification à deux facteurs">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        L'authentification à deux facteurs ajoute une couche de sécurité supplémentaire à votre compte.
                    </div>

                    <div class="row">
                        <div class="col-md-6 text-center mb-4">
                            <h5 class="mb-3">1. Scannez ce QR Code</h5>
                            <div class="p-3 bg-light rounded">
                                {!! $qr_code !!}
                            </div>
                            <p class="text-muted mt-3 small">
                                Utilisez une application comme Google Authenticator ou Authy
                            </p>
                        </div>

                        <div class="col-md-6">
                            <h5 class="mb-3">2. Ou entrez ce code manuellement</h5>
                            <div class="alert alert-secondary">
                                <code class="fs-5">{{ $secret }}</code>
                            </div>

                            <h5 class="mt-4 mb-3">3. Codes de récupération</h5>
                            <div class="alert alert-warning">
                                <strong>Important !</strong> Sauvegardez ces codes dans un endroit sûr.
                            </div>
                            <div class="bg-light p-3 rounded">
                                <div class="row g-2">
                                    @foreach(array_chunk($recovery_codes, 4) as $chunk)
                                    <div class="col-6">
                                        @foreach($chunk as $code)
                                        <div><code>{{ $code }}</code></div>
                                        @endforeach
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h5 class="mb-3">4. Vérifiez votre configuration</h5>
                    <form method="POST" action="{{ route('2fa.confirm') }}">
                        @csrf
                        <div class="row align-items-end">
                            <div class="col-md-6">
                                <label class="form-label">Entrez le code à 6 chiffres</label>
                                <input 
                                    type="text" 
                                    name="code" 
                                    class="form-control form-control-modern @error('code') is-invalid @enderror" 
                                    placeholder="000000"
                                    maxlength="6"
                                    required
                                    autofocus>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <x-button-gradient type="success" icon="bi-shield-check">
                                    Activer le 2FA
                                </x-button-gradient>
                                <a href="{{ route('account.security') }}" class="btn btn-outline-secondary">
                                    Annuler
                                </a>
                            </div>
                        </div>
                    </form>
                </x-premium-card>
            </div>
        </div>
    </section>
</main>
@endsection
