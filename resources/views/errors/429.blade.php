<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>429 - Trop de requetes | PSSP IMPACT+</title>
    <link href="{{ asset('backend/assets/img/favicon.png') }}" rel="icon">
    <link href="{{ asset('backend/assets/img/apple-touch-icon.png') }}" rel="apple-touch-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #0d6efd;
            --warning: #ff8f00;
            --bg-1: #f6fbff;
            --bg-2: #e8f2ff;
            --text: #1c2a3a;
            --muted: #5f6f86;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 15% 20%, #d9ecff 0, transparent 45%),
                radial-gradient(circle at 85% 80%, #ffe6bf 0, transparent 40%),
                linear-gradient(145deg, var(--bg-1), var(--bg-2));
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .wrap {
            width: min(760px, 100%);
        }

        .card-429 {
            background: rgba(255, 255, 255, 0.88);
            border: 1px solid rgba(13, 110, 253, 0.15);
            border-radius: 20px;
            box-shadow: 0 18px 50px rgba(18, 43, 82, 0.12);
            padding: 34px 28px;
            backdrop-filter: blur(8px);
        }

        .badge-soft {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #fff2df;
            color: #8b5a00;
            border: 1px solid #ffd8a1;
            border-radius: 999px;
            padding: 8px 14px;
            font-size: 0.86rem;
            font-weight: 600;
        }

        .code {
            margin-top: 14px;
            margin-bottom: 0;
            font-size: clamp(2.4rem, 8vw, 4.3rem);
            line-height: 1;
            letter-spacing: 0.02em;
            color: var(--primary);
            font-weight: 700;
        }

        .title {
            margin-top: 8px;
            margin-bottom: 10px;
            font-size: clamp(1.2rem, 4vw, 1.8rem);
            font-weight: 600;
        }

        .desc {
            margin: 0;
            color: var(--muted);
            line-height: 1.65;
        }

        .tips {
            margin-top: 18px;
            margin-bottom: 0;
            color: var(--muted);
            padding-left: 20px;
        }

        .tips li {
            margin-bottom: 8px;
        }

        .actions {
            margin-top: 24px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .btn-main {
            border-radius: 12px;
            padding: 10px 16px;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <main class="wrap">
        <section class="card-429">
            <span class="badge-soft">
                <i class="bi bi-speedometer2"></i> Protection active
            </span>

            <h1 class="code">429</h1>
            <h2 class="title">Trop de requetes en peu de temps</h2>
            <p class="desc">
                Le systeme a temporairement limite les tentatives pour proteger le service.
                Patientez quelques instants puis reessayez.
            </p>

            <ul class="tips">
                <li>Attendez 1 a 2 minutes avant de renvoyer la requete.</li>
                <li>Evitez les rafraichissements rapides ou les clics repetes.</li>
                <li>Si le probleme persiste, contactez l'administration.</li>
            </ul>

            <div class="actions">
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-main">
                    <i class="bi bi-arrow-left"></i> Retour
                </a>
                @auth
                <a href="{{ route('dashboard') }}" class="btn btn-primary btn-main">
                    <i class="bi bi-grid"></i> Tableau de bord
                </a>
                @else
                <a href="{{ route('login') }}" class="btn btn-primary btn-main">
                    <i class="bi bi-box-arrow-in-right"></i> Aller a la connexion
                </a>
                @endauth
            </div>
        </section>
    </main>
</body>

</html>
