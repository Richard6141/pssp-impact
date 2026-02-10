@extends('layouts.back')

@section('content')
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Sessions Actives</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
                <li class="breadcrumb-item">Admin</li>
                <li class="breadcrumb-item active">Sessions</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        {{-- Statistiques --}}
        <div class="row mb-4">
            <div class="col-md-4">
                <x-stats-card 
                    icon="bi-display" 
                    :value="$stats['by_device']['desktop'] ?? 0" 
                    label="Sessions Desktop"
                    color="primary">
                </x-stats-card>
            </div>
            <div class="col-md-4">
                <x-stats-card 
                    icon="bi-phone" 
                    :value="$stats['by_device']['mobile'] ?? 0" 
                    label="Sessions Mobile"
                    color="success">
                </x-stats-card>
            </div>
            <div class="col-md-4">
                <x-stats-card 
                    icon="bi-tablet" 
                    :value="$stats['by_device']['tablet'] ?? 0" 
                    label="Sessions Tablet"
                    color="info">
                </x-stats-card>
            </div>
        </div>

        <x-premium-card>
            <x-slot name="actions">
                <form method="POST" action="{{ route('admin.sessions.destroy-others') }}" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Terminer toutes les autres sessions ?')">
                        <i class="bi bi-x-circle"></i> Terminer les autres sessions
                    </button>
                </form>
            </x-slot>

            <div class="table-responsive">
                <table class="table table-premium">
                    <thead>
                        <tr>
                            <th>Appareil</th>
                            <th>Navigateur</th>
                            <th>Plateforme</th>
                            <th>IP</th>
                            <th>Dernière activité</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sessions as $session)
                        <tr>
                            <td>
                                <i class="bi bi-{{ $session->device_type === 'desktop' ? 'display' : ($session->device_type === 'mobile' ? 'phone' : 'tablet') }} me-2"></i>
                                {{ ucfirst($session->device_type) }}
                            </td>
                            <td>{{ $session->browser }}</td>
                            <td>{{ $session->platform }}</td>
                            <td><code>{{ $session->ip_address }}</code></td>
                            <td>
                                <small class="text-muted">
                                    {{ $session->last_activity->diffForHumans() }}
                                </small>
                            </td>
                            <td>
                                @if($session->is_current)
                                    <x-badge-modern type="success" :glow="true">
                                        <i class="bi bi-check-circle"></i> Session actuelle
                                    </x-badge-modern>
                                @else
                                    <x-badge-modern type="secondary">
                                        Autre session
                                    </x-badge-modern>
                                @endif
                            </td>
                            <td>
                                @if(!$session->is_current)
                                <form method="POST" action="{{ route('admin.sessions.destroy', $session->session_id) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Terminer cette session ?')">
                                        <i class="bi bi-x-circle"></i> Terminer
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Aucune session active
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-premium-card>
    </section>
</main>
@endsection
