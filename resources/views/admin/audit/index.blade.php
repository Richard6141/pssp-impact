@extends('layouts.back')

@section('content')
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Journal d'Audit</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
                <li class="breadcrumb-item">Admin</li>
                <li class="breadcrumb-item active">Audit</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        {{-- Statistiques --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <x-stats-card 
                    icon="bi-activity" 
                    :value="$stats['total_actions']" 
                    label="Total Actions"
                    color="primary">
                </x-stats-card>
            </div>
            <div class="col-md-3">
                <x-stats-card 
                    icon="bi-pencil-square" 
                    :value="$stats['by_action']['update'] ?? 0" 
                    label="Modifications"
                    color="warning">
                </x-stats-card>
            </div>
            <div class="col-md-3">
                <x-stats-card 
                    icon="bi-plus-circle" 
                    :value="$stats['by_action']['create'] ?? 0" 
                    label="Créations"
                    color="success">
                </x-stats-card>
            </div>
            <div class="col-md-3">
                <x-stats-card 
                    icon="bi-trash" 
                    :value="$stats['by_action']['delete'] ?? 0" 
                    label="Suppressions"
                    color="danger">
                </x-stats-card>
            </div>
        </div>

        {{-- Filtres --}}
        <x-premium-card title="Filtres" class="mb-4">
            <form method="GET" action="{{ route('admin.audit.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label>Action</label>
                        <select name="action" class="form-control form-control-modern">
                            <option value="">Toutes</option>
                            <option value="create" {{ request('action') == 'create' ? 'selected' : '' }}>Création</option>
                            <option value="update" {{ request('action') == 'update' ? 'selected' : '' }}>Modification</option>
                            <option value="delete" {{ request('action') == 'delete' ? 'selected' : '' }}>Suppression</option>
                            <option value="login" {{ request('action') == 'login' ? 'selected' : '' }}>Connexion</option>
                            <option value="logout" {{ request('action') == 'logout' ? 'selected' : '' }}>Déconnexion</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Type d'entité</label>
                        <select name="entity_type" class="form-control form-control-modern">
                            <option value="">Tous</option>
                            <option value="Collecte" {{ request('entity_type') == 'Collecte' ? 'selected' : '' }}>Collecte</option>
                            <option value="Facture" {{ request('entity_type') == 'Facture' ? 'selected' : '' }}>Facture</option>
                            <option value="Site" {{ request('entity_type') == 'Site' ? 'selected' : '' }}>Site</option>
                            <option value="User" {{ request('entity_type') == 'User' ? 'selected' : '' }}>Utilisateur</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Date début</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control form-control-modern">
                    </div>
                    <div class="col-md-3">
                        <label>Date fin</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control form-control-modern">
                    </div>
                    <div class="col-12">
                        <x-button-gradient type="primary" icon="bi-search">
                            Filtrer
                        </x-button-gradient>
                        <a href="{{ route('admin.audit.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Réinitialiser
                        </a>
                        <a href="{{ route('admin.audit.export', request()->all()) }}" class="btn btn-outline-success">
                            <i class="bi bi-download"></i> Exporter CSV
                        </a>
                    </div>
                </div>
            </form>
        </x-premium-card>

        {{-- Tableau des logs --}}
        <x-premium-card title="Logs d'Audit">
            <div class="table-responsive">
                <table class="table table-premium" data-server-search data-total="{{ $logs->total() }}">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Utilisateur</th>
                            <th>Action</th>
                            <th>Entité</th>
                            <th>Description</th>
                            <th>IP</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td>
                                <small>{{ $log->performed_at->format('d/m/Y H:i:s') }}</small>
                            </td>
                            <td>
                                @if($log->user)
                                    {{ $log->user->firstname }} {{ $log->user->lastname }}
                                @else
                                    <em class="text-muted">Système</em>
                                @endif
                            </td>
                            <td>
                                @php
                                    $badgeType = match($log->action) {
                                        'create' => 'success',
                                        'update' => 'warning',
                                        'delete' => 'danger',
                                        'login' => 'info',
                                        'logout' => 'secondary',
                                        default => 'primary'
                                    };
                                @endphp
                                <x-badge-modern :type="$badgeType">
                                    {{ ucfirst($log->action) }}
                                </x-badge-modern>
                            </td>
                            <td>
                                <strong>{{ $log->entity_type }}</strong>
                                @if($log->entity_id)
                                    <br><small class="text-muted">#{{ $log->entity_id }}</small>
                                @endif
                            </td>
                            <td>{{ $log->description }}</td>
                            <td><small class="text-muted">{{ $log->ip_address }}</small></td>
                            <td>
                                <a href="{{ route('admin.audit.show', $log->audit_id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Détails
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Aucun log trouvé
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $logs->links() }}
            </div>
        </x-premium-card>
    </section>
</main>
@endsection
