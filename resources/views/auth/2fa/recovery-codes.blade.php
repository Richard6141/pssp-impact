@extends('layouts.back')

@section('content')
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Codes de Récupération 2FA</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
                <li class="breadcrumb-item"><a href="{{ route('account.security') }}">Sécurité</a></li>
                <li class="breadcrumb-item active">Codes de Récupération</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <x-premium-card>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Important !</strong> Sauvegardez ces codes dans un endroit sûr. Chaque code ne peut être utilisé qu'une seule fois.
                    </div>

                    <div class="text-center mb-4">
                        <i class="bi bi-key text-primary" style="font-size: 3rem;"></i>
                        <h4 class="mt-3">Nouveaux Codes de Récupération</h4>
                        <p class="text-muted">
                            Utilisez ces codes si vous perdez l'accès à votre application d'authentification
                        </p>
                    </div>

                    <div class="bg-light p-4 rounded mb-4">
                        <div class="row g-3">
                            @foreach(array_chunk($codes, 4) as $chunk)
                            <div class="col-6">
                                @foreach($chunk as $code)
                                <div class="mb-2">
                                    <code class="fs-5">{{ $code }}</code>
                                </div>
                                @endforeach
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-content-center">
                        <button onclick="printCodes()" class="btn btn-outline-primary">
                            <i class="bi bi-printer"></i> Imprimer
                        </button>
                        <button onclick="copyCodes()" class="btn btn-outline-secondary">
                            <i class="bi bi-clipboard"></i> Copier
                        </button>
                        <a href="{{ route('account.security') }}" class="btn btn-gradient-primary">
                            <i class="bi bi-check-circle"></i> J'ai sauvegardé les codes
                        </a>
                    </div>
                </x-premium-card>
            </div>
        </div>
    </section>
</main>

<script>
    function copyCodes() {
        const codes = @json($codes);
        const text = codes.join('\n');
        
        navigator.clipboard.writeText(text).then(() => {
            alert('Codes copiés dans le presse-papiers !');
        }).catch(err => {
            console.error('Erreur lors de la copie:', err);
        });
    }

    function printCodes() {
        window.print();
    }
</script>

<style>
    @media print {
        .main, nav, .btn {
            display: none !important;
        }
        .premium-card {
            box-shadow: none !important;
        }
    }
</style>
@endsection
