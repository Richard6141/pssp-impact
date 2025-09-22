@extends('layouts.back')

@section('content')
<main id="main" class="main">
    <div class="pagetitle d-flex justify-content-between align-items-center">
        <h1>Observations</h1>
        <a href="{{ route('observations.create') }}"
            class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center"
            style="width:45px; height:45px;">
            <i class="bi bi-plus-lg"></i>
        </a>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Liste des observations</h5>

                        <div class="table-responsive">
                            <table class="table datatable table-hover align-middle text-sm text-nowrap">

                                style="white-space: nowrap;">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Site</th>
                                        <th>Utilisateur</th>
                                        <th>Contenu</th>
                                        <th>Date</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($observations as $index => $obs)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $obs->site?->site_name ?? '—' }}</td>
                                        <td>{{ $obs->user?->firstname }} {{ $obs->user?->lastname }}</td>
                                        <td>{{ Str::limit($obs->contenu, 50) }}</td>
                                        <td>{{ \Carbon\Carbon::parse($obs->date_obs)->format('d/m/Y H:i') }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('observations.show', $obs) }}" class="btn btn-sm btn-info"
                                                title="Voir"><i class="bi bi-eye"></i></a>
                                            <a href="{{ route('observations.edit', $obs) }}"
                                                class="btn btn-sm btn-warning" title="Éditer"><i
                                                    class="bi bi-pencil"></i></a>
                                            <form action="{{ route('observations.destroy', $obs) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" data-confirm-delete
                                                    data-item-name="Facture #{{ $obs}}"
                                                    data-confirm-title="Supprimer cette observation ?"
                                                    data-confirm-text="Voulez-vous vraiment supprimer cette observation ? Cette action est irréversible."
                                                    title="Supprimer" data-bs-toggle="tooltip">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-3">
                            {{ $observations->links() }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const table = document.querySelector('.datatable');
        if (table) {
            new simpleDatatables.DataTable(table, {
                searchable: true,
                fixedHeight: false,
                perPageSelect: true,
                perPage: 10
            });
        }
    });
</script>
@endsection