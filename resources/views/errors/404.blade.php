<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page non trouvée | PSSP IMPACT+</title>
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
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

    .error-code {
        font-size: 10rem;
        font-weight: 700;
        line-height: 1;
        text-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        margin-bottom: 1rem;
        animation: bounce 2s infinite;
    }

    @keyframes bounce {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-20px);
        }
    }

    .error-icon {
        font-size: 8rem;
        margin-bottom: 2rem;
        opacity: 0.9;
        animation: float 3s ease-in-out infinite;
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-15px);
        }
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
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }

    .btn-home {
        background: white;
        color: #667eea;
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
        color: #764ba2;
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
        color: #667eea;
        transform: translateY(-3px);
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
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        animation: particleFloat 15s infinite ease-in-out;
    }

    @keyframes particleFloat {

        0%,
        100% {
            transform: translateY(0) translateX(0);
        }

        50% {
            transform: translateY(-100px) translateX(100px);
        }
    }

    .suggestions {
        margin-top: 3rem;
        padding-top: 2rem;
        border-top: 1px solid rgba(255, 255, 255, 0.2);
    }

    .suggestions h4 {
        font-size: 1.2rem;
        margin-bottom: 1.5rem;
        opacity: 0.9;
    }

    .suggestion-links {
        display: flex;
        justify-content: center;
        gap: 1.5rem;
        flex-wrap: wrap;
    }

    .suggestion-link {
        background: rgba(255, 255, 255, 0.15);
        color: white;
        padding: 0.8rem 1.5rem;
        border-radius: 25px;
        text-decoration: none;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
    }

    .suggestion-link:hover {
        background: rgba(255, 255, 255, 0.25);
        color: white;
        transform: translateY(-2px);
    }

    @media (max-width: 768px) {
        .error-code {
            font-size: 6rem;
        }

        .error-icon {
            font-size: 5rem;
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

        .suggestion-links {
            flex-direction: column;
            align-items: center;
        }
    }
    </style>
</head>

<body>
    <!-- Particles Background -->
    <div class="particles">
        <div class="particle" style="width: 100px; height: 100px; top: 10%; left: 20%; animation-delay: 0s;"></div>
        <div class="particle" style="width: 60px; height: 60px; top: 30%; left: 80%; animation-delay: 2s;"></div>
        <div class="particle" style="width: 80px; height: 80px; top: 60%; left: 10%; animation-delay: 4s;"></div>
        <div class="particle" style="width: 40px; height: 40px; top: 80%; left: 70%; animation-delay: 1s;"></div>
        <div class="particle" style="width: 120px; height: 120px; top: 20%; left: 60%; animation-delay: 3s;"></div>
    </div>

    <div class="error-container">
        <div class="error-icon">
            <i class="bi bi-compass"></i>
        </div>

        <div class="error-code">404</div>

        <h1 class="error-title">Oups ! Page introuvable</h1>

        <p class="error-message">
            La page que vous recherchez semble avoir disparu. Elle a peut-être été déplacée ou n'existe plus.
        </p>

        <div class="mt-4">
            <a href="{{ route('dashboard') }}" class="btn-home">
                <i class="bi bi-house-door me-2"></i>Retour à l'accueil
            </a>
            <a href="javascript:history.back()" class="btn-back">
                <i class="bi bi-arrow-left me-2"></i>Page précédente
            </a>
        </div>

        <div class="suggestions">
            <h4>Liens utiles :</h4>
            <div class="suggestion-links">
                @auth
                @can('sites.view')
                <a href="{{ route('sites.index') }}" class="suggestion-link">
                    <i class="bi bi-building me-1"></i> Sites
                </a>
                @endcan
                @can('collectes.view')
                <a href="{{ route('collectes.index') }}" class="suggestion-link">
                    <i class="bi bi-truck me-1"></i> Collectes
                </a>
                @endcan
                @can('factures.view')
                <a href="{{ route('factures.index') }}" class="suggestion-link">
                    <i class="bi bi-receipt me-1"></i> Factures
                </a>
                @endcan
                <a href="{{ route('profile.show') }}" class="suggestion-link">
                    <i class="bi bi-person me-1"></i> Mon profil
                </a>
                @else
                <a href="{{ route('login') }}" class="suggestion-link">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Connexion
                </a>
                @endauth
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>