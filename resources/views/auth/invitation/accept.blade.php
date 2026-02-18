<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Finaliser votre inscription - PSSP IMPACT+</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Premium CSS -->
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
            padding: 2rem 0;
        }

        .premium-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        .card-header {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 1.5rem;
            text-align: center;
        }

        .card-body {
            padding: 2rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                
                <div class="text-center mb-4">
                    <h2 class="h3 fw-bold text-primary">PSSP IMPACT+</h2>
                    <p class="text-muted">Plateforme de Gestion des DÃ©chets MÃ©dicaux</p>
                </div>

                <div class="premium-card">
                    <div class="card-header">
                        <h4 class="mb-0">Finaliser votre inscription</h4>
                    </div>

                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger mb-4">
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="alert alert-info mb-4">
                            <i class="bi bi-info-circle me-1"></i>
                            Vous avez Ã©tÃ© invitÃ©(e) avec l'adresse <strong>{{ $invitation->email }}</strong>.
                            Veuillez complÃ©ter vos informations ci-dessous.
                        
                            @if($invitation->site)
                            <hr class="my-2">
                            <div><strong>Site assigne :</strong> {{ $invitation->site->site_name }}</div>
                            @if($invitation->assign_as_site_responsable)
                            <div><strong>Acces :</strong> Responsable du site</div>
                            @endif
                            @endif
                        </div>

                        <form method="POST" action="{{ route('invitation.accept.store', $invitation->token) }}">
                            @csrf

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">PrÃ©nom</label>
                                    <input type="text" name="firstname" class="form-control" value="{{ old('firstname') }}" required autofocus>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nom</label>
                                    <input type="text" name="lastname" class="form-control" value="{{ old('lastname') }}" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nom d'utilisateur</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" name="username" class="form-control" value="{{ old('username') }}" required>
                                </div>
                                <div class="form-text">Ce nom sera utilisÃ© pour vous connecter.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mot de passe</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" name="password" class="form-control" required minlength="8">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Confirmer le mot de passe</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                    <input type="password" name="password_confirmation" class="form-control" required>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    CrÃ©er mon compte
                                </button>
                            </div>

                        </form>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <p class="text-muted small">&copy; {{ date('Y') }} PSSP IMPACT+. Tous droits rÃ©servÃ©s.</p>
                </div>

            </div>
        </div>
    </div>
</body>
</html>
