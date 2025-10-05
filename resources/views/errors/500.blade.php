<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Erreur serveur | PSSP IMPACT+</title>
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
        background: linear-gradient(135deg, #FF416C 0%, #FF4B2B 100%);
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

    .error-icon {
        font-size: 8rem;
        margin-bottom: 2rem;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.1);
        }
    }

    .error-code {
        font-size: 8rem;
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
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }

    .btn-home {
        background: white;
        color: #FF416C;
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
        color: #FF4B2B;
    }

    .info-box {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 2rem;
        margin-top: 3rem;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    @media (max-width: 768px) {
        .error-code {
            font-size: 5rem;
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
    }
    </style>
</head>

<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="bi bi-tools"></i>
        </div>

        <div class="error-code">500</div>

        <h1 class="error-title">Erreur serveur</h1>

        <p class="error-message">
            Quelque chose s'est mal passé de notre côté. Nous travaillons à résoudre le problème.
        </p>

        <div class="mt-4">
            <a href="{{ route('dashboard') }}" class="btn-home">
                <i class="bi bi-house-door me-2"></i>Retour à l'accueil
            </a>
        </div>

        <div class="info-box">
            <h5><i class="bi bi-info-circle me-2"></i>Que faire ?</h5>
            <ul class="text-start mt-3" style="max-width: 400px; margin: 0 auto;">
                <li class="mb-2">Actualisez la page dans quelques instants</li>
                <li class="mb-2">Vérifiez votre connexion internet</li>
                <li class="mb-2">Si le problème persiste, contactez le support</li>
            </ul>
        </div>
    </div>
</body>

</html>