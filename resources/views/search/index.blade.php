@extends('layouts.back')

@section('content')
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Recherche globale</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Recherche</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('search.global') }}" class="row g-2 mt-2">
                    <div class="col-md-10">
                        <input type="text" name="query" class="form-control" value="{{ $query }}" placeholder="Tapez votre recherche..." required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Rechercher
                        </button>
                    </div>
                </form>

                @if($query === '')
                    <div class="alert alert-info mt-3 mb-0">Saisissez un mot-clé pour lancer une recherche.</div>
                @elseif(mb_strlen($query) < 2)
                    <div class="alert alert-warning mt-3 mb-0">Le mot-clé doit contenir au moins 2 caractères.</div>
                @elseif(empty($results))
                    <div class="alert alert-secondary mt-3 mb-0">Aucun résultat trouvé pour "{{ $query }}".</div>
                @else
                    @foreach($results as $section => $items)
                        <h5 class="mt-4 mb-2">{{ $section }} ({{ count($items) }})</h5>
                        <div class="list-group">
                            @foreach($items as $item)
                                <a href="{{ $item['url'] }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <div class="me-3">
                                        <div class="fw-semibold">{{ $item['title'] }}</div>
                                        <small class="text-muted">{{ $item['subtitle'] }}</small>
                                    </div>
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                            @endforeach
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </section>
</main>
@endsection

