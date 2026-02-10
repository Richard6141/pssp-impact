@extends('layouts.back')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
        <h1>Détails Utilisateur</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Utilisateurs</a></li>
                <li class="breadcrumb-item active">Détails</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Informations</h5>
                        <div class="mb-3 d-flex align-items-center gap-3">
                            <img src="{{ $user->profile_image ? asset('storage/'.$user->profile_image) : 'https://st2.depositphotos.com/1104517/11967/v/950/depositphotos_119675554-stock-illustration-male-avatar-profile-picture-vector.jpg' }}"
                                alt="Avatar" class="rounded-circle" width="72" height="72">
                            <div>
                                <div class="fw-bold">{{ $user->firstname }} {{ $user->lastname }}</div>
                                <div class="text-muted">{{ $user->email }}</div>
                            </div>
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><strong>Nom :</strong> {{ $user->firstname }}
                                {{ $user->lastname }}</li>
                            <li class="list-group-item"><strong>Email :</strong> {{ $user->email }}</li>
                            <li class="list-group-item"><strong>Username :</strong> {{ $user->username }}</li>
                            <li class="list-group-item"><strong>Rôle(s) :</strong>
                                {{ $user->getRoleNames()->implode(', ') ?: 'Aucun' }}</li>
                            <li class="list-group-item"><strong>Statut :</strong>
                                {{ $user->isActive ? 'Actif' : 'Inactif' }}</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Statistiques</h5>
                        @if(!empty($stats))
                        <ul class="list-group list-group-flush">
                            @foreach($stats as $label => $data)
                            <li class="list-group-item">
                                <strong>{{ ucfirst(str_replace('_', ' ', $label)) }} :</strong>
                                @if(is_array($data))
                                {{ json_encode($data) }}
                                @else
                                {{ $data }}
                                @endif
                            </li>
                            @endforeach
                        </ul>
                        @else
                        <div class="text-muted">Aucune statistique disponible.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Retour</a>
            @can('users.update')
            <a href="{{ route('users.edit', $user->user_id) }}" class="btn btn-primary">Modifier</a>
            @endcan
        </div>
    </section>

</main>
@endsection
