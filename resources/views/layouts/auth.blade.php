<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>@yield('title', 'Connexion') - PSSP IMPACT+</title>
    <link href="{{ asset('backend/assets/img/favicon.png') }}" rel="icon">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --pi-ink: #1d2622;
            --pi-ink-soft: #5b6a62;
            --pi-paper: #f8faf9;
            --pi-card: #ffffff;
            --pi-green-900: #27443a;
            --pi-green-800: #35604d;
            --pi-green-600: #3f7a60;
            --pi-green-500: #549678;
            --pi-mint: #a8cfba;
            --pi-line: #dfe7e2;
            --pi-danger: #b3372f;
            --pi-font: 'Plus Jakarta Sans', 'Segoe UI', system-ui, sans-serif;
        }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            font-family: var(--pi-font);
            color: var(--pi-ink);
            background: var(--pi-paper);
        }

        .auth-shell {
            display: grid;
            grid-template-columns: minmax(360px, 42%) 1fr;
            min-height: 100vh;
        }

        /* ============ Panneau marque (sobre) ============ */
        .auth-brand {
            position: relative;
            overflow: hidden;
            background: linear-gradient(165deg, var(--pi-green-900) 0%, var(--pi-green-800) 100%);
            color: #eef4f0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: clamp(28px, 4vw, 56px);
        }

        .auth-brand::after {
            content: "";
            position: absolute;
            width: 560px;
            height: 560px;
            right: -220px;
            bottom: -220px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(168, 207, 186, .12), transparent 65%);
            pointer-events: none;
        }

        .brand-mark {
            display: flex;
            align-items: center;
            gap: 13px;
            position: relative;
            z-index: 1;
        }

        .brand-mark .glyph {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            font-size: 22px;
            color: var(--pi-green-800);
            background: #eef4f0;
        }

        .brand-mark .name {
            font-weight: 800;
            font-size: 1.2rem;
            letter-spacing: .01em;
        }

        .brand-mark .name small {
            display: block;
            font-weight: 500;
            font-size: .72rem;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: rgba(238, 244, 240, .6);
        }

        .brand-hero { position: relative; z-index: 1; max-width: 26rem; }

        .brand-hero h1 {
            font-size: clamp(1.7rem, 2.8vw, 2.3rem);
            font-weight: 700;
            line-height: 1.2;
            margin: 0 0 14px;
        }

        .brand-hero h1 em {
            font-style: normal;
            color: var(--pi-mint);
        }

        .brand-hero p {
            color: rgba(238, 244, 240, .72);
            font-size: .98rem;
            line-height: 1.65;
            margin: 0;
        }

        .brand-foot {
            position: relative;
            z-index: 1;
            font-size: .8rem;
            color: rgba(238, 244, 240, .5);
        }

        /* ============ Panneau formulaire ============ */
        .auth-form-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(24px, 4vw, 56px);
        }

        .auth-card {
            width: 100%;
            max-width: @yield('card-width', '430px');
            animation: pi-rise .45s ease-out both;
        }

        @keyframes pi-rise {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: none; }
        }

        .auth-title {
            font-weight: 700;
            font-size: 1.55rem;
            margin: 0 0 6px;
        }

        .auth-sub {
            color: var(--pi-ink-soft);
            font-size: .95rem;
            margin: 0 0 26px;
        }

        .pi-field { margin-bottom: 18px; }

        .pi-field label {
            display: block;
            font-weight: 600;
            font-size: .86rem;
            margin-bottom: 7px;
        }

        .pi-input-group { position: relative; }

        .pi-input-group > i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #93a49a;
            font-size: 1rem;
            pointer-events: none;
        }

        .pi-input {
            width: 100%;
            padding: 12px 14px 12px 42px;
            font: inherit;
            font-size: .95rem;
            color: var(--pi-ink);
            background: var(--pi-card);
            border: 1.5px solid var(--pi-line);
            border-radius: 10px;
            outline: none;
            transition: border-color .18s, box-shadow .18s;
        }

        .pi-input::placeholder { color: #aab8af; }

        .pi-input:focus {
            border-color: var(--pi-green-600);
            box-shadow: 0 0 0 3px rgba(63, 122, 96, .12);
        }

        .pi-input.is-invalid { border-color: var(--pi-danger); }

        .pi-toggle-eye {
            position: absolute;
            right: 6px;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            color: #7f9187;
            padding: 8px 10px;
            border-radius: 8px;
            cursor: pointer;
        }

        .pi-toggle-eye:hover { color: var(--pi-green-800); background: rgba(63, 122, 96, .08); }

        .pi-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 4px 0 22px;
            font-size: .88rem;
        }

        .pi-check { display: flex; align-items: center; gap: 8px; color: var(--pi-ink-soft); }

        .pi-check input {
            width: 16px;
            height: 16px;
            accent-color: var(--pi-green-600);
        }

        a.pi-link {
            color: var(--pi-green-600);
            font-weight: 600;
            text-decoration: none;
        }

        a.pi-link:hover { color: var(--pi-green-800); text-decoration: underline; }

        .pi-btn {
            width: 100%;
            padding: 13px 18px;
            font: inherit;
            font-size: .96rem;
            font-weight: 700;
            color: #fff;
            background: var(--pi-green-600);
            border: 0;
            border-radius: 10px;
            cursor: pointer;
            transition: background .15s;
        }

        .pi-btn:hover { background: var(--pi-green-800); }

        .pi-btn[disabled] { opacity: .7; cursor: progress; }

        .pi-btn .spinner {
            display: none;
            width: 1rem;
            height: 1rem;
            border: 2px solid rgba(255, 255, 255, .4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: pi-spin 0.7s linear infinite;
            vertical-align: -2px;
            margin-right: 8px;
        }

        @keyframes pi-spin { to { transform: rotate(360deg); } }

        .pi-btn.loading .spinner { display: inline-block; }

        .auth-alt {
            text-align: center;
            margin-top: 24px;
            font-size: .92rem;
            color: var(--pi-ink-soft);
        }

        .pi-alert {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            padding: 13px 15px;
            border-radius: 10px;
            font-size: .9rem;
            line-height: 1.5;
            margin-bottom: 20px;
        }

        .pi-alert i { margin-top: 1px; }

        .pi-alert.success { background: #ecf4ee; border: 1px solid #cfe3d5; color: #35604d; }
        .pi-alert.error { background: #fbeeed; border: 1px solid #efc9c5; color: #8c2b24; }

        .pi-hint { font-size: .8rem; color: var(--pi-ink-soft); margin-top: 6px; }

        .auth-mobile-brand { display: none; }

        @media (max-width: 900px) {
            .auth-shell { grid-template-columns: 1fr; }
            .auth-brand { display: none; }

            .auth-mobile-brand {
                display: flex;
                align-items: center;
                gap: 10px;
                justify-content: center;
                margin-bottom: 26px;
            }

            .auth-mobile-brand .glyph {
                width: 40px;
                height: 40px;
                border-radius: 10px;
                display: grid;
                place-items: center;
                font-size: 20px;
                color: #fff;
                background: var(--pi-green-600);
            }

            .auth-mobile-brand .name {
                font-weight: 800;
                font-size: 1.1rem;
                color: var(--pi-green-900);
            }
        }
    </style>
    @yield('styles')
</head>

<body>
    <div class="auth-shell">
        <aside class="auth-brand">
            <div class="brand-mark">
                <div class="glyph"><i class="bi bi-recycle"></i></div>
                <div class="name">
                    PSSP IMPACT+
                    <small>Gestion des déchets biomédicaux</small>
                </div>
            </div>

            <div class="brand-hero">
                <h1>La traçabilité des DBM, <em>du site à la facture</em>.</h1>
                <p>Collectes, validations signées, facturation et rapports pour vos
                    établissements de santé, en toute conformité.</p>
            </div>

            <div class="brand-foot">
                &copy; {{ date('Y') }} PSSP IMPACT+ &middot; Sèmè-Kpodji, République du Bénin
            </div>
        </aside>

        <main class="auth-form-wrap">
            <div class="auth-card">
                <div class="auth-mobile-brand">
                    <div class="glyph"><i class="bi bi-recycle"></i></div>
                    <div class="name">PSSP IMPACT+</div>
                </div>

                @yield('content')
            </div>
        </main>
    </div>

    <script>
        // Afficher / masquer les mots de passe
        document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var input = document.getElementById(btn.getAttribute('data-toggle-password'));
                if (!input) return;
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.querySelector('i').className = show ? 'bi bi-eye' : 'bi bi-eye-slash';
            });
        });

        // État de chargement au submit (évite les doubles envois)
        document.querySelectorAll('form[data-loading]').forEach(function (form) {
            form.addEventListener('submit', function () {
                var btn = form.querySelector('button[type="submit"]');
                if (btn) { btn.classList.add('loading'); btn.disabled = true; }
            });
        });
    </script>
    @yield('scripts')
</body>

</html>
