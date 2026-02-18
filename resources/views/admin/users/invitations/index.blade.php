@extends('layouts.back')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
        <h1>Gestion des invitations</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
                <li class="breadcrumb-item">Utilisateurs</li>
                <li class="breadcrumb-item active">Invitations</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">

                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-1"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-octagon me-1"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                <div class="card premium-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title mb-0">Liste des invitations</h5>
                            <a href="{{ route('admin.users.invitations.create') }}" class="btn btn-primary-flat">
                                <i class="bi bi-plus-circle me-1"></i> Nouvelle invitation
                            </a>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-premium">
                                <thead>
                                    <tr>
                                        <th>Email</th>
                                        <th>Rôle</th>
                                        <th>Site</th>
                                        <th>Invité par</th>
                                        <th>Statut</th>
                                        <th>Envoyé le</th>
                                        <th>Expire le</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($invitations as $invitation)
                                    <tr>
                                        <td>{{ $invitation->email }}</td>
                                        <td>
                                            @if($invitation->role_id)
                                                <span class="badge bg-info">Défini (ID: {{ $invitation->role_id }})</span>
                                            @else
                                                <span class="badge bg-secondary">Aucun</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($invitation->site)
                                                <div>{{ $invitation->site->site_name }}</div>
                                                @if($invitation->assign_as_site_responsable)
                                                    <small class="text-success">Responsable du site</small>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($invitation->inviter)
                                                {{ $invitation->inviter->firstname }} {{ $invitation->inviter->lastname }}
                                            @else
                                                <span class="text-muted">Système</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($invitation->registered_at)
                                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Acceptée</span>
                                            @elseif($invitation->isExpired())
                                                <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i> Expirée</span>
                                            @else
                                                <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i> En attente</span>
                                            @endif
                                        </td>
                                        <td>{{ $invitation->created_at->format('d/m/Y H:i') }}</td>
                                        <td>{{ $invitation->expires_at->format('d/m/Y H:i') }}</td>
                                        <td class="text-end">
                                            @if(!$invitation->registered_at)
                                                <form action="{{ route('admin.users.invitations.resend', $invitation->id) }}" method="POST" class="d-inline-block">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-primary" title="Renvoyer l'email">
                                                        <i class="bi bi-envelope"></i>
                                                    </button>
                                                </form>

                                                {{-- Bouton TEST pour copier le lien --}}
                                                <button type="button" class="btn btn-sm btn-outline-secondary" title="Copier le lien (Dev)" 
                                                    onclick="prompt('Copiez ce lien pour tester :', '{{ route('invitation.accept.store', $invitation->token) }}'.replace('/store', ''))">
                                                    <i class="bi bi-link-45deg"></i>
                                                </button>

                                                <form action="{{ route('admin.users.invitations.destroy', $invitation->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette invitation ?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Annuler l'invitation">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-muted small">Terminé</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">Aucune invitation trouvée.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $invitations->links() }}
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

</main>
@endsection
