@extends('layouts.back')

@section('content')
<main id="main" class="main">

    <div class="pagetitle d-flex justify-content-between align-items-center">
        <h1>Validations des collectes</h1>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Liste des collectes</h5>

                        <div class="table-responsive">
                            <table class="table datatable table-hover align-middle text-sm"
                                style="white-space: nowrap; width: 100%;">
                                <thead class="table-dark text-center">
                                    <tr>
                                        <th>#</th>
                                        <th>Collecte</th>
                                        <th>Site</th>
                                        <th>Validé par</th>
                                        <th>Type</th>
                                        <th>Date</th>
                                        <th>Commentaire</th>
                                        <th>Signature</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="text-center">
                                    @forelse($collectes as $index => $collecte)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $collecte->titre ?? '-' }}</td>
                                        <td>{{ $collecte->site?->site_name ?? '-' }}</td>
                                        <td>
                                            {{ $collecte->validation?->validator?->firstname ?? '-' }}
                                            {{ $collecte->validation?->validator?->lastname ?? '' }}
                                        </td>
                                        <td>{{ ucfirst($collecte->validation?->type_validation ?? '-') }}</td>
                                        <td>
                                            {{ $collecte->validation
                                                ? \Carbon\Carbon::parse($collecte->validation->date_validation)->format('d/m/Y H:i')
                                                : '-' }}
                                        </td>
                                        <td>{{ $collecte->validation?->commentaire ?? '-' }}</td>
                                        <td>
                                            @if($collecte->validation?->signature)
                                            <img src="{{ asset('storage/'.$collecte->validation->signature) }}"
                                                alt="Signature" width="100">
                                            @else
                                            -
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if(!$collecte->validation)
                                            <!-- Modification du lien pour passer collecte_id en paramètre de requête -->
                                            <a href="{{ route('validations.create', ['collecte_id' => $collecte->collecte_id]) }}"
                                                class="btn btn-sm btn-success">Valider</a>
                                            @else
                                            <a href="{{ route('validations.show', $collecte->validation->validation_id) }}"
                                                class="btn btn-sm btn-info">Voir</a>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="9">Aucune collecte trouvée.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-3">
                            {{ $collectes->links() }}
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