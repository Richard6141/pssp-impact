@extends('layouts.back')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
        <h1>Journal comptable</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Journal comptable</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Écritures</h5>

                <form method="GET" class="row g-3 mb-3">
                    <div class="col-md-2">
                        <input type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_fin" class="form-control" value="{{ request('date_fin') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="type_piece" class="form-select">
                            <option value="">Type</option>
                            @foreach(['facture','paiement','avoir','charge'] as $type)
                            <option value="{{ $type }}" @selected(request('type_piece')===$type)>
                                {{ ucfirst($type) }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="compte" class="form-control" placeholder="Compte"
                            value="{{ request('compte') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-primary w-100">Filtrer</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('comptabilite.journal') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </form>
                @can('rapports.export')
                <div class="d-flex justify-content-end mb-3">
                    <a href="{{ route('comptabilite.journal.pdf', request()->query()) }}"
                        class="btn btn-danger btn-sm">
                        <i class="bi bi-file-earmark-pdf"></i> PDF
                    </a>
                </div>
                @endcan

                <div class="table-responsive">
                    <table class="table table-striped align-middle table-nowrap">
                        <thead class="table-dark">
                            <tr>
                                <th>Date</th>
                                <th>Pièce</th>
                                <th>Type</th>
                                <th>Débit</th>
                                <th>Crédit</th>
                                <th>Libellé</th>
                                <th>Montant</th>
                                <th>Saisi par</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ecritures as $ecriture)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($ecriture->date_ecriture)->format('d/m/Y') }}</td>
                                <td>{{ $ecriture->numero_piece }}</td>
                                <td>{{ ucfirst($ecriture->type_piece) }}</td>
                                <td>{{ $ecriture->compte_debit }}</td>
                                <td>{{ $ecriture->compte_credit }}</td>
                                <td>{{ $ecriture->libelle }}</td>
                                <td>{{ number_format($ecriture->montant, 2, ',', ' ') }}</td>
                                <td>{{ $ecriture->user?->firstname }} {{ $ecriture->user?->lastname }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">Aucune écriture.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-3">
                    {{ $ecritures->links() }}
                </div>
            </div>
        </div>
    </section>

</main>

<style>
    .table-nowrap th,
    .table-nowrap td {
        white-space: nowrap;
    }
</style>
@endsection
