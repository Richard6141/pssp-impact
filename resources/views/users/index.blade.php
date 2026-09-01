@extends('layouts.back')

@section('content')
<main id="main" class="main">

    <div class="pagetitle d-flex justify-content-between align-items-center">
        <div>
            <h1>Utilisateurs</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Utilisateurs</li>
                </ol>
            </nav>
        </div>
        @can('users.create')
        <div class="d-flex gap-2">
            <div class="dropdown">
                <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-gear"></i> Actions
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.users.invitations.index') }}">
                            <i class="bi bi-envelope me-2"></i> Gérer les invitations
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.users.import.index') }}">
                            <i class="bi bi-file-earmark-arrow-up me-2"></i> Importer (CSV)
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('users.export') }}">
                            <i class="bi bi-file-earmark-arrow-down me-2"></i> Exporter (CSV)
                        </a>
                    </li>
                </ul>
            </div>

            <a href="{{ route('users.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Nouvel Utilisateur
            </a>
        </div>
        @endcan
    </div>

    <section class="section">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Liste des utilisateurs</h5>

                {{-- La recherche texte est fournie par la barre du tableau
                     (data-server-search) : on la conserve ici en champ cache pour
                     ne pas la perdre en appliquant un filtre. --}}
                <form method="GET" class="row g-3 mb-3">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <div class="col-md-4">
                        <select name="role" class="form-select">
                            <option value="">Tous les rôles</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->name }}" @selected(request('role')===$role->name)>
                                {{ $role->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="site_id" class="form-select">
                            <option value="">Tous les sites</option>
                            @foreach($sites as $site)
                            <option value="{{ $site->site_id }}" @selected(request('site_id')===$site->site_id)>
                                {{ $site->site_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">Tous les statuts</option>
                            <option value="active" @selected(request('status')==='active')>Actif</option>
                            <option value="inactive" @selected(request('status')==='inactive')>Inactif</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-primary w-100">Filtrer</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle"
                        data-server-search data-total="{{ $users->total() }}">
                        <thead class="table-dark">
                            <tr>
                                <th>Utilisateur</th>
                                <th>Email</th>
                                <th>Rôle(s)</th>
                                <th>Statut</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $user->profile_image ? asset('storage/'.$user->profile_image) : 'https://st2.depositphotos.com/1104517/11967/v/950/depositphotos_119675554-stock-illustration-male-avatar-profile-picture-vector.jpg' }}"
                                            alt="Avatar" class="rounded-circle" width="32" height="32">
                                        <span>{{ $user->firstname }} {{ $user->lastname }}</span>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @forelse($user->roles as $role)
                                    <span class="badge bg-secondary me-1">{{ $role->name }}</span>
                                    @empty
                                    <span class="text-muted">Aucun</span>
                                    @endforelse
                                </td>
                                <td>
                                    <span class="badge bg-{{ $user->isActive ? 'success' : 'danger' }}">
                                        {{ $user->isActive ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @can('users.view')
                                    <a href="{{ route('users.show', $user->user_id) }}" class="btn btn-sm btn-info"
                                        title="Voir">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @endcan
                                    @can('users.update')
                                    <a href="{{ route('users.edit', $user->user_id) }}" class="btn btn-sm btn-warning"
                                        title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @endcan
                                    @can('users.delete')
                                    <form action="{{ route('users.destroy', $user->user_id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Supprimer cet utilisateur ?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">Aucun utilisateur trouvé.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-3">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </section>

</main>
@endsection
