@extends('layouts.back')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
        <h1>Types de déchets</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                <li class="breadcrumb-item">Gestion</li>
                <li class="breadcrumb-item active">Types de déchets</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="row">
            <div class="col-lg-12">

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title d-flex justify-content-between align-items-center">
                            Liste des types de déchets

                            <!-- Bouton rond bleu -->
                            <a href="{{ route('type_dechets.create') }}"
                                class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center"
                                style="width:45px; height:45px;">
                                <i class="bi bi-plus-lg"></i>
                            </a>
                        </h5>

                        <!-- Table avec DataTables -->
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Libellé</th>
                                    <th>Code DBM</th>
                                    <th>Date de création</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($types as $index => $type)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $type->libelle }}</td>
                                    <td>{{ $type->code ?? '—' }}</td>
                                    <td>{{ $type->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <!-- <a href="" class="btn btn-sm btn-info">Voir</a> -->
                                        <a href="{{ route('type_dechets.edit', $type->type_dechet_id) }}"
                                            class="btn btn-sm btn-warning">Modifier</a>

                                        <form action="{{ route('type_dechets.destroy', $type->type_dechet_id) }}"
                                            method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" data-confirm-delete
                                                data-item-name="Facture #{{ $type->type_dechet_id}}"
                                                data-confirm-title="Supprimer cette ce type ?"
                                                data-confirm-text="Voulez-vous vraiment supprimer ce type ? Cette action est irréversible."
                                                title="Supprimer" data-bs-toggle="tooltip">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <!-- End Table -->

                    </div>
                </div>

            </div>
        </div>
    </section>

</main><!-- End #main -->
@endsection