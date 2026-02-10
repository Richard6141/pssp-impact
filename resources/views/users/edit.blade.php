@extends('layouts.back')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
        <h1>Modifier Utilisateur</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Utilisateurs</a></li>
                <li class="breadcrumb-item active">Modifier</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Modifier l'utilisateur</h5>

                <form method="POST" action="{{ route('users.update', $user->user_id) }}"
                    enctype="multipart/form-data" class="row g-3">
                    @csrf
                    @method('PUT')

                    <div class="col-md-6">
                        <label class="form-label">Prénom</label>
                        <input type="text" name="firstname" class="form-control"
                            value="{{ old('firstname', $user->firstname) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nom</label>
                        <input type="text" name="lastname" class="form-control"
                            value="{{ old('lastname', $user->lastname) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nom d'utilisateur</label>
                        <input type="text" name="username" class="form-control"
                            value="{{ old('username', $user->username) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control"
                            value="{{ old('email', $user->email) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mot de passe (optionnel)</label>
                        <input type="password" name="password" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirmation (optionnel)</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Rôle</label>
                        <select name="role" class="form-select" required>
                            @foreach($roles as $role)
                            <option value="{{ $role->name }}"
                                @selected(old('role', $userRoles->first())===$role->name)>
                                {{ $role->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Site</label>
                        <select name="site_id" class="form-select">
                            <option value="">Aucun</option>
                            @foreach($sites as $site)
                            <option value="{{ $site->site_id }}"
                                @selected(old('site_id', $user->site_id)===$site->site_id)>
                                {{ $site->site_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="phone" class="form-control"
                            value="{{ old('phone', $user->phone) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Entreprise</label>
                        <input type="text" name="company" class="form-control"
                            value="{{ old('company', $user->company) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Poste</label>
                        <input type="text" name="job_title" class="form-control"
                            value="{{ old('job_title', $user->job_title) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Adresse</label>
                        <input type="text" name="address" class="form-control"
                            value="{{ old('address', $user->address) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Pays</label>
                        <input type="text" name="country" class="form-control"
                            value="{{ old('country', $user->country) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Localisation</label>
                        <input type="text" name="localisation" class="form-control"
                            value="{{ old('localisation', $user->localisation) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Latitude</label>
                        <input type="number" step="0.0000001" name="latitude" class="form-control"
                            value="{{ old('latitude', $user->latitude) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Longitude</label>
                        <input type="number" step="0.0000001" name="longitude" class="form-control"
                            value="{{ old('longitude', $user->longitude) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">À propos</label>
                        <textarea name="about" class="form-control"
                            rows="3">{{ old('about', $user->about) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Photo de profil</label>
                        <input type="file" name="profile_image" class="form-control" accept="image/*">
                        @if($user->profile_image)
                        <div class="mt-2">
                            <img src="{{ asset('storage/'.$user->profile_image) }}" alt="Profil" width="80"
                                class="img-thumbnail">
                        </div>
                        @endif
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="isActive" id="isActive"
                                {{ old('isActive', $user->isActive) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isActive">Compte actif</label>
                        </div>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Mettre à jour</button>
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </section>

</main>
@endsection
