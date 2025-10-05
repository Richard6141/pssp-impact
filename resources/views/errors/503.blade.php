<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance en cours</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
            padding: 60px 40px;
            text-align: center;
            animation: fadeIn 0.6s ease-out;
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

        .icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 30px;
            position: relative;
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

        .icon svg {
            width: 100%;
            height: 100%;
            fill: #667eea;
        }

        h1 {
            color: #2d3748;
            font-size: 2.5rem;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .message {
            color: #4a5568;
            font-size: 1.1rem;
            line-height: 1.8;
            margin-bottom: 30px;
        }

        .info-box {
            background: #f7fafc;
            border-left: 4px solid #667eea;
            padding: 20px;
            border-radius: 8px;
            margin: 30px 0;
            text-align: left;
        }

        .info-box p {
            color: #4a5568;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-box p:last-child {
            margin-bottom: 0;
        }

        .spinner {
            display: inline-block;
            width: 40px;
            height: 40px;
            margin-top: 20px;
        }

        .spinner:after {
            content: " ";
            display: block;
            width: 32px;
            height: 32px;
            margin: 8px;
            border-radius: 50%;
            border: 4px solid #667eea;
            border-color: #667eea transparent #667eea transparent;
            animation: spinner 1.2s linear infinite;
        }

        @keyframes spinner {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .contact {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid #e2e8f0;
        }

        .contact p {
            color: #718096;
            font-size: 0.9rem;
        }

        .contact a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .contact a:hover {
            text-decoration: underline;
        }

        @media (max-width: 600px) {
            .container {
                padding: 40px 30px;
            }

            h1 {
                font-size: 2rem;
            }

            .message {
                font-size: 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="icon">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" />
            </svg>
        </div>

        <h1>Maintenance en cours</h1>

        <p class="message">
            Notre site est temporairement indisponible pour cause de maintenance planifiée.
            Nous effectuons des mises à jour importantes pour améliorer votre expérience.
        </p>

        <div class="info-box">
            <p>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path
                        d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13H11v6l5.2 3.2.8-1.3-4.5-2.7V7z" />
                </svg>
                <strong>Durée estimée :</strong> Quelques minutes
            </p>
            <p>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path
                        d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z" />
                </svg>
                <strong>Retour prévu :</strong> Sous peu
            </p>
        </div>

        <div class="spinner"></div>

        <div class="contact">
            <p>Pour toute urgence, contactez-nous à <a href="mailto:richardsomasse@gmail.com">Le concepteur</a></p>
        </div>
    </div>

    <script>
        // Rafraîchir la page toutes les 30 secondes pour vérifier si la maintenance est terminée
        setTimeout(function() {
            location.reload();
        }, 30000);
    </script>
</body>

</html>