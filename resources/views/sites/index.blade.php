@extends('layouts.back')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
        <h1>Sites</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                <li class="breadcrumb-item">Gestion</li>
                <li class="breadcrumb-item active">Sites</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="row">
            <div class="col-12">

                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title m-0">Liste des sites</h5>

                            <!-- Bouton responsive -->
                            @can('sites.create')
                            <a href="{{ route('sites.create') }}"
                                class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center"
                                style="width:45px; height:45px; min-width:45px;" title="Ajouter un site"
                                data-bs-toggle="tooltip">
                                <i class="bi bi-plus-lg"></i>
                            </a>
                            @endcan
                        </div>

                        <!-- Table responsive -->
                        <div class="table-responsive">
                            <table class="table datatable table-striped table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" class="text-nowrap">#</th>
                                        <th scope="col" class="text-nowrap">Nom</th>
                                        <th scope="col" class="d-none d-md-table-cell">Département</th>
                                        <th scope="col" class="d-none d-lg-table-cell">Commune</th>
                                        <th scope="col" class="d-none d-xl-table-cell">Localisation</th>
                                        <th scope="col" class="d-none d-sm-table-cell">Responsable</th>
                                        @canany(['sites.view', 'sites.update', 'sites.delete'])
                                        <th scope="col" class="text-center text-nowrap">Actions</th>
                                        @endcanany
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sites as $index => $site)
                                    <tr>
                                        <td class="fw-bold">{{ $index + 1 }}</td>
                                        <td class="text-nowrap">
                                            <strong>{{ $site->site_name }}</strong>
                                        </td>
                                        <td class="d-none d-md-table-cell">
                                            {{ $site->site_departement }}
                                        </td>
                                        <td class="d-none d-lg-table-cell">
                                            {{ $site->site_commune }}
                                        </td>
                                        <td class="d-none d-xl-table-cell">
                                            <span class="text-truncate d-inline-block" style="max-width: 150px;">
                                                {{ $site->localisation }}
                                            </span>
                                        </td>
                                        <td class="d-none d-sm-table-cell">
                                            {{ $site->responsableUser ? $site->responsableUser->firstname . ' ' . $site->responsableUser->lastname : '—' }}
                                        </td>
                                        @canany(['sites.view', 'sites.update', 'sites.delete'])
                                        <td>
                                            <div class="d-flex flex-nowrap justify-content-center gap-1">
                                                <!-- Bouton Voir -->
                                                @can('sites.view')
                                                <a href="{{ route('sites.show', $site->site_id) }}"
                                                    class="btn btn-sm btn-info d-flex align-items-center"
                                                    title="Voir les détails" data-bs-toggle="tooltip">
                                                    <i class="bi bi-eye d-sm-none"></i>
                                                    <span class="d-none d-sm-inline">Voir</span>
                                                </a>
                                                @endcan

                                                <!-- Bouton Modifier -->
                                                @can('sites.update')
                                                <a href="{{ route('sites.edit', $site->site_id) }}"
                                                    class="btn btn-sm btn-warning d-flex align-items-center"
                                                    title="Modifier" data-bs-toggle="tooltip">
                                                    <i class="bi bi-pencil d-sm-none"></i>
                                                    <span class="d-none d-sm-inline">Modifier</span>
                                                </a>
                                                @endcan

                                                <!-- Bouton Supprimer avec SweetAlert2 -->
                                                @can('sites.delete')
                                                <form action="{{ route('sites.destroy', $site->site_id) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="btn btn-sm btn-danger d-flex align-items-center"
                                                        data-confirm-delete data-item-name="{{ $site->site_name }}"
                                                        data-confirm-title="Supprimer le site"
                                                        data-confirm-text="Voulez-vous vraiment supprimer le site '{{ $site->site_name }}' ? Toutes les données associées seront perdues."
                                                        title="Supprimer" data-bs-toggle="tooltip">
                                                        <i class="bi bi-trash d-sm-none"></i>
                                                        <span class="d-none d-sm-inline">Supprimer</span>
                                                    </button>
                                                </form>
                                                @endcan
                                            </div>
                                        </td>
                                        @endcanany
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

<style>
    /* Styles responsifs supplémentaires */
    @media (max-width: 768px) {
        .card-body {
            padding: 1rem;
        }

        .table-responsive {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
    }

    @media (max-width: 576px) {
        .d-flex.gap-1 {
            gap: 0.25rem !important;
        }

        .btn {
            min-width: 36px;
        }
    }

    /* Amélioration de l'affichage des tooltips */
    .tooltip {
        pointer-events: none;
    }
</style>

<script>
    // Activation des tooltips Bootstrap
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    });
</script>
@endsection