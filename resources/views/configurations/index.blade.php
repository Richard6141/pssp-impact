@extends('layouts.back')
@section('content')
<main id="main" class="main">

    <div class="pagetitle">
        <h1>Configuration</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active">Configuration</li>
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

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Boutons d'initialisation du système -->
    @if($permissions->count() == 0 || $roles->count() == 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-body">
                    <h5 class="card-title text-warning">
                        <i class="bi bi-exclamation-triangle"></i> Initialisation du système
                    </h5>
                    <p class="card-text">Il semble que ce soit votre première utilisation. Initialisez le système avec
                        les permissions et rôles par défaut :</p>
                    <div class="d-flex gap-2">
                        <form method="POST" action="{{ route('create-default-permissions') }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-gear"></i> Créer les Permissions par Défaut
                            </button>
                        </form>
                        <form method="POST" action="{{ route('create-default-roles') }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-info">
                                <i class="bi bi-people"></i> Créer les Rôles par Défaut
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <section class="section">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <!-- Bordered Tabs -->
                        <ul class="nav nav-tabs nav-tabs-bordered" id="borderedTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="sites-tab" data-bs-toggle="tab"
                                    data-bs-target="#bordered-sites" type="button" role="tab" aria-controls="sites"
                                    aria-selected="true">
                                    <i class="bi bi-geo-alt"></i> Sites
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="types-tab" data-bs-toggle="tab"
                                    data-bs-target="#bordered-types" type="button" role="tab" aria-controls="types"
                                    aria-selected="false">
                                    <i class="bi bi-trash"></i> Types de Déchets
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="roles-tab" data-bs-toggle="tab"
                                    data-bs-target="#bordered-roles" type="button" role="tab" aria-controls="roles"
                                    aria-selected="false">
                                    <i class="bi bi-person-badge"></i> Rôles & Permissions
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="users-roles-tab" data-bs-toggle="tab"
                                    data-bs-target="#bordered-users-roles" type="button" role="tab"
                                    aria-controls="users-roles" aria-selected="false">
                                    <i class="bi bi-people"></i> Assigner Rôles
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content pt-2" id="borderedTabContent">

                            <!-- Onglet Sites -->
                            <div class="tab-pane fade show active" id="bordered-sites" role="tabpanel"
                                aria-labelledby="sites-tab">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title">Gestion des Sites</h5>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#addSiteModal">
                                        <i class="bi bi-plus"></i> Ajouter un Site
                                    </button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Nom</th>
                                                <th>Adresse</th>
                                                <th>Coordonnées</th>
                                                <th>Statut</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($sites as $site)
                                            <tr>
                                                <td>{{ $site->site_name }}</td>
                                                <td>{{ $site->site_departement }}, {{ $site->site_commune}}</td>
                                                <td>
                                                    @if($site->latitude && $site->longitude)
                                                    {{ $site->latitude }}, {{ $site->longitude }}
                                                    @else
                                                    N/A
                                                    @endif
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge bg-{{ $site->is_active ? 'success' : 'danger' }}">
                                                        {{ $site->responsable ? 'Actif' : 'Inactif' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <!-- Utiliser getKey() pour obtenir la clé primaire -->
                                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                                        onclick="editSite({{ $site->getKey() }})">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <form method="POST"
                                                        action="{{ route('sites.destroy', $site->getKey()) }}"
                                                        style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                            onclick="return confirm('Êtes-vous sûr ?')">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="5" class="text-center">Aucun site enregistré</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Onglet Types de Déchets -->
                            <div class="tab-pane fade" id="bordered-types" role="tabpanel" aria-labelledby="types-tab">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title">Gestion des Types de Déchets</h5>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#addTypeModal">
                                        <i class="bi bi-plus"></i> Ajouter un Type
                                    </button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Nom</th>
                                                <th>Description</th>
                                                <th>Code</th>
                                                <th>Statut</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($typesDechets as $type)
                                            <tr>
                                                <td>{{ $type->libelle }}</td>
                                                <td>{{ Str::limit($type->description, 50) }}</td>
                                                <td>
                                                    @if($type->code)
                                                    @endif
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge bg-{{ $type->is_active ? 'success' : 'danger' }}">
                                                        {{ $type->is_active ? 'Actif' : 'Inactif' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                                        onclick="editType({{ $type->getKey() }})">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <form method="POST"
                                                        action="{{ route('type_dechets.destroy', $type->getKey()) }}"
                                                        style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                            onclick="return confirm('Êtes-vous sûr ?')">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="5" class="text-center">Aucun type de déchet enregistré</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Onglet Rôles & Permissions -->
                            <div class="tab-pane fade" id="bordered-roles" role="tabpanel" aria-labelledby="roles-tab">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6>Rôles</h6>
                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#addRoleModal">
                                                <i class="bi bi-plus"></i> Nouveau Rôle
                                            </button>
                                        </div>
                                        <div class="list-group">
                                            @forelse($roles as $role)
                                            <div
                                                class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong>{{ $role->name }}</strong>
                                                    <br><small class="text-muted">{{ $role->permissions->count() }}
                                                        permissions</small>
                                                </div>
                                                <div>
                                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                                        onclick="editRole({{ $role->getKey() }})">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    @if($role->name !== 'admin')
                                                    <form method="POST"
                                                        action="{{ route('roles.destroy', $role->getKey()) }}"
                                                        style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                            onclick="return confirm('Êtes-vous sûr ?')">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                    @endif
                                                </div>
                                            </div>
                                            @empty
                                            <div class="list-group-item text-center">Aucun rôle défini</div>
                                            @endforelse
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6>Permissions</h6>
                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#addPermissionModal">
                                                <i class="bi bi-plus"></i> Nouvelle Permission
                                            </button>
                                        </div>
                                        <div class="list-group">
                                            @forelse($permissions as $permission)
                                            <div
                                                class="list-group-item d-flex justify-content-between align-items-center">
                                                <span>{{ $permission->name }}</span>
                                                <form method="POST"
                                                    action="{{ route('permissions.destroy', $permission->getKey()) }}"
                                                    style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Êtes-vous sûr ?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                            @empty
                                            <div class="list-group-item text-center">Aucune permission définie</div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Onglet Assigner Rôles -->
                            <div class="tab-pane fade" id="bordered-users-roles" role="tabpanel"
                                aria-labelledby="users-roles-tab">
                                <h5 class="card-title">Assigner Rôles et Permissions aux Utilisateurs</h5>

                                <form method="POST" action="{{ route('assign-role') }}">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="user_id" class="form-label">Utilisateur</label>
                                            <select name="user_id" id="user_id"
                                                class="form-select @error('user_id') is-invalid @enderror" required>
                                                <option value="">Sélectionner un utilisateur</option>
                                                @foreach($users as $user)
                                                <option value="{{ $user->user_id }}"
                                                    {{ old('user_id') == $user->user_id ? 'selected' : '' }}>
                                                    {{ $user->firstname }} {{ $user->lastname }} ({{ $user->username }})
                                                </option>
                                                @endforeach
                                            </select>
                                            @error('user_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label for="roles" class="form-label">Rôles</label>
                                            <select name="roles[]" id="roles" class="form-select" multiple>
                                                @foreach($roles as $role)
                                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                                                @endforeach
                                            </select>
                                            <small class="text-muted">Maintenir Ctrl pour sélectionner plusieurs
                                                rôles</small>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label for="permissions" class="form-label">Permissions directes</label>
                                            <select name="permissions[]" id="permissions" class="form-select" multiple>
                                                @foreach($permissions as $permission)
                                                <option value="{{ $permission->name }}">{{ $permission->name }}</option>
                                                @endforeach
                                            </select>
                                            <small class="text-muted">Maintenir Ctrl pour sélectionner plusieurs
                                                permissions</small>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <button type="submit" name="action" value="assign" class="btn btn-success">
                                            <i class="bi bi-check"></i> Assigner
                                        </button>
                                        <button type="submit" name="action" value="revoke" class="btn btn-warning">
                                            <i class="bi bi-x"></i> Révoquer
                                        </button>
                                    </div>
                                </form>

                                <!-- Affichage des utilisateurs avec leurs rôles -->
                                <div class="mt-4">
                                    <h6>Utilisateurs et leurs rôles/permissions</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Utilisateur</th>
                                                    <th>Rôles</th>
                                                    <th>Permissions directes</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($users as $user)
                                                <tr>
                                                    <td>{{ $user->firstname }} {{ $user->lastname }}</td>
                                                    <td>
                                                        @forelse($user->roles as $role)
                                                        <span class="badge bg-primary me-1">{{ $role->name }}</span>
                                                        @empty
                                                        <small class="text-muted">Aucun rôle</small>
                                                        @endforelse
                                                    </td>
                                                    <td>
                                                        @forelse($user->getDirectPermissions() as $permission)
                                                        <span
                                                            class="badge bg-secondary me-1">{{ $permission->name }}</span>
                                                        @empty
                                                        <small class="text-muted">Aucune permission directe</small>
                                                        @endforelse
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                            onclick="loadUserRoles('{{ $user->user_id }}')">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

</main><!-- End #main -->

<!-- Modals -->
<!-- Modal Ajouter Site -->
<div class="modal fade" id="addSiteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('sites.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter un Site</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="site_name" class="form-label">Nom du site</label>
                        <input type="text" class="form-control" id="site_name" name="site_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="site_departement" class="form-label">Département</label>
                        <input type="text" class="form-control" id="site_departement" name="site_departement" required>
                    </div>
                    <div class="mb-3">
                        <label for="site_commune" class="form-label">Commune</label>
                        <input type="text" class="form-control" id="site_commune" name="site_commune" required>
                    </div>
                    <div class="mb-3">
                        <label for="localisation" class="form-label">Localisation</label>
                        <textarea class="form-control" id="localisation" name="localisation" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="site_latitude" class="form-label">Latitude</label>
                            <input type="number" step="0.0000001" class="form-control" id="site_latitude"
                                name="latitude">
                        </div>
                        <div class="col-md-6">
                            <label for="site_longitude" class="form-label">Longitude</label>
                            <input type="number" step="0.0000001" class="form-control" id="site_longitude"
                                name="longitude">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ajouter Type -->
<div class="modal fade" id="addTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('type_dechets.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter un Type de Déchet</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="type_nom" class="form-label">Nom du type</label>
                        <input type="text" class="form-control" id="type_nom" name="nom" required>
                    </div>
                    <div class="mb-3">
                        <label for="type_description" class="form-label">Description</label>
                        <textarea class="form-control" id="type_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="type_couleur" class="form-label">Couleur</label>
                        <input type="color" class="form-control form-control-color" id="type_couleur" name="couleur">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ajouter Rôle -->
<div class="modal fade" id="addRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('roles.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Créer un Rôle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="role_name" class="form-label">Nom du rôle</label>
                        <input type="text" class="form-control" id="role_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="role_permissions" class="form-label">Permissions</label>
                        <div class="row">
                            @foreach($permissions as $permission)
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="permissions[]"
                                        value="{{ $permission->name }}" id="perm_{{ $permission->id }}">
                                    <label class="form-check-label" for="perm_{{ $permission->id }}">
                                        {{ $permission->name }}
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Créer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ajouter Permission -->
<div class="modal fade" id="addPermissionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('permissions.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Créer une Permission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="permission_name" class="form-label">Nom de la permission</label>
                        <input type="text" class="form-control" id="permission_name" name="name" required>
                        <small class="text-muted">Ex: view-users, create-posts, manage-settings</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Créer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function loadUserRoles(userId) {
        document.getElementById('user_id').value = userId;

        // Scroll to the form
        document.getElementById('bordered-users-roles').scrollIntoView({
            behavior: 'smooth'
        });

        // You can add AJAX call here to load current roles/permissions for this user
    }

    function editSite(siteId) {
        // Add your edit site logic here
        console.log('Edit site:', siteId);
    }

    function editType(typeId) {
        // Add your edit type logic here
        console.log('Edit type:', typeId);
    }

    function editRole(roleId) {
        // Add your edit role logic here
        console.log('Edit role:', roleId);
    }
</script>

@endsection