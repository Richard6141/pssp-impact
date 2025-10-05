<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Accès refusé | PSSP IMPACT+</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        position: relative;
    }

    .error-container {
        text-align: center;
        color: white;
        padding: 2rem;
        position: relative;
        z-index: 10;
        animation: fadeIn 0.8s ease-in;
        max-width: 800px;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .lock-container {
        position: relative;
        display: inline-block;
        margin-bottom: 2rem;
    }

    .lock-icon {
        font-size: 8rem;
        animation: shake 0.8s ease-in-out;
        filter: drop-shadow(0 10px 30px rgba(0, 0, 0, 0.3));
    }

    @keyframes shake {

        0%,
        100% {
            transform: translateX(0);
        }

        10%,
        30%,
        50%,
        70%,
        90% {
            transform: translateX(-10px);
        }

        20%,
        40%,
        60%,
        80% {
            transform: translateX(10px);
        }
    }

    .error-code {
        font-size: 6rem;
        font-weight: 700;
        text-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        margin-bottom: 1rem;
    }

    .error-title {
        font-size: 2.5rem;
        font-weight: 600;
        margin-bottom: 1rem;
        text-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .error-message {
        font-size: 1.2rem;
        margin-bottom: 2rem;
        opacity: 0.95;
        line-height: 1.6;
    }

    .user-info {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .user-info h5 {
        margin-bottom: 1rem;
        font-size: 1.1rem;
    }

    .user-role {
        display: inline-block;
        background: rgba(255, 255, 255, 0.25);
        padding: 0.5rem 1.5rem;
        border-radius: 25px;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .required-permission {
        background: rgba(255, 255, 255, 0.2);
        padding: 1rem;
        border-radius: 15px;
        margin-top: 1rem;
        font-size: 0.95rem;
    }

    .btn-home {
        background: white;
        color: #f5576c;
        padding: 1rem 3rem;
        border-radius: 50px;
        font-weight: 600;
        text-decoration: none;
        display: inline-block;
        transition: all 0.3s ease;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    .btn-home:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        color: #f093fb;
    }

    .btn-back {
        background: transparent;
        color: white;
        padding: 1rem 3rem;
        border: 2px solid white;
        border-radius: 50px;
        font-weight: 600;
        text-decoration: none;
        display: inline-block;
        margin-left: 1rem;
        transition: all 0.3s ease;
    }

    .btn-back:hover {
        background: white;
        color: #f5576c;
        transform: translateY(-3px);
    }

    .help-section {
        margin-top: 3rem;
        padding-top: 2rem;
        border-top: 1px solid rgba(255, 255, 255, 0.2);
    }

    .help-section h4 {
        font-size: 1.2rem;
        margin-bottom: 1.5rem;
    }

    .help-card {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: 15px;
        padding: 1.5rem;
        margin: 1rem 0;
        border: 1px solid rgba(255, 255, 255, 0.15);
        transition: all 0.3s ease;
    }

    .help-card:hover {
        background: rgba(255, 255, 255, 0.15);
        transform: translateY(-2px);
    }

    .help-card i {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    .help-card h6 {
        font-size: 1.1rem;
        margin-bottom: 0.5rem;
    }

    .help-card p {
        font-size: 0.9rem;
        opacity: 0.9;
        margin: 0;
    }

    /* Particles background */
    .particles {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
    }

    .particle {
        position: absolute;
        background: rgba(255, 255, 255, 0.08);
        border-radius: 50%;
        animation: particleFloat 20s infinite ease-in-out;
    }

    @keyframes particleFloat {

        0%,
        100% {
            transform: translateY(0) translateX(0) rotate(0deg);
        }

        50% {
            transform: translateY(-150px) translateX(150px) rotate(180deg);
        }
    }

    @media (max-width: 768px) {
        .lock-icon {
            font-size: 5rem;
        }

        .error-code {
            font-size: 4rem;
        }

        .error-title {
            font-size: 1.8rem;
        }

        .error-message {
            font-size: 1rem;
        }

        .btn-home,
        .btn-back {
            padding: 0.8rem 2rem;
            font-size: 0.9rem;
            display: block;
            margin: 0.5rem auto;
        }

        .help-card {
            padding: 1rem;
        }
    }
    </style>
</head>

<body>
    <!-- Particles Background -->
    <div class="particles">
        <div class="particle" style="width: 150px; height: 150px; top: 5%; left: 15%; animation-delay: 0s;"></div>
        <div class="particle" style="width: 80px; height: 80px; top: 25%; left: 75%; animation-delay: 3s;"></div>
        <div class="particle" style="width: 120px; height: 120px; top: 55%; left: 5%; animation-delay: 6s;"></div>
        <div class="particle" style="width: 60px; height: 60px; top: 75%; left: 65%; animation-delay: 2s;"></div>
        <div class="particle" style="width: 100px; height: 100px; top: 15%; left: 55%; animation-delay: 4s;"></div>
    </div>

    <div class="error-container">
        <div class="lock-container">
            <div class="lock-icon">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
        </div>

        <div class="error-code">403</div>

        <h1 class="error-title">Accès refusé</h1>

        <p class="error-message">
            Désolé, vous n'avez pas les permissions nécessaires pour accéder à cette ressource.
        </p>

        @auth
        <div class="user-info">
            <h5><i class="bi bi-person-circle me-2"></i>Informations de votre compte</h5>
            <div class="user-role">
                <i class="bi bi-award me-1"></i>
                {{ auth()->user()->getRoleNames()->first() ?? 'Aucun rôle' }}
            </div>
            <div class="mt-2">
                <small>
                    <i class="bi bi-person me-1"></i>
                    {{ auth()->user()->firstname }} {{ auth()->user()->lastname }}
                </small>
            </div>
            @if(auth()->user()->site)
            <div class="mt-1">
                <small>
                    <i class="bi bi-geo-alt me-1"></i>
                    {{ auth()->user()->site->nom }}
                </small>
            </div>
            @endif

            <div class="required-permission">
                <i class="bi bi-info-circle me-1"></i>
                Cette page nécessite des permissions spécifiques que votre rôle actuel ne possède pas.
            </div>
        </div>
        @endauth

        <div class="mt-4">
            <a href="{{ route('dashboard') }}" class="btn-home">
                <i class="bi bi-house-door me-2"></i>Retour à l'accueil
            </a>
            <a href="javascript:history.back()" class="btn-back">
                <i class="bi bi-arrow-left me-2"></i>Page précédente
            </a>
        </div>

        <div class="help-section">
            <h4><i class="bi bi-question-circle me-2"></i>Besoin d'aide ?</h4>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="help-card">
                        <i class="bi bi-person-badge"></i>
                        <h6>Contactez votre administrateur</h6>
                        <p>Si vous pensez que c'est une erreur, contactez l'administrateur système.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="help-card">
                        <i class="bi bi-file-text"></i>
                        <h6>Vérifiez vos permissions</h6>
                        <p>Assurez-vous que votre rôle dispose des permissions nécessaires.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="help-card">
                        <i class="bi bi-arrow-clockwise"></i>
                        <h6>Reconnectez-vous</h6>
                        <p>Parfois une simple reconnexion peut résoudre le problème.</p>
                    </div>
                </div>
            </div>

            @auth
            @hasrole('Super Admin|Administrateur')
            <div class="mt-3">
                <a href="{{ route('users.index') }}" class="text-white text-decoration-underline">
                    <i class="bi bi-gear me-1"></i>
                    Gérer les utilisateurs et permissions
                </a>
            </div>
            @endhasrole
            @endauth
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>