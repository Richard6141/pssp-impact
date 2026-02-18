@extends('layouts.back')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
        <h1>Nouvel Utilisateur</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Utilisateurs</a></li>
                <li class="breadcrumb-item active">CrÃ©er</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">CrÃ©er un utilisateur</h5>

                @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if($errors->any())
                <div class="alert alert-danger">
                    <strong>Erreurs de validation :</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data"
                    class="row g-3">
                    @csrf

                    <div class="col-md-6">
                        <label class="form-label">PrÃ©nom</label>
                        <input type="text" name="firstname" class="form-control" value="{{ old('firstname') }}"
                            required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nom</label>
                        <input type="text" name="lastname" class="form-control" value="{{ old('lastname') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nom d'utilisateur</label>
                        <input type="text" name="username" class="form-control" value="{{ old('username') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mot de passe</label>
                        <input type="password" name="password" class="form-control" required autocomplete="new-password">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirmation</label>
                        <input type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">RÃ´le</label>
                        <select name="role" class="form-select" required>
                            <option value="">SÃ©lectionner</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->name }}" @selected(old('role')===$role->name)>
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
                            <option value="{{ $site->site_id }}" @selected(old('site_id')===$site->site_id)>
                                {{ $site->site_name }}
                            </option>
                            @endforeach
                        </select>
                        <div class="form-text">Site principal (rattachement hiÃ©rarchique)</div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="set_as_site_responsable" id="set_as_site_responsable" value="1" {{ old('set_as_site_responsable') ? 'checked' : '' }}>
                            <label class="form-check-label" for="set_as_site_responsable">Definir cet utilisateur comme responsable du site principal</label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Sites autorisÃ©s (AccÃ¨s)</label>
                        <select name="sites[]" class="form-select" multiple size="4">
                            @foreach($sites as $site)
                            <option value="{{ $site->site_id }}" {{ in_array($site->site_id, old('sites', [])) ? 'selected' : '' }}>
                                {{ $site->site_name }}
                            </option>
                            @endforeach
                        </select>
                        <div class="form-text">Maintenez Ctrl pour sÃ©lectionner plusieurs sites.</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">TÃ©lÃ©phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Entreprise</label>
                        <input type="text" name="company" class="form-control" value="{{ old('company') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Poste</label>
                        <input type="text" name="job_title" class="form-control" value="{{ old('job_title') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Adresse</label>
                        <input type="text" name="address" class="form-control" value="{{ old('address') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Pays</label>
                        <input type="text" name="country" class="form-control" value="{{ old('country') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Localisation</label>
                        <input type="text" name="localisation" class="form-control" value="{{ old('localisation') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Latitude</label>
                        <input type="number" step="0.0000001" name="latitude" class="form-control"
                            value="{{ old('latitude') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Longitude</label>
                        <input type="number" step="0.0000001" name="longitude" class="form-control"
                            value="{{ old('longitude') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Ã€ propos</label>
                        <textarea name="about" class="form-control" rows="3">{{ old('about') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Photo de profil</label>
                        <input type="file" name="profile_image" class="form-control" accept="image/*"
                            id="profile_image_input">
                        <div class="mt-2 d-none" id="profile_image_preview">
                            <img src="" alt="AperÃ§u" width="80" height="80" class="img-thumbnail rounded-circle">
                        </div>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check">
                            <input type="hidden" name="isActive" value="0">
                            <input class="form-check-input" type="checkbox" name="isActive" id="isActive" value="1"
                                {{ old('isActive', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isActive">Compte actif</label>
                        </div>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">CrÃ©er</button>
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </section>

</main>
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('profile_image_input');
        const previewWrapper = document.getElementById('profile_image_preview');
        const previewImg = previewWrapper?.querySelector('img');

        if (!input || !previewWrapper || !previewImg) return;

        input.addEventListener('change', function () {
            const file = this.files && this.files[0];
            if (!file) {
                previewWrapper.classList.add('d-none');
                previewImg.src = '';
                return;
            }
            const url = URL.createObjectURL(file);
            previewImg.src = url;
            previewWrapper.classList.remove('d-none');
        });
    });
</script>
@endpush
@endsection
