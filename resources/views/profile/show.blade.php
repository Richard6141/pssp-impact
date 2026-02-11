@extends('layouts.back')
@section('content')
<main id="main" class="main">

    <div class="pagetitle">
        <h1>Profil</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
                <li class="breadcrumb-item active">Profil</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <!-- Messages de succès -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <section class="section profile">
        <div class="row">
            <div class="col-xl-4">

                <div class="card">
                    <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">

                        <img src="{{ Auth::user()->profile_image ? asset('storage/'.Auth::user()->profile_image) : 'https://st2.depositphotos.com/1104517/11967/v/950/depositphotos_119675554-stock-illustration-male-avatar-profile-picture-vector.jpg' }}"
                            alt="Profil" class="rounded-circle">
                        <h2>{{ Auth::user()->firstname }} {{ Auth::user()->lastname }}</h2>
                        <h3>{{ Auth::user()->username }}</h3>
                        <div class="small text-muted">
                            {{ Auth::user()->getRoleNames()->first() ?? 'Aucun rôle' }}
                            @if(Auth::user()->site)
                            • {{ Auth::user()->site->site_name }}
                            @endif
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-xl-8">

                <div class="card">
                    <div class="card-body pt-3">
                        <!-- Bordered Tabs -->
                        <ul class="nav nav-tabs nav-tabs-bordered">

                            <li class="nav-item">
                                <button class="nav-link active" data-bs-toggle="tab"
                                    data-bs-target="#profile-overview">Infos personnelles</button>
                            </li>

                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-edit">Éditer le profil</button>
                            </li>

                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab"
                                    data-bs-target="#profile-change-password">Mot de passe</button>
                            </li>

                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab"
                                    data-bs-target="#profile-security">
                                    <i class="bi bi-shield-lock"></i> Sécurité & 2FA
                                </button>
                            </li>

                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab"
                                    data-bs-target="#profile-professional">
                                    <i class="bi bi-briefcase"></i> Infos Pro
                                </button>
                            </li>

                        </ul>
                        <div class="tab-content pt-2">

                            <div class="tab-pane fade show active profile-overview" id="profile-overview">

                                <h5 class="card-title">Détails du profil</h5>

                                <div class="row">
                                    <div class="col-lg-3 col-md-4 label ">Prénom</div>
                                    <div class="col-lg-9 col-md-8">{{ Auth::user()->firstname }}</div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-3 col-md-4 label">Nom</div>
                                    <div class="col-lg-9 col-md-8">{{ Auth::user()->lastname }}</div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-3 col-md-4 label">Nom d'utilisateur</div>
                                    <div class="col-lg-9 col-md-8">{{ Auth::user()->username }}</div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-3 col-md-4 label">Email</div>
                                    <div class="col-lg-9 col-md-8">{{ Auth::user()->email }}</div>
                                </div>

                                @if(Auth::user()->localisation)
                                <div class="row">
                                    <div class="col-lg-3 col-md-4 label">Localisation</div>
                                    <div class="col-lg-9 col-md-8">{{ Auth::user()->localisation }}</div>
                                </div>
                                @endif

                                @if(Auth::user()->longitude && Auth::user()->latitude)
                                <div class="row">
                                    <div class="col-lg-3 col-md-4 label">Coordonnées</div>
                                    <div class="col-lg-9 col-md-8">{{ Auth::user()->latitude }},
                                        {{ Auth::user()->longitude }}
                                    </div>
                                </div>
                                @endif

                                <div class="row">
                                    <div class="col-lg-3 col-md-4 label">Statut</div>
                                    <div class="col-lg-9 col-md-8">
                                        <span class="badge bg-{{ Auth::user()->isActif ? 'success' : 'danger' }}">
                                            {{ Auth::user()->isActif ? 'Actif' : 'Inactif' }}
                                        </span>
                                    </div>
                                </div>

                            </div>

                            <div class="tab-pane fade profile-edit pt-3" id="profile-edit">

                                <!-- Formulaire d'édition du profil -->
                                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')

                                    <div class="row mb-3">
                                        <label for="firstname" class="col-md-4 col-lg-3 col-form-label">First
                                            Name</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="firstname" type="text"
                                                class="form-control @error('firstname') is-invalid @enderror"
                                                id="firstname" value="{{ old('firstname', Auth::user()->firstname) }}">
                                            @error('firstname')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="lastname" class="col-md-4 col-lg-3 col-form-label">Nom</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="lastname" type="text"
                                                class="form-control @error('lastname') is-invalid @enderror"
                                                id="lastname" value="{{ old('lastname', Auth::user()->lastname) }}">
                                            @error('lastname')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="username" class="col-md-4 col-lg-3 col-form-label">Nom d'utilisateur</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="username" type="text"
                                                class="form-control @error('username') is-invalid @enderror"
                                                id="username" value="{{ old('username', Auth::user()->username) }}">
                                            @error('username')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="email" class="col-md-4 col-lg-3 col-form-label">Email</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="email" type="email"
                                                class="form-control @error('email') is-invalid @enderror" id="email"
                                                value="{{ old('email', Auth::user()->email) }}">
                                            @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="profile_image" class="col-md-4 col-lg-3 col-form-label">Photo</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="profile_image" type="file" accept="image/*"
                                                class="form-control @error('profile_image') is-invalid @enderror"
                                                id="profile_image">
                                            @error('profile_image')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="localisation"
                                            class="col-md-4 col-lg-3 col-form-label">Localisation</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="localisation" type="text"
                                                class="form-control @error('localisation') is-invalid @enderror"
                                                id="localisation"
                                                value="{{ old('localisation', Auth::user()->localisation) }}">
                                            @error('localisation')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label class="col-md-4 col-lg-3 col-form-label">Coordonnées GPS</label>
                                        <div class="col-md-8 col-lg-9">
                                            <button type="button" class="btn btn-success btn-sm" id="shareLocalisationBtn">
                                                <i class="bi bi-geo-alt-fill"></i> Partager ma position
                                            </button>
                                            <small class="text-muted d-block mt-2" id="locationStatut"></small>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="latitude" class="col-md-4 col-lg-3 col-form-label">Latitude</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="latitude" type="number" step="0.0000001"
                                                class="form-control @error('latitude') is-invalid @enderror"
                                                id="latitude" value="{{ old('latitude', Auth::user()->latitude) }}" readonly>
                                            @error('latitude')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="longitude"
                                            class="col-md-4 col-lg-3 col-form-label">Longitude</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="longitude" type="number" step="0.0000001"
                                                class="form-control @error('longitude') is-invalid @enderror"
                                                id="longitude" value="{{ old('longitude', Auth::user()->longitude) }}" readonly>
                                            @error('longitude')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                                    </div>
                                </form><!-- End Formulaire d'édition du profil -->

                            </div>

                            <div class="tab-pane fade pt-3" id="profile-change-password">
                                <!-- Formulaire de changement de mot de passe -->
                                <form method="POST" action="{{ route('profile.password.update') }}">
                                    @csrf
                                    @method('PUT')

                                    <div class="row mb-3">
                                        <label for="current_password" class="col-md-4 col-lg-3 col-form-label">Mot de passe actuel</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="current_password" type="password"
                                                class="form-control @error('current_password') is-invalid @enderror"
                                                id="current_password">
                                            @error('current_password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="password" class="col-md-4 col-lg-3 col-form-label">Nouveau mot de passe</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="password" type="password"
                                                class="form-control @error('password') is-invalid @enderror"
                                                id="password">
                                            @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="password_confirmation"
                                            class="col-md-4 col-lg-3 col-form-label">Confirmer le nouveau mot de passe</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="password_confirmation" type="password" class="form-control"
                                                id="password_confirmation">
                                        </div>
                                    </div>

                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary">Changer le mot de passe</button>
                                    </div>
                                </form><!-- End Formulaire de changement de mot de passe -->

                            </div>

                            <div class="tab-pane fade pt-3" id="profile-security">
                                <!-- Paramètres de sécurité -->
                                <h5 class="card-title">Paramètres de Sécurité</h5>

                                @php
                                    $tfaService = app(\App\Services\TwoFactorAuthService::class);
                                    $tfaEnabled = $tfaService->isEnabled(Auth::user());
                                @endphp

                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="alert alert-info">
                                            <i class="bi bi-info-circle me-2"></i>
                                            L'authentification à deux facteurs (2FA) ajoute une couche de sécurité supplémentaire à votre compte.
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-md-4 col-lg-3 col-form-label">
                                        <i class="bi bi-shield-lock text-primary"></i> Authentification 2FA
                                    </label>
                                    <div class="col-md-8 col-lg-9">
                                        @if($tfaEnabled)
                                            <span class="badge bg-success mb-2">
                                                <i class="bi bi-check-circle"></i> Activé
                                            </span>
                                            <p class="text-muted mb-2">Votre compte est protégé par l'authentification à deux facteurs.</p>
                                            
                                            <div class="d-flex gap-2 mb-3">
                                                <a href="{{ route('2fa.recovery-codes') }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-key"></i> Codes de récupération
                                                </a>
                                                
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="document.getElementById('disable-2fa-form').style.display='block'">
                                                    <i class="bi bi-shield-x"></i> Désactiver
                                                </button>
                                            </div>

                                            <form id="disable-2fa-form" method="POST" action="{{ route('2fa.disable') }}" 
                                                style="display:none;" class="mt-3">
                                                @csrf
                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Mot de passe actuel</label>
                                                        <input type="password" name="password" class="form-control" required>
                                                        <small class="text-danger">
                                                            Entrez votre mot de passe pour désactiver le 2FA
                                                        </small>
                                                    </div>
                                                </div>
                                                <button type="submit" class="btn btn-danger" 
                                                    onclick="return confirm('Êtes-vous sûr de vouloir désactiver le 2FA ?')">
                                                    Confirmer la désactivation
                                                </button>
                                                <button type="button" class="btn btn-secondary" 
                                                    onclick="document.getElementById('disable-2fa-form').style.display='none'">
                                                    Annuler
                                                </button>
                                            </form>
                                        @else
                                            <span class="badge bg-secondary mb-2">
                                                <i class="bi bi-x-circle"></i> Désactivé
                                            </span>
                                            <p class="text-muted mb-2">
                                                Nous vous recommandons fortement d'activer l'authentification à deux facteurs.
                                            </p>
                                            <a href="{{ route('2fa.enable') }}" class="btn btn-sm btn-primary">
                                                <i class="bi bi-shield-plus"></i> Activer le 2FA
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-md-4 col-lg-3 col-form-label">
                                        <i class="bi bi-laptop text-info"></i> Sessions actives
                                    </label>
                                    <div class="col-md-8 col-lg-9">
                                        <p class="text-muted mb-2">Gérez les appareils connectés à votre compte.</p>
                                        <a href="{{ route('admin.sessions.index') }}" class="btn btn-sm btn-outline-info">
                                            <i class="bi bi-eye"></i> Voir mes sessions
                                        </a>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-md-4 col-lg-3 col-form-label">
                                        <i class="bi bi-clock-history text-secondary"></i> Dernière connexion
                                    </label>
                                    <div class="col-md-8 col-lg-9">
                                        @if(Auth::user()->last_login_at)
                                            <p class="mb-0">
                                                {{ Auth::user()->last_login_at->format('d/m/Y à H:i') }}
                                            </p>
                                            @if(Auth::user()->last_login_ip)
                                                <small class="text-muted">IP: {{ Auth::user()->last_login_ip }}</small>
                                            @endif
                                        @else
                                            <p class="text-muted mb-0">Aucune connexion enregistrée</p>
                                        @endif
                                    </div>
                                </div>

                            </div>

                            <div class="tab-pane fade pt-3" id="profile-professional">
                                <h5 class="card-title">Informations Professionnelles</h5>
                                
                                <form method="POST" action="{{ route('profile.update-professional') }}">
                                    @csrf
                                    @method('PUT')

                                    <div class="row mb-3">
                                        <label for="availability_status" class="col-md-4 col-lg-3 col-form-label">Statut de disponibilité</label>
                                        <div class="col-md-8 col-lg-9">
                                            <select name="availability_status" class="form-select @error('availability_status') is-invalid @enderror">
                                                <option value="available" {{ Auth::user()->availability_status == 'available' ? 'selected' : '' }}>Disponible</option>
                                                <option value="busy" {{ Auth::user()->availability_status == 'busy' ? 'selected' : '' }}>Occupé</option>
                                                <option value="offline" {{ Auth::user()->availability_status == 'offline' ? 'selected' : '' }}>Hors ligne</option>
                                            </select>
                                            @error('availability_status')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="service_communes" class="col-md-4 col-lg-3 col-form-label">Zones d'intervention</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input type="text" name="service_communes" class="form-control @error('service_communes') is-invalid @enderror"
                                                value="{{ is_array(Auth::user()->service_communes) ? implode(', ', Auth::user()->service_communes) : Auth::user()->service_communes }}"
                                                placeholder="Ex: Cotonou, Porto-Novo, Calavi (séparés par des virgules)">
                                            @error('service_communes')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">Communes ou quartiers où vous intervenez.</div>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="specialties" class="col-md-4 col-lg-3 col-form-label">Spécialités</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input type="text" name="specialties" class="form-control @error('specialties') is-invalid @enderror"
                                                value="{{ is_array(Auth::user()->specialties) ? implode(', ', Auth::user()->specialties) : Auth::user()->specialties }}"
                                                placeholder="Ex: DASRI, Déchets chimiques, Collecte (séparés par des virgules)">
                                            @error('specialties')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="text-center mt-4">
                                        <button type="submit" class="btn btn-primary">Enregistrer les informations</button>
                                    </div>
                                </form>
                            </div>

                        </div><!-- End Bordered Tabs -->

                    </div>
                </div>

            </div>
        </div>
    </section>

</main><!-- End #main -->

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const shareLocalisationBtn = document.getElementById('shareLocalisationBtn');
        const locationStatut = document.getElementById('locationStatut');
        const latitudeInput = document.getElementById('latitude');
        const longitudeInput = document.getElementById('longitude');

        if (shareLocalisationBtn) {
            shareLocalisationBtn.addEventListener('click', function() {
                // Vérifier si la géolocalisation est supportée
                if (!navigator.geolocation) {
                    locationStatut.textContent = 'La géolocalisation n\'est pas supportée par votre navigateur.';
                    locationStatut.className = 'text-danger d-block mt-2';
                    return;
                }

                // Afficher un message de chargement
                locationStatut.textContent = 'Récupération de votre position...';
                locationStatut.className = 'text-info d-block mt-2';
                shareLocalisationBtn.disabled = true;

                // Récupérer la position
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        // Succès - récupération des coordonnées
                        const latitude = position.coords.latitude;
                        const longitude = position.coords.longitude;

                        // Remplir les champs
                        latitudeInput.value = latitude.toFixed(7);
                        longitudeInput.value = longitude.toFixed(7);

                        // Afficher un message de succès
                        locationStatut.textContent = `Position récupérée avec succès ! (Précision: ${Math.round(position.coords.accuracy)}m)`;
                        locationStatut.className = 'text-success d-block mt-2';
                        shareLocalisationBtn.disabled = false;
                    },
                    function(error) {
                        // Erreur
                        let errorMessage = '';
                        switch (error.code) {
                            case error.PERMISSION_DENIED:
                                errorMessage = 'Vous avez refusé l\'accès à votre position.';
                                break;
                            case error.POSITION_UNAVAILABLE:
                                errorMessage = 'Les informations de localisation ne sont pas disponibles.';
                                break;
                            case error.TIMEOUT:
                                errorMessage = 'La demande de localisation a expiré.';
                                break;
                            default:
                                errorMessage = 'Une erreur inconnue s\'est produite.';
                                break;
                        }
                        locationStatut.textContent = errorMessage;
                        locationStatut.className = 'text-danger d-block mt-2';
                        shareLocalisationBtn.disabled = false;
                    }, {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    }
                );
            });
        }
    });
</script>
@endpush

@endsection
