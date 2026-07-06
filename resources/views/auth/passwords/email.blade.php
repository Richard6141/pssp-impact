@extends('layouts.auth')

@section('title', 'Mot de passe oublié')

@section('content')
    <h2 class="auth-title">Mot de passe oublié&nbsp;?</h2>
    <p class="auth-sub">Indiquez l'adresse e-mail de votre compte&nbsp;: nous vous enverrons un lien
        sécurisé pour définir un nouveau mot de passe.</p>

    @if (session('status'))
        <div class="pi-alert success">
            <i class="bi bi-envelope-check-fill"></i>
            <div>{{ session('status') }}</div>
        </div>
    @endif

    @if ($errors->any())
        <div class="pi-alert error">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>{{ $errors->first() }}</div>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" data-loading>
        @csrf

        <div class="pi-field">
            <label for="email">Adresse e-mail</label>
            <div class="pi-input-group">
                <i class="bi bi-envelope"></i>
                <input type="email" name="email" id="email" class="pi-input @error('email') is-invalid @enderror"
                    placeholder="nom@domaine.com" value="{{ old('email') }}" required autofocus
                    autocomplete="email">
            </div>
            <div class="pi-hint">Le lien est valide 60 minutes. Pensez à vérifier votre dossier spam.</div>
        </div>

        <button type="submit" class="pi-btn">
            <span class="spinner"></span>Envoyer le lien de réinitialisation
        </button>
    </form>

    <p class="auth-alt">
        <a href="{{ route('login') }}" class="pi-link"><i class="bi bi-arrow-left"></i> Retour à la connexion</a>
    </p>
@endsection
