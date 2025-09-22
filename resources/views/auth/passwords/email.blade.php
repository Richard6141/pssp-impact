<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Mot de passe oublié</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light d-flex align-items-center justify-content-center min-vh-100">

    <div class="card shadow p-4" style="width: 400px;">
        <h4 class="text-center mb-3">Mot de passe oublié</h4>
        <p class="text-muted text-center">Entrez votre adresse email pour recevoir un lien de réinitialisation.</p>

        @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label">Adresse Email</label>
                <input type="email" name="email" id="email" class="form-control" required autofocus>
            </div>
            <button type="submit" class="btn btn-primary w-100">Envoyer le lien</button>
        </form>

        <div class="text-center mt-3">
            <a href="{{ route('login') }}">Retour à la connexion</a>
        </div>
    </div>

</body>

</html>