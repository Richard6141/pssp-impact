@extends('layouts.back')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
        <h1>Export comptable</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Export comptable</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Vue exportable</h5>

                <form method="GET" class="row g-3 mb-3">
                    <div class="col-md-4">
                        <select name="site_id" class="form-select">
                            <option value="">Tous les sites</option>
                            @foreach($sites as $site)
                            <option value="{{ $site->site_id }}" @selected(request('site_id')===$site->site_id)>
                                {{ $site->site_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select name="comptable_id" class="form-select">
                            <option value="">Tous les comptables</option>
                            @foreach($comptables as $comptable)
                            <option value="{{ $comptable->user_id }}"
                                @selected(request('comptable_id')===$comptable->user_id)>
                                {{ $comptable->firstname }} {{ $comptable->lastname }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="statut_facture" class="form-select">
                            <option value="">Tous les statuts</option>
                            @foreach($statuts as $statut)
                            <option value="{{ $statut }}" @selected(request('statut_facture')===$statut)>
                                {{ $statut }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-primary w-100">Filtrer</button>
                    </div>
                </form>

                @can('rapports.export')
                <div class="d-flex justify-content-end gap-2 mb-3">
                    <a href="{{ route('comptabilite.export.csv', request()->query()) }}"
                        class="btn btn-outline-success btn-sm">
                        <i class="bi bi-filetype-csv"></i> CSV
                    </a>
                    <a href="{{ route('comptabilite.export.txt', request()->query()) }}"
                        class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-file-earmark-text"></i> TXT
                    </a>
                    <a href="{{ route('comptabilite.export.excel', request()->query()) }}"
                        class="btn btn-outline-success btn-sm">
                        <i class="bi bi-file-earmark-excel"></i> Excel
                    </a>
                </div>
                @endcan

                <div class="table-responsive">
                    <table class="table table-striped align-middle table-nowrap">
                        <thead class="table-dark">
                            <tr>
                                <th>Facture</th>
                                <th>Date</th>
                                <th>Montant</th>
                                <th>Statut</th>
                                <th>Site</th>
                                <th>Comptable</th>
                                <th>Paiement</th>
                                <th>Montant payé</th>
                                <th>Date paiement</th>
                                <th>Mode</th>
                                <th>Statut paiement</th>
                                <th>Solde</th>
                                <th class="text-center">Détails</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $row)
                            <tr>
                                <td>{{ $row->numero_facture }}</td>
                                <td>{{ \Carbon\Carbon::parse($row->date_facture)->format('d/m/Y') }}</td>
                                <td>{{ number_format($row->montant_facture, 2, ',', ' ') }}</td>
                                <td>{{ $row->statut_facture }}</td>
                                <td>{{ $row->site_name }}</td>
                                <td>{{ $row->firstname }} {{ $row->lastname }}</td>
                                <td>{{ $row->numero_paiement }}</td>
                                <td>{{ number_format($row->montant_paye, 2, ',', ' ') }}</td>
                                <td>
                                    @if($row->date_paiement)
                                    {{ \Carbon\Carbon::parse($row->date_paiement)->format('d/m/Y') }}
                                    @endif
                                </td>
                                <td>{{ $row->mode_paiement }}</td>
                                <td>{{ $row->statut_paiement }}</td>
                                <td>{{ number_format($row->solde_restant, 2, ',', ' ') }}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#paiements-{{ $row->facture_id }}">
                                        Voir
                                    </button>
                                </td>
                            </tr>
                            <tr class="collapse" id="paiements-{{ $row->facture_id }}">
                                <td colspan="13">
                                    <div class="p-2">
                                        <strong>Paiements liés</strong>
                                        <div class="table-responsive mt-2">
                                            <table class="table table-sm table-bordered mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>N°</th>
                                                        <th>Date</th>
                                                        <th>Montant</th>
                                                        <th>Mode</th>
                                                        <th>Statut</th>
                                                        <th>Référence</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                        $paiements = $paiementsByFacture[$row->facture_id] ?? collect();
                                                    @endphp
                                                    @forelse($paiements as $p)
                                                    <tr>
                                                        <td>{{ $p->numero_paiement }}</td>
                                                        <td>{{ \Carbon\Carbon::parse($p->date_paiement)->format('d/m/Y') }}</td>
                                                        <td>{{ number_format($p->montant, 2, ',', ' ') }}</td>
                                                        <td>{{ $p->mode_paiement }}</td>
                                                        <td>{{ $p->statut }}</td>
                                                        <td>{{ $p->reference }}</td>
                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center">Aucun paiement.</td>
                                                    </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="13" class="text-center">Aucune donnée.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
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
