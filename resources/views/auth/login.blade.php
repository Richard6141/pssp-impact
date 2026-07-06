@extends('layouts.auth')

@section('title', 'Connexion')

@section('content')
    <h2 class="auth-title">Bon retour&nbsp;!</h2>
    <p class="auth-sub">Connectez-vous pour accéder à votre espace de gestion.</p>

    @if (session('status'))
        <div class="pi-alert success">
            <i class="bi bi-check-circle-fill"></i>
            <div>{{ session('status') }}</div>
        </div>
    @endif

    @if (session('success'))
        <div class="pi-alert success">
            <i class="bi bi-check-circle-fill"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if ($errors->any())
        <div class="pi-alert error">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>{{ $errors->first() }}</div>
        </div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" data-loading>
        @csrf

        <div class="pi-field">
            <label for="login">Email ou nom d'utilisateur</label>
            <div class="pi-input-group">
                <i class="bi bi-person"></i>
                <input type="text" name="login" id="login" class="pi-input @error('login') is-invalid @enderror"
                    placeholder="ex. j.dupont ou nom@domaine.com" value="{{ old('login') }}" required autofocus
                    autocomplete="username">
            </div>
        </div>

        <div class="pi-field">
            <label for="password">Mot de passe</label>
            <div class="pi-input-group">
                <i class="bi bi-lock"></i>
                <input type="password" name="password" id="password" class="pi-input" style="padding-right: 46px;"
                    placeholder="Votre mot de passe" required autocomplete="current-password">
                <button type="button" class="pi-toggle-eye" data-toggle-password="password" aria-label="Afficher le mot de passe">
                    <i class="bi bi-eye-slash"></i>
                </button>
            </div>
        </div>

        <div class="pi-row">
            <label class="pi-check">
                <input type="checkbox" name="remember" id="remember">
                Se souvenir de moi
            </label>
            <a href="{{ route('password.request') }}" class="pi-link">Mot de passe oublié&nbsp;?</a>
        </div>

        <button type="submit" class="pi-btn">
            <span class="spinner"></span>Se connecter
        </button>
    </form>

    <p class="auth-alt">
        Pas encore de compte&nbsp;?
        <a href="{{ route('register') }}" class="pi-link">Créer un compte</a>
    </p>
@endsection
