<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>@yield('title', 'Connexion') - PSSP IMPACT+</title>
    <link href="{{ asset('backend/assets/img/favicon.png') }}" rel="icon">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,600;12..96,700;12..96,800&family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --pi-ink: #16211b;
            --pi-ink-soft: #4c5e54;
            --pi-paper: #f4f7f2;
            --pi-card: #ffffff;
            --pi-green-950: #0a2b1b;
            --pi-green-900: #0f3d26;
            --pi-green-800: #14522f;
            --pi-green-600: #1e7d47;
            --pi-green-500: #27995a;
            --pi-lime: #a3e635;
            --pi-teal: #2cb8a6;
            --pi-line: #dbe5dc;
            --pi-danger: #b3372f;
            --pi-font-display: 'Bricolage Grotesque', 'Segoe UI', system-ui, sans-serif;
            --pi-font-body: 'Instrument Sans', 'Segoe UI', system-ui, sans-serif;
        }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            font-family: var(--pi-font-body);
            color: var(--pi-ink);
            background: var(--pi-paper);
        }

        .auth-shell {
            display: grid;
            grid-template-columns: minmax(380px, 44%) 1fr;
            min-height: 100vh;
        }

        /* ============ Panneau marque ============ */
        .auth-brand {
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(1100px 600px at -10% -10%, rgba(44, 184, 166, .28), transparent 60%),
                radial-gradient(900px 700px at 110% 110%, rgba(163, 230, 53, .16), transparent 55%),
                linear-gradient(160deg, var(--pi-green-950) 0%, var(--pi-green-900) 55%, var(--pi-green-800) 100%);
            color: #eef7ef;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: clamp(28px, 4vw, 56px);
        }

        .auth-brand::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(255, 255, 255, .07) 1px, transparent 1.6px);
            background-size: 26px 26px;
            mask-image: linear-gradient(180deg, transparent, #000 30%, #000 70%, transparent);
            pointer-events: none;
        }

        .auth-brand::after {
            content: "";
            position: absolute;
            width: 520px;
            height: 520px;
            right: -180px;
            top: 24%;
            border: 1.5px dashed rgba(163, 230, 53, .35);
            border-radius: 50%;
            animation: pi-spin 90s linear infinite;
            pointer-events: none;
        }

        @keyframes pi-spin { to { transform: rotate(360deg); } }

        .brand-mark {
            display: flex;
            align-items: center;
            gap: 14px;
            position: relative;
            z-index: 1;
        }

        .brand-mark .glyph {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            font-size: 26px;
            color: var(--pi-green-950);
            background: linear-gradient(135deg, var(--pi-lime), var(--pi-teal));
            box-shadow: 0 10px 30px rgba(0, 0, 0, .35);
        }

        .brand-mark .name {
            font-family: var(--pi-font-display);
            font-weight: 800;
            font-size: 1.35rem;
            letter-spacing: .02em;
        }

        .brand-mark .name small {
            display: block;
            font-family: var(--pi-font-body);
            font-weight: 500;
            font-size: .74rem;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: rgba(238, 247, 239, .65);
        }

        .brand-hero { position: relative; z-index: 1; max-width: 30rem; }

        .brand-hero h1 {
            font-family: var(--pi-font-display);
            font-size: clamp(1.9rem, 3.2vw, 2.7rem);
            font-weight: 700;
            line-height: 1.12;
            margin: 0 0 14px;
        }

        .brand-hero h1 em {
            font-style: normal;
            color: var(--pi-lime);
        }

        .brand-hero p {
            color: rgba(238, 247, 239, .78);
            font-size: 1.02rem;
            line-height: 1.65;
            margin: 0 0 26px;
        }

        .brand-points { display: grid; gap: 12px; }

        .brand-points .point {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            padding: 12px 14px;
            border: 1px solid rgba(255, 255, 255, .12);
            border-radius: 12px;
            background: rgba(255, 255, 255, .05);
            backdrop-filter: blur(4px);
        }

        .brand-points .point i {
            color: var(--pi-lime);
            font-size: 1.1rem;
            margin-top: 2px;
        }

        .brand-points .point strong { display: block; font-size: .94rem; }
        .brand-points .point span { font-size: .84rem; color: rgba(238, 247, 239, .66); }

        .brand-foot {
            position: relative;
            z-index: 1;
            font-size: .8rem;
            color: rgba(238, 247, 239, .55);
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
            animation: pi-rise .55s cubic-bezier(.2, .8, .25, 1) both;
        }

        @keyframes pi-rise {
            from { opacity: 0; transform: translateY(18px); }
            to { opacity: 1; transform: none; }
        }

        .auth-card > * { animation: pi-rise .55s cubic-bezier(.2, .8, .25, 1) both; }
        .auth-card > *:nth-child(2) { animation-delay: .06s; }
        .auth-card > *:nth-child(3) { animation-delay: .12s; }
        .auth-card > *:nth-child(4) { animation-delay: .18s; }

        .auth-title {
            font-family: var(--pi-font-display);
            font-weight: 700;
            font-size: 1.72rem;
            margin: 0 0 6px;
        }

        .auth-sub {
            color: var(--pi-ink-soft);
            font-size: .96rem;
            margin: 0 0 28px;
        }

        .pi-field { margin-bottom: 18px; }

        .pi-field label {
            display: block;
            font-weight: 600;
            font-size: .86rem;
            margin-bottom: 7px;
            letter-spacing: .01em;
        }

        .pi-input-group { position: relative; }

        .pi-input-group > i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #8ba394;
            font-size: 1rem;
            pointer-events: none;
        }

        .pi-input {
            width: 100%;
            padding: 12px 14px 12px 42px;
            font: inherit;
            font-size: .96rem;
            color: var(--pi-ink);
            background: var(--pi-card);
            border: 1.5px solid var(--pi-line);
            border-radius: 11px;
            outline: none;
            transition: border-color .18s, box-shadow .18s;
        }

        .pi-input::placeholder { color: #a7b8ac; }

        .pi-input:focus {
            border-color: var(--pi-green-600);
            box-shadow: 0 0 0 4px rgba(39, 153, 90, .14);
        }

        .pi-input.is-invalid { border-color: var(--pi-danger); }

        .pi-toggle-eye {
            position: absolute;
            right: 6px;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            color: #7b9284;
            padding: 8px 10px;
            border-radius: 8px;
            cursor: pointer;
        }

        .pi-toggle-eye:hover { color: var(--pi-green-800); background: rgba(30, 125, 71, .08); }

        .pi-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 4px 0 22px;
            font-size: .88rem;
        }

        .pi-check { display: flex; align-items: center; gap: 8px; color: var(--pi-ink-soft); }

        .pi-check input {
            width: 17px;
            height: 17px;
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
            font-size: .98rem;
            font-weight: 700;
            letter-spacing: .01em;
            color: #f2fbf4;
            background: linear-gradient(180deg, var(--pi-green-600), var(--pi-green-800));
            border: 0;
            border-radius: 11px;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(15, 61, 38, .25);
            transition: transform .15s, box-shadow .15s, filter .15s;
        }

        .pi-btn:hover { transform: translateY(-1px); filter: brightness(1.05); box-shadow: 0 12px 26px rgba(15, 61, 38, .3); }
        .pi-btn:active { transform: none; }

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
            border-radius: 11px;
            font-size: .9rem;
            line-height: 1.5;
            margin-bottom: 20px;
        }

        .pi-alert i { margin-top: 1px; }

        .pi-alert.success { background: #e7f6ec; border: 1px solid #bfe5cc; color: #14522f; }
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
                width: 42px;
                height: 42px;
                border-radius: 11px;
                display: grid;
                place-items: center;
                font-size: 21px;
                color: var(--pi-green-950);
                background: linear-gradient(135deg, var(--pi-lime), var(--pi-teal));
            }

            .auth-mobile-brand .name {
                font-family: var(--pi-font-display);
                font-weight: 800;
                font-size: 1.15rem;
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
                <p>Collectes, validations signées, facturation et rapports : une seule plateforme
                    pour vos établissements de santé, en toute conformité.</p>

                <div class="brand-points">
                    <div class="point">
                        <i class="bi bi-clipboard2-check-fill"></i>
                        <div>
                            <strong>Enlèvements validés sur le terrain</strong>
                            <span>Signature électronique par le responsable de site ou l'agent de santé.</span>
                        </div>
                    </div>
                    <div class="point">
                        <i class="bi bi-shield-lock-fill"></i>
                        <div>
                            <strong>Sécurité de niveau professionnel</strong>
                            <span>Double authentification, audit complet, rôles granulaires.</span>
                        </div>
                    </div>
                    <div class="point">
                        <i class="bi bi-graph-up-arrow"></i>
                        <div>
                            <strong>Pilotage en temps réel</strong>
                            <span>Tableaux de bord, rapports financiers et exports PDF/Excel.</span>
                        </div>
                    </div>
                </div>
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
