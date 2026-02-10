@extends('layouts.back')

@section('content')
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Détail du log d'audit</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.audit.index') }}">Audit</a></li>
                <li class="breadcrumb-item active">Détail</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-8">
                <x-premium-card title="Informations générales">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <th width="200">Date & Heure :</th>
                                <td>{{ $log->performed_at->format('d/m/Y à H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th>Utilisateur :</th>
                                <td>
                                    @if($log->user)
                                        {{ $log->user->firstname }} {{ $log->user->lastname }}
                                        <small class="text-muted">({{ $log->user->email }})</small>
                                    @else
                                        <em class="text-muted">Système</em>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Action :</th>
                                <td>
                                    @php
                                        $badgeType = match($log->action) {
                                            'create' => 'success',
                                            'update' => 'warning',
                                            'delete' => 'danger',
                                            default => 'primary'
                                        };
                                    @endphp
                                    <x-badge-modern :type="$badgeType">
                                        {{ ucfirst($log->action) }}
                                    </x-badge-modern>
                                </td>
                            </tr>
                            <tr>
                                <th>Entité :</th>
                                <td>
                                    <strong>{{ $log->entity_type }}</strong>
                                    @if($log->entity_id)
                                        <span class="text-muted">#{{ $log->entity_id }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Description :</th>
                                <td>{{ $log->description }}</td>
                            </tr>
                            <tr>
                                <th>Adresse IP :</th>
                                <td><code>{{ $log->ip_address }}</code></td>
                            </tr>
                            <tr>
                                <th>User Agent :</th>
                                <td><small class="text-muted">{{ $log->user_agent }}</small></td>
                            </tr>
                        </tbody>
                    </table>
                </x-premium-card>

                @if($log->old_values || $log->new_values)
                <div class="row mt-4">
                    @if($log->old_values)
                    <div class="col-md-6">
                        <x-premium-card title="Anciennes valeurs" class="h-100">
                            <pre class="bg-light p-3 rounded"><code>{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                        </x-premium-card>
                    </div>
                    @endif

                    @if($log->new_values)
                    <div class="col-md-6">
                        <x-premium-card title="Nouvelles valeurs" class="h-100">
                            <pre class="bg-light p-3 rounded"><code>{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                        </x-premium-card>
                    </div>
                    @endif
                </div>
                @endif
            </div>

            <div class="col-lg-4">
                <x-premium-card title="Actions">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.audit.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Retour à la liste
                        </a>
                    </div>
                </x-premium-card>
            </div>
        </div>
    </section>
</main>
@endsection
