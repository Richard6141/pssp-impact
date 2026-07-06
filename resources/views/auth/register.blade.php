@extends('layouts.auth')

@section('title', 'Créer un compte')

@section('card-width', '520px')

@section('content')
    <h2 class="auth-title">Créer votre compte</h2>
    <p class="auth-sub">Rejoignez la plateforme de gestion des déchets biomédicaux.</p>

    @if ($errors->any())
        <div class="pi-alert error">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>
                @foreach ($errors->all() as $error)
                    {{ $error }}@if(!$loop->last)<br>@endif
                @endforeach
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('register.store') }}" data-loading>
        @csrf

        <div class="pi-grid-2">
            <div class="pi-field">
                <label for="firstname">Prénom</label>
                <div class="pi-input-group">
                    <i class="bi bi-person"></i>
                    <input type="text" name="firstname" id="firstname"
                        class="pi-input @error('firstname') is-invalid @enderror" placeholder="Prénom"
                        value="{{ old('firstname') }}" required autofocus autocomplete="given-name">
                </div>
            </div>

            <div class="pi-field">
                <label for="lastname">Nom</label>
                <div class="pi-input-group">
                    <i class="bi bi-person"></i>
                    <input type="text" name="lastname" id="lastname"
                        class="pi-input @error('lastname') is-invalid @enderror" placeholder="Nom"
                        value="{{ old('lastname') }}" required autocomplete="family-name">
                </div>
            </div>
        </div>

        <div class="pi-field">
            <label for="username">Nom d'utilisateur</label>
            <div class="pi-input-group">
                <i class="bi bi-at"></i>
                <input type="text" name="username" id="username"
                    class="pi-input @error('username') is-invalid @enderror" placeholder="ex. j.dupont"
                    value="{{ old('username') }}" required autocomplete="username">
            </div>
        </div>

        <div class="pi-field">
            <label for="email">Adresse e-mail</label>
            <div class="pi-input-group">
                <i class="bi bi-envelope"></i>
                <input type="email" name="email" id="email" class="pi-input @error('email') is-invalid @enderror"
                    placeholder="nom@domaine.com" value="{{ old('email') }}" required autocomplete="email">
            </div>
            <div class="pi-hint">Un e-mail de confirmation vous sera envoyé à cette adresse.</div>
        </div>

        <div class="pi-field">
            <label for="password">Mot de passe</label>
            <div class="pi-input-group">
                <i class="bi bi-lock"></i>
                <input type="password" name="password" id="password" class="pi-input" style="padding-right: 46px;"
                    placeholder="10 caractères minimum" required minlength="10" autocomplete="new-password">
                <button type="button" class="pi-toggle-eye" data-toggle-password="password" aria-label="Afficher le mot de passe">
                    <i class="bi bi-eye-slash"></i>
                </button>
            </div>
            <div class="pi-strength" aria-hidden="true">
                <span></span><span></span><span></span><span></span>
            </div>
            <div class="pi-hint">Au moins 10 caractères, avec majuscules, minuscules et chiffres.</div>
        </div>

        <div class="pi-field">
            <label for="password_confirmation">Confirmer le mot de passe</label>
            <div class="pi-input-group">
                <i class="bi bi-lock-fill"></i>
                <input type="password" name="password_confirmation" id="password_confirmation" class="pi-input"
                    style="padding-right: 46px;" placeholder="Répétez le mot de passe" required minlength="10"
                    autocomplete="new-password">
                <button type="button" class="pi-toggle-eye" data-toggle-password="password_confirmation" aria-label="Afficher le mot de passe">
                    <i class="bi bi-eye-slash"></i>
                </button>
            </div>
        </div>

        <button type="submit" class="pi-btn">
            <span class="spinner"></span>Créer mon compte
        </button>
    </form>

    <p class="auth-alt">
        Déjà inscrit&nbsp;?
        <a href="{{ route('login') }}" class="pi-link">Se connecter</a>
    </p>
@endsection

@section('styles')
    <style>
        .pi-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        @media (max-width: 520px) {
            .pi-grid-2 { grid-template-columns: 1fr; gap: 0; }
        }

        .pi-strength {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 6px;
            margin-top: 9px;
        }

        .pi-strength span {
            height: 5px;
            border-radius: 3px;
            background: var(--pi-line);
            transition: background .25s;
        }

        .pi-strength.s1 span:nth-child(-n+1) { background: #d8574c; }
        .pi-strength.s2 span:nth-child(-n+2) { background: #e2a63b; }
        .pi-strength.s3 span:nth-child(-n+3) { background: #7dbb51; }
        .pi-strength.s4 span:nth-child(-n+4) { background: var(--pi-green-600); }
    </style>
@endsection

@section('scripts')
    <script>
        // Indicateur de robustesse du mot de passe
        var pwd = document.getElementById('password');
        var meter = document.querySelector('.pi-strength');

        pwd.addEventListener('input', function () {
            var v = pwd.value;
            var score = 0;
            if (v.length >= 10) score++;
            if (/[a-z]/.test(v) && /[A-Z]/.test(v)) score++;
            if (/\d/.test(v)) score++;
            if (/[^A-Za-z0-9]/.test(v) && v.length >= 12) score++;
            meter.className = 'pi-strength' + (score ? ' s' + score : '');
        });
    </script>
@endsection
