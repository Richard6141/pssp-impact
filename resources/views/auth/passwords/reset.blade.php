@extends('layouts.auth')

@section('title', 'Nouveau mot de passe')

@section('content')
    <h2 class="auth-title">Définir un nouveau mot de passe</h2>
    <p class="auth-sub">Choisissez un mot de passe solide pour le compte <strong>{{ $email }}</strong>.</p>

    @if ($errors->any())
        <div class="pi-alert error">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>{{ $errors->first() }}</div>
        </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}" data-loading>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">

        <div class="pi-field">
            <label for="password">Nouveau mot de passe</label>
            <div class="pi-input-group">
                <i class="bi bi-lock"></i>
                <input type="password" name="password" id="password" class="pi-input" style="padding-right: 46px;"
                    placeholder="8 caractères minimum" required minlength="8" autocomplete="new-password" autofocus>
                <button type="button" class="pi-toggle-eye" data-toggle-password="password" aria-label="Afficher le mot de passe">
                    <i class="bi bi-eye-slash"></i>
                </button>
            </div>
            <div class="pi-hint" id="strength-hint">Utilisez au moins 8 caractères, avec majuscules, chiffres et symboles.</div>
        </div>

        <div class="pi-field">
            <label for="password_confirmation">Confirmer le mot de passe</label>
            <div class="pi-input-group">
                <i class="bi bi-lock-fill"></i>
                <input type="password" name="password_confirmation" id="password_confirmation" class="pi-input"
                    style="padding-right: 46px;" placeholder="Répétez le mot de passe" required minlength="8"
                    autocomplete="new-password">
                <button type="button" class="pi-toggle-eye" data-toggle-password="password_confirmation" aria-label="Afficher le mot de passe">
                    <i class="bi bi-eye-slash"></i>
                </button>
            </div>
        </div>

        <button type="submit" class="pi-btn">
            <span class="spinner"></span>Réinitialiser mon mot de passe
        </button>
    </form>

    <p class="auth-alt">
        <a href="{{ route('login') }}" class="pi-link"><i class="bi bi-arrow-left"></i> Retour à la connexion</a>
    </p>
@endsection
