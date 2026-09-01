@extends('layouts.back')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
        <h1>Inviter un utilisateur</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
                <li class="breadcrumb-item">Utilisateurs</li>
                <li class="breadcrumb-item"><a href="{{ route('admin.users.invitations.index') }}">Invitations</a></li>
                <li class="breadcrumb-item active">Nouvelle invitation</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <div class="card premium-card">
                    <div class="card-body">
                        <h5 class="card-title">Envoyer une invitation</h5>
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-1"></i>
                            L'utilisateur recevra un email contenant un lien unique pour créer son mot de passe et finaliser son inscription.
                        </div>

                        <form action="{{ route('admin.users.invitations.store') }}" method="POST" class="needs-validation" novalidate>
                            @csrf

                            <div class="row mb-3">
                                <label for="email" class="col-sm-3 col-form-label">Adresse Email</label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                            id="email" name="email" value="{{ old('email') }}" required autofocus>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-text">L'email doit être unique et ne pas appartenir à un compte existant.</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="role_id" class="col-sm-3 col-form-label">Rôle assigné</label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                                        <select class="form-select @error('role_id') is-invalid @enderror" id="role_id" name="role_id" required>
                                            <option value="" selected disabled>Choisir un rôle...</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                                    {{ ucfirst($role->name) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('role_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="site_id" class="col-sm-3 col-form-label">Site</label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-building"></i></span>
                                        <select class="form-select @error('site_id') is-invalid @enderror" id="site_id" name="site_id">
                                            <option value="">Aucun site</option>
                                            @foreach($sites as $site)
                                                <option value="{{ $site->site_id }}" {{ old('site_id') == $site->site_id ? 'selected' : '' }}>
                                                    {{ $site->site_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('site_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-text">Optionnel : site associe automatiquement a la creation du compte.</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-9 offset-sm-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="assign_as_site_responsable" name="assign_as_site_responsable" value="1" {{ old('assign_as_site_responsable') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="assign_as_site_responsable">
                                            Definir l'invite comme responsable du site selectionne
                                        </label>
                                    </div>
                                </div>
                            </div>


                            <div class="row mb-3">
                                <div class="col-sm-9 offset-sm-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="notify_me" name="notify_me" value="1" checked>
                                        <label class="form-check-label" for="notify_me">
                                            M'avertir lorsque l'invitation est acceptée
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <a href="{{ route('admin.users.invitations.index') }}" class="btn btn-secondary">Annuler</a>
                                <button type="submit" class="btn btn-primary-flat">
                                    <i class="bi bi-send me-1"></i> Envoyer l'invitation
                                </button>
                            </div>

                        </form>

                    </div>
                </div>

            </div>
        </div>
    </section>

</main>


@endsection
