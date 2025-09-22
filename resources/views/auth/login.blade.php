<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Connexion - Gestion Déchets Médicaux</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
    body {
        background: url("{{ asset('backend/assets/img/background.jpg') }}") no-repeat center center fixed;
        background-size: cover;
        font-family: 'Segoe UI', sans-serif;
    }

    .card {
        border-radius: 15px;
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.3);
        backdrop-filter: blur(6px);
        background: rgba(255, 255, 255, 0.9);
    }

    .btn-custom {
        background: linear-gradient(90deg, #0d6efd, #20c997);
        color: #fff;
        border: none;
    }

    .btn-custom:hover {
        opacity: 0.9;
    }

    .form-control:focus {
        border-color: #20c997;
        box-shadow: 0 0 0 0.2rem rgba(32, 201, 151, 0.25);
    }

    .logo {
        font-weight: bold;
        font-size: 1.4rem;
        color: #0d6efd;
    }
    </style>
</head>

<body>
    <main class="d-flex align-items-center justify-content-center min-vh-100">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-5">
                    <div class="card p-4">
                        <div class="text-center mb-3">
                            <i class="bi bi-recycle" style="font-size: 3rem; color: #20c997;"></i>
                            <h4 class="mt-2 logo">Gestion Déchets Médicaux</h4>
                            <p class="text-muted">Connectez-vous à votre compte</p>
                        </div>

                        <!-- Formulaire de connexion -->
                        <form method="POST" action="{{ route('login.store') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label">Adresse Email</label>
                                <input type="text" name="login" id="email" class="form-control" required autofocus>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Mot de passe</label>
                                <input type="password" name="password" id="password" class="form-control" required>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                    <label class="form-check-label" for="remember">Se souvenir de moi</label>
                                </div>
                                <a href="{{ route('password.request') }}" class="text-decoration-none">Mot de passe
                                    oublié ?</a>
                            </div>

                            <button type="submit" class="btn btn-custom w-100">Se connecter</button>
                        </form>

                        <div class="text-center mt-3">
                            <p class="mb-0">Pas encore de compte ?
                                <a href="{{ route('register.store') }}" class="text-decoration-none">Créer un compte</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>