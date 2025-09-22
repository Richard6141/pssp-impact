@extends('layouts.back')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
        <h1>Collectes</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                <li class="breadcrumb-item">Gestion</li>
                <li class="breadcrumb-item active">Collectes</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="row">
            <div class="col-lg-12">

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title d-flex justify-content-between align-items-center">
                            Liste des collectes

                            <!-- Bouton rond bleu -->
                            <a href="{{ route('collectes.create') }}"
                                class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center"
                                style="width:40px; height:40px;">
                                <i class="bi bi-plus-lg"></i>
                            </a>
                        </h5>

                        <!-- Table sans retour à la ligne -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle text-sm w-100"
                                style="white-space: nowrap; table-layout: auto;">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Poids (Kg)</th>
                                        <th>Type</th>
                                        <th>Agent</th>
                                        <th>Site</th>
                                        <th>Resp.</th>
                                        <th>Validée</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($collectes as $index => $collecte)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ \Carbon\Carbon::parse($collecte->date_collecte)->format('d/m/Y H:i') }}
                                        </td>
                                        <td>{{ number_format($collecte->poids, 2, ',', ' ') }}</td>
                                        <td>{{ $collecte->typeDechet?->libelle ?? '—' }}</td>
                                        <td>{{ $collecte->agent?->firstname }} {{ $collecte->agent?->lastname }}</td>
                                        <td>{{ $collecte->site?->site_name ?? '—' }}</td>
                                        <td>
                                            @if($collecte->signature_responsable_site)
                                            <span class="badge bg-success">✔</span>
                                            @else
                                            <span class="badge bg-danger">✘</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($collecte->isValid)
                                            <span class="badge bg-success">✔</span>
                                            @else
                                            <span class="badge bg-warning text-dark">…</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('collectes.show', $collecte->collecte_id) }}"
                                                class="btn btn-sm btn-info" title="Voir">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('collectes.edit', $collecte->collecte_id) }}"
                                                class="btn btn-sm btn-warning" title="Modifier">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('collectes.destroy', $collecte->collecte_id) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Confirmer la suppression ?')"
                                                    title="Supprimer">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- End Table -->

                    </div>
                </div>

            </div>
        </div>
    </section>

</main>
@endsection