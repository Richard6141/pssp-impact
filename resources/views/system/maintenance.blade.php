@extends('layouts.back')

@section('title', 'Mode Maintenance')

@section('content')
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Mode Maintenance</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
                <li class="breadcrumb-item">Système</li>
                <li class="breadcrumb-item active">Maintenance</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <!-- État actuel -->
        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        @if($isDown)
                        <i class="bi bi-exclamation-triangle-fill text-warning display-1"></i>
                        <h2 class="mt-4 text-warning">Mode Maintenance Activé</h2>
                        <p class="text-muted">Le site est actuellement en mode maintenance pour tous les utilisateurs
                            sauf
                            les administrateurs.</p>
                        <button type="button" class="btn btn-success btn-lg mt-3" onclick="toggleMaintenance()">
                            <i class="bi bi-check-circle"></i> Désactiver le mode maintenance
                        </button>
                        @else
                        <i class="bi bi-check-circle-fill text-success display-1"></i>
                        <h2 class="mt-4 text-success">Site Opérationnel</h2>
                        <p class="text-muted">Le site fonctionne normalement et est accessible à tous les utilisateurs.
                        </p>
                        <button type="button" class="btn btn-warning btn-lg mt-3" onclick="toggleMaintenance()">
                            <i class="bi bi-exclamation-triangle"></i> Activer le mode maintenance
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Informations sur le mode maintenance -->
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-info-circle text-primary"></i> Qu'est-ce que le mode maintenance ?
                        </h5>
                        <p>Le mode maintenance permet de rendre le site temporairement inaccessible aux utilisateurs
                            pendant
                            les opérations de maintenance.</p>
                        <ul>
                            <li>Les utilisateurs verront une page de maintenance personnalisée</li>
                            <li>Les administrateurs peuvent toujours accéder au site</li>
                            <li>Utile lors de mises à jour importantes</li>
                            <li>Évite les erreurs pour les utilisateurs pendant les modifications</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-list-check text-success"></i> Quand utiliser le mode maintenance ?
                        </h5>
                        <ul class="mb-0">
                            <li><strong>Mises à jour de la base de données</strong> : Modifications de structure
                                importantes
                            </li>
                            <li><strong>Déploiement de nouvelles fonctionnalités</strong> : Installation de modules
                                majeurs
                            </li>
                            <li><strong>Maintenance serveur</strong> : Opérations d'infrastructure</li>
                            <li><strong>Corrections critiques</strong> : Résolution de bugs majeurs</li>
                            <li><strong>Optimisations</strong> : Nettoyage et optimisation des performances</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conseils -->
        <div class="row mt-4">
            <div class="col-lg-12">
                <div class="alert alert-info">
                    <h5><i class="bi bi-lightbulb"></i> Conseils</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="mb-0">
                                <li>Planifiez la maintenance en dehors des heures de pointe</li>
                                <li>Informez les utilisateurs à l'avance si possible</li>
                                <li>Testez vos modifications avant la mise en production</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="mb-0">
                                <li>Désactivez le mode maintenance dès que possible</li>
                                <li>Vérifiez que tout fonctionne avant de réactiver le site</li>
                                <li>Gardez une trace des opérations effectuées</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historique récent (optionnel) -->
        <div class="row mt-4">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-clock-history"></i> Actions récentes
                        </h5>
                        <div class="alert alert-secondary">
                            <p class="mb-0">
                                <i class="bi bi-info-circle"></i>
                                État actuel: <strong>{{ $isDown ? 'En maintenance' : 'Opérationnel' }}</strong>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal de confirmation -->
    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert" id="modalAlert"></div>
                    <p id="modalMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn" id="confirmBtn" onclick="confirmToggle()"></button>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
    const isDown = @json($isDown);

    function toggleMaintenance() {
        const modal = document.getElementById('confirmModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalAlert = document.getElementById('modalAlert');
        const modalMessage = document.getElementById('modalMessage');
        const confirmBtn = document.getElementById('confirmBtn');

        if (isDown) {
            modalTitle.textContent = 'Désactiver le mode maintenance';
            modalAlert.className = 'alert alert-success';
            modalAlert.innerHTML = '<i class="bi bi-check-circle"></i> <strong>Réactivation du site</strong>';
            modalMessage.textContent = 'Le site sera de nouveau accessible à tous les utilisateurs.';
            confirmBtn.className = 'btn btn-success';
            confirmBtn.innerHTML = '<i class="bi bi-check-circle"></i> Désactiver';
        } else {
            modalTitle.textContent = 'Activer le mode maintenance';
            modalAlert.className = 'alert alert-warning';
            modalAlert.innerHTML = '<i class="bi bi-exclamation-triangle"></i> <strong>Attention !</strong>';
            modalMessage.textContent = 'Le site sera inaccessible pour tous les utilisateurs (sauf les administrateurs).';
            confirmBtn.className = 'btn btn-warning';
            confirmBtn.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Activer';
        }

        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    }

    function confirmToggle() {
        const confirmModal = document.getElementById('confirmModal');
        const modalInstance = bootstrap.Modal.getInstance(confirmModal);
        if (modalInstance) {
            modalInstance.hide();
        }

        Swal.fire({
            title: 'Traitement en cours...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch('{{ route("system.maintenance.toggle") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur réseau');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Si un secret est retourné (activation de la maintenance)
                    if (data.secret) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Succès',
                            html: data.message +
                                '<br><small class="text-muted">Redirection en cours...</small>',
                            timer: 1500,
                            showConfirmButton: false,
                            allowOutsideClick: false
                        }).then(() => {
                            // Rediriger vers l'URL avec le secret pour maintenir l'accès admin
                            window.location.href = '/' + data.secret + '/system/maintenance';
                        });
                    } else {
                        // Désactivation de la maintenance - recharger normalement
                        Swal.fire({
                            icon: 'success',
                            title: 'Succès',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false,
                            allowOutsideClick: false
                        }).then(() => {
                            window.location.reload(true);
                        });
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: data.message || 'Une erreur est survenue'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Une erreur est survenue lors du changement de mode'
                });
            });
    }
</script>
@endpush

@push('styles')
<style>
    .card-body.text-center {
        padding: 3rem 1rem;
    }

    .display-1 {
        font-size: 5rem;
    }
</style>
@endpush