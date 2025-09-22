@extends('layouts.back')

@section('title', 'Logs Système')

@section('content')
<div class="pagetitle">
    <h1>Logs Système</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
            <li class="breadcrumb-item">Système</li>
            <li class="breadcrumb-item active">Logs</li>
        </ol>
    </nav>
</div>

<section class="section">
    <!-- Filtres -->
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Filtres des logs</h5>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-danger" onclick="clearLogs()">
                            <i class="bi bi-trash"></i> Vider les logs
                        </button>
                        <button type="button" class="btn btn-sm btn-primary" onclick="downloadLogs()">
                            <i class="bi bi-download"></i> Télécharger
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('system.logs') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="level" class="form-label">Niveau</label>
                                <select class="form-select" id="level" name="level">
                                    <option value="">Tous les niveaux</option>
                                    <option value="emergency" {{ $level == 'emergency' ? 'selected' : '' }}>Emergency
                                    </option>
                                    <option value="alert" {{ $level == 'alert' ? 'selected' : '' }}>Alert</option>
                                    <option value="critical" {{ $level == 'critical' ? 'selected' : '' }}>Critical
                                    </option>
                                    <option value="error" {{ $level == 'error' ? 'selected' : '' }}>Error</option>
                                    <option value="warning" {{ $level == 'warning' ? 'selected' : '' }}>Warning</option>
                                    <option value="notice" {{ $level == 'notice' ? 'selected' : '' }}>Notice</option>
                                    <option value="info" {{ $level == 'info' ? 'selected' : '' }}>Info</option>
                                    <option value="debug" {{ $level == 'debug' ? 'selected' : '' }}>Debug</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="search" class="form-label">Rechercher</label>
                                <input type="text" class="form-control" id="search" name="search"
                                    placeholder="Rechercher dans les messages..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="bi bi-funnel"></i> Filtrer
                                </button>
                                <a href="{{ route('system.logs') }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-clockwise"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-exclamation-triangle text-danger display-6"></i>
                    <h5 class="mt-2">Erreurs</h5>
                    <h3 class="text-danger">{{ collect($logs)->where('level', 'error')->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-exclamation-circle text-warning display-6"></i>
                    <h5 class="mt-2">Warnings</h5>
                    <h3 class="text-warning">{{ collect($logs)->where('level', 'warning')->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-info-circle text-info display-6"></i>
                    <h5 class="mt-2">Info</h5>
                    <h3 class="text-info">{{ collect($logs)->where('level', 'info')->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-file-text text-primary display-6"></i>
                    <h5 class="mt-2">Total</h5>
                    <h3 class="text-primary">{{ $total }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Logs -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        Entrées de logs ({{ count($logs) }} sur {{ $total }})
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($logs as $log)
                    <div class="log-entry border-bottom pb-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge bg-{{ 
                                        match($log['level']) {
                                            'emergency', 'alert', 'critical', 'error' => 'danger',
                                            'warning' => 'warning',
                                            'notice', 'info' => 'info',
                                            'debug' => 'secondary',
                                            default => 'primary'
                                        }
                                    }} me-2">
                                        {{ strtoupper($log['level']) }}
                                    </span>
                                    <small class="text-muted">
                                        <i class="bi bi-clock"></i> {{ $log['timestamp'] }}
                                    </small>
                                    <small class="text-muted ms-3">
                                        <i class="bi bi-gear"></i> {{ $log['environment'] }}
                                    </small>
                                </div>
                                <div class="log-message">
                                    <strong>{{ $log['message'] }}</strong>
                                </div>
                                @if(!empty(trim($log['context'])))
                                <div class="log-context mt-2">
                                    <button class="btn btn-sm btn-outline-secondary" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#context-{{ $loop->index }}"
                                        aria-expanded="false">
                                        <i class="bi bi-chevron-down"></i> Détails
                                    </button>
                                    <div class="collapse mt-2" id="context-{{ $loop->index }}">
                                        <div class="card card-body bg-light">
                                            <pre class="mb-0"><code>{{ trim($log['context']) }}</code></pre>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5">
                        <i class="bi bi-file-text display-1 text-muted"></i>
                        <h4 class="text-muted mt-3">Aucun log trouvé</h4>
                        <p class="text-muted">Il n'y a aucune entrée de log correspondant à vos critères.</p>
                    </div>
                    @endforelse

                    <!-- Pagination simple -->
                    @if($total > $perPage)
                    <div class="d-flex justify-content-center mt-4">
                        <nav>
                            <ul class="pagination">
                                @if($page > 1)
                                <li class="page-item">
                                    <a class="page-link" href="?page={{ $page - 1 }}&level={{ $level }}">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                                @endif

                                <li class="page-item active">
                                    <span class="page-link">{{ $page }}</span>
                                </li>

                                @if($page * $perPage < $total) <li class="page-item">
                                    <a class="page-link" href="?page={{ $page + 1 }}&level={{ $level }}">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                    </li>
                                    @endif
                            </ul>
                        </nav>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal de confirmation pour vider les logs -->
<div class="modal fade" id="clearLogsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Vider les logs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Attention !</strong> Cette action supprimera définitivement tous les logs système.
                </div>
                <p>Êtes-vous sûr de vouloir continuer ?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" onclick="confirmClearLogs()">
                    <i class="bi bi-trash"></i> Vider les logs
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function clearLogs() {
        const modal = new bootstrap.Modal(document.getElementById('clearLogsModal'));
        modal.show();
    }

    function confirmClearLogs() {
        // Ici vous pouvez ajouter l'appel AJAX pour vider les logs
        fetch('/system/logs/clear', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erreur lors de la suppression des logs');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erreur lors de la suppression des logs');
            });
    }

    function downloadLogs() {
        window.location.href = '/system/logs/download';
    }

    // Auto-refresh toutes les 30 secondes si on est sur la page sans filtre
    @if(!$level && !request('search'))
    setInterval(function() {
        location.reload();
    }, 30000);
    @endif
</script>
@endpush

@push('styles')
<style>
    .log-entry:last-child {
        border-bottom: none !important;
        margin-bottom: 0 !important;
        padding-bottom: 0 !important;
    }

    .log-message {
        font-family: 'Courier New', monospace;
        font-size: 0.9rem;
        line-height: 1.4;
    }

    .log-context pre {
        font-size: 0.8rem;
        max-height: 300px;
        overflow-y: auto;
    }

    .badge {
        font-size: 0.7rem;
        min-width: 60px;
    }

    .card-body {
        max-height: 70vh;
        overflow-y: auto;
    }
</style>
@endpush