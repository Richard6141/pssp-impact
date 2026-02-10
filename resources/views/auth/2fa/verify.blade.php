<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Vérification 2FA - Gestion Déchets Médicaux</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Premium CSS (sans gradients) -->
    <link href="{{ asset('backend/assets/css/premium-design.css') }}" rel="stylesheet">
    <link href="{{ asset('backend/assets/css/no-gradients.css') }}" rel="stylesheet">

    <style>
        body {
            background: #f6f9ff;
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .premium-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            padding: 2rem;
            max-width: 450px;
            width: 100%;
            border: 1px solid #e2e8f0;
        }

        .btn-primary-flat {
            background-color: #667eea;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 0.375rem;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-primary-flat:hover {
            background-color: #5568d3;
            transform: translateY(-1px);
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="premium-card">
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <i class="bi bi-shield-lock text-primary" style="font-size: 4rem;"></i>
                        </div>
                        <h4 class="mb-2">Authentification requise</h4>
                        <p class="text-muted small">
                            Entrez le code à 6 chiffres de votre application d'authentification
                        </p>
                    </div>

                    <form method="POST" action="{{ route('2fa.validate') }}">
                        @csrf
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Code de vérification</label>
                            <input 
                                type="text" 
                                name="code" 
                                class="form-control form-control-lg text-center @error('code') is-invalid @enderror" 
                                placeholder="000000"
                                style="font-size: 1.5rem; letter-spacing: 0.5rem;"
                                maxlength="6"
                                required
                                autofocus
                                autocomplete="one-time-code"
                                inputmode="numeric">
                            @error('code')
                                <div class="invalid-feedback text-center mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary-flat">
                                <i class="bi bi-check-circle me-2"></i> Vérifier
                            </button>
                        </div>
                    </form>

                    <div class="text-center">
                        <a href="{{ route('login') }}" class="text-decoration-none text-muted small">
                            <i class="bi bi-arrow-left me-1"></i> Retour à la connexion
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
