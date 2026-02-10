@extends('layouts.back')

@section('content')
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Paramètres de Sécurité</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
                <li class="breadcrumb-item">Compte</li>
                <li class="breadcrumb-item active">Sécurité</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-8">
                {{-- Authentification à deux facteurs --}}
                <x-premium-card title="Authentification à deux facteurs (2FA)" class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="mb-2">
                                <i class="bi bi-shield-lock text-primary me-2"></i>
                                Protection renforcée de votre compte
                            </h5>
                            <p class="text-muted mb-0">
                                Ajoutez une couche de sécurité supplémentaire en exigeant un code de vérification lors de la connexion.
                            </p>
                        </div>
                        <div>
                            @if($tfaEnabled)
                                <x-badge-modern type="success" :glow="true">
                                    <i class="bi bi-check-circle"></i> Activé
                                </x-badge-modern>
                            @else
                                <x-badge-modern type="secondary">
                                    <i class="bi bi-x-circle"></i> Désactivé
                                </x-badge-modern>
                            @endif
                        </div>
                    </div>

                    <hr>

                    @if($tfaEnabled)
                        <div class="alert alert-success">
                            <i class="bi bi-shield-check me-2"></i>
                            Votre compte est protégé par l'authentification à deux facteurs.
                        </div>

                        <div class="d-flex gap-2">
                            <a href="{{ route('2fa.recovery-codes') }}" class="btn btn-outline-primary">
                                <i class="bi bi-key"></i> Régénérer les codes de récupération
                            </a>

                            <form method="POST" action="{{ route('2fa.disable') }}" class="d-inline">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Entrez votre mot de passe pour désactiver</label>
                                    <input 
                                        type="password" 
                                        name="password" 
                                        class="form-control form-control-modern @error('password') is-invalid @enderror"
                                        required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Désactiver le 2FA ?')">
                                    <i class="bi bi-shield-x"></i> Désactiver le 2FA
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Votre compte n'est pas protégé par l'authentification à deux facteurs. Nous vous recommandons fortement de l'activer.
                        </div>

                        <a href="{{ route('2fa.enable') }}" class="btn btn-gradient-primary">
                            <i class="bi bi-shield-plus"></i> Activer le 2FA
                        </a>
                    @endif
                </x-premium-card>

                {{-- Sessions actives --}}
                <x-premium-card title="Sessions actives" class="mb-4">
                    <p class="text-muted">
                        Gérez les appareils connectés à votre compte.
                    </p>

                    <a href="{{ route('admin.sessions.index') }}" class="btn btn-outline-primary">
                        <i class="bi bi-laptop"></i> Voir les sessions actives
                    </a>
                </x-premium-card>

                {{-- Mot de passe --}}
                <x-premium-card title="Mot de passe">
                    <p class="text-muted mb-3">
                        Assurez-vous que votre compte utilise un mot de passe long et aléatoire pour rester sécurisé.
                    </p>

                    <a href="{{ route('profile.show') }}" class="btn btn-outline-primary">
                        <i class="bi bi-key"></i> Modifier le mot de passe
                    </a>
                </x-premium-card>
            </div>

            <div class="col-lg-4">
                <x-premium-card title="Conseils de sécurité" :gradient="true">
                    <div class="mb-3">
                        <h6><i class="bi bi-check-circle text-success"></i> Activez le 2FA</h6>
                        <small class="text-muted">
                            L'authentification à deux facteurs protège votre compte même si votre mot de passe est compromis.
                        </small>
                    </div>

                    <div class="mb-3">
                        <h6><i class="bi bi-check-circle text-success"></i> Mot de passe fort</h6>
                        <small class="text-muted">
                            Utilisez au moins 8 caractères avec des majuscules, minuscules et chiffres.
                        </small>
                    </div>

                    <div class="mb-3">
                        <h6><i class="bi bi-check-circle text-success"></i> Surveillez vos sessions</h6>
                        <small class="text-muted">
                            Vérifiez régulièrement les appareils connectés à votre compte.
                        </small>
                    </div>

                    <div>
                        <h6><i class="bi bi-check-circle text-success"></i> Codes de récupération</h6>
                        <small class="text-muted">
                            Sauvegardez vos codes de récupération dans un endroit sûr.
                        </small>
                    </div>
                </x-premium-card>
            </div>
        </div>
    </section>
</main>
@endsection
