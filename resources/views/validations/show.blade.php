@extends('layouts.back')

@section('content')
<main id="main" class="main">
    <div class="container">
        <h2>Détails de la validation</h2>

        <ul class="list-group">
            <li class="list-group-item"><strong>Collecte :</strong> {{ $validation->collecte->titre ?? '-' }}</li>
            <li class="list-group-item"><strong>Validé par :</strong>
                {{ $validation->validator->firstname ?? '' }} {{ $validation->validator->lastname ?? '' }}
            </li>
            <li class="list-group-item"><strong>Type :</strong> {{ ucfirst($validation->type_validation) }}</li>
            <li class="list-group-item"><strong>Date :</strong>
                {{ \Carbon\Carbon::parse($validation->date_validation)->format('d/m/Y H:i') }}
            </li>
            <li class="list-group-item"><strong>Commentaire :</strong> {{ $validation->commentaire ?? '-' }}</li>
            <li class="list-group-item"><strong>Signature :</strong><br>
                @if($validation->signature)
                <img src="{{ asset('storage/'.$validation->signature) }}" alt="Signature" width="250">
                @else
                -
                @endif
            </li>
        </ul>

        <div class="mt-3">
            <a href="{{ route('validations.index') }}" class="btn btn-secondary">Retour</a>
        </div>
    </div>
</main>
@endsection