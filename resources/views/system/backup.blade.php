@extends('layouts.back')

@section('title', 'Gestion des Sauvegardes')

@section('content')
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Gestion des Sauvegardes</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
                <li class="breadcrumb-item">Système</li>
                <li class="breadcrumb-item active">Sauvegardes</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <!-- Actions rapides -->
        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Actions</h5>
                        <button type="button" class="btn btn-primary" id="createBackupBtn">
                            <i class="bi bi-plus-circle"></i> Créer une nouvelle sauvegarde
                        </button>
                        <button type="button" class="btn btn-info" onclick="location.reload()">
                            <i class="bi bi-arrow-clockwise"></i> Actualiser
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="bi bi-hdd text-primary display-6"></i>
                        <h5 class="mt-2">Sauvegardes</h5>
                        <h3 class="text-primary">{{ count($backups) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="bi bi-calendar-check text-success display-6"></i>
                        <h5 class="mt-2">Dernière sauvegarde</h5>
                        <h6 class="text-success">
                            @if(count($backups) > 0)
                            {{ $backups->first()['date']->diffForHumans() }}
                            @else
                            Aucune
                            @endif
                        </h6>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="bi bi-file-earmark-zip text-info display-6"></i>
                        <h5 class="mt-2">Espace utilisé</h5>
                        <h6 class="text-info">
                            {{ $backups->sum(function($b) { return floatval(str_replace(' MB', '', $b['size'])); }) }}
                            MB
                        </h6>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des sauvegardes -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Liste des sauvegardes disponibles</h5>
                    </div>
                    <div class="card-body">
                        @if(count($backups) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nom du fichier</th>
                                        <th>Date de création</th>
                                        <th>Taille</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($backups as $backup)
                                    <tr>
                                        <td>
                                            <i class="bi bi-file-earmark-zip text-primary"></i>
                                            {{ $backup['name'] }}
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <i class="bi bi-clock"></i> {{ $backup['date']->format('d/m/Y H:i:s') }}
                                                <br>
                                                <span
                                                    class="badge bg-secondary">{{ $backup['date']->diffForHumans() }}</span>
                                            </small>
                                        </td>
                                        <td>{{ $backup['size'] }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('system.backup.download', $backup['name']) }}"
                                                class="btn btn-sm btn-success" title="Télécharger">
                                                <i class="bi bi-download"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger delete-backup-btn"
                                                data-filename="{{ $backup['name'] }}" title="Supprimer">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-5">
                            <i class="bi bi-hdd display-1 text-muted"></i>
                            <h4 class="text-muted mt-3">Aucune sauvegarde disponible</h4>
                            <p class="text-muted">Créez votre première sauvegarde pour commencer.</p>
                            <button type="button" class="btn btn-primary mt-3" id="createBackupBtnEmpty">
                                <i class="bi bi-plus-circle"></i> Créer une sauvegarde
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Informations importantes -->
        <div class="row mt-4">
            <div class="col-lg-12">
                <div class="alert alert-info">
                    <h5><i class="bi bi-info-circle"></i> Informations importantes</h5>
                    <ul class="mb-0">
                        <li>Les sauvegardes contiennent une copie complète de la base de données</li>
                        <li>Il est recommandé de créer des sauvegardes régulières</li>
                        <li>Téléchargez et stockez les sauvegardes dans un endroit sûr</li>
                        <li>Les anciennes sauvegardes peuvent être supprimées pour libérer de l'espace</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal de chargement -->
    <div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-5">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <h5>Création de la sauvegarde en cours...</h5>
                    <p class="text-muted mb-0">Veuillez patienter, cela peut prendre quelques instants.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmer la suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Attention !</strong> Cette action est irréversible.
                    </div>
                    <p>Êtes-vous sûr de vouloir supprimer cette sauvegarde ?</p>
                    <p class="text-muted mb-0"><strong id="backupToDelete"></strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <i class="bi bi-trash"></i> Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
    (function() {
        'use strict';

        let backupToDelete = null;

        // Attendre que le DOM soit chargé
        document.addEventListener('DOMContentLoaded', function() {

            // Bouton créer sauvegarde (en haut)
            const createBackupBtn = document.getElementById('createBackupBtn');
            if (createBackupBtn) {
                createBackupBtn.addEventListener('click', createBackup);
            }

            // Bouton créer sauvegarde (quand liste vide)
            const createBackupBtnEmpty = document.getElementById('createBackupBtnEmpty');
            if (createBackupBtnEmpty) {
                createBackupBtnEmpty.addEventListener('click', createBackup);
            }

            // Boutons supprimer
            document.querySelectorAll('.delete-backup-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const filename = this.getAttribute('data-filename');
                    deleteBackup(filename);
                });
            });

            // Bouton confirmation suppression
            const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
            if (confirmDeleteBtn) {
                confirmDeleteBtn.addEventListener('click', confirmDelete);
            }
        });

        // Fonction pour créer une sauvegarde
        function createBackup() {
            const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
            loadingModal.show();

            fetch('{{ route("system.backup.create") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    loadingModal.hide();
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Succès',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur',
                            text: data.message
                        });
                    }
                })
                .catch(error => {
                    loadingModal.hide();
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: 'Une erreur est survenue lors de la création de la sauvegarde'
                    });
                });
        }

        // Fonction pour afficher le modal de suppression
        function deleteBackup(filename) {
            backupToDelete = filename;
            document.getElementById('backupToDelete').textContent = filename;
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        }

        // Fonction pour confirmer la suppression
        function confirmDelete() {
            if (!backupToDelete) return;

            const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
            deleteModal.hide();

            fetch('/system/backup/' + backupToDelete, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Succès',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur',
                            text: data.message
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: 'Une erreur est survenue'
                    });
                });

            backupToDelete = null;
        }

    })();
</script>
@endpush

@push('styles')
<style>
    .table td {
        vertical-align: middle;
    }
</style>
@endpush