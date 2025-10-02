<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Dashboard - PSSP IMPACT+</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="{{asset('backend/assets/img/favicon.png')}}" rel="icon">
    <link href="{{asset('backend/assets/img/apple-touch-icon.png')}}" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
        rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="{{asset('backend/assets/vendor/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{asset('backend/assets/vendor/bootstrap-icons/bootstrap-icons.css')}}" rel="stylesheet">
    <link href="{{asset('backend/assets/vendor/boxicons/css/boxicons.min.css')}}" rel="stylesheet">
    <link href="{{asset('backend/assets/vendor/quill/quill.snow.css')}}" rel="stylesheet">
    <link href="{{asset('backend/assets/vendor/quill/quill.bubble.css')}}" rel="stylesheet">
    <link href="{{asset('backend/assets/vendor/remixicon/remixicon.css')}}" rel="stylesheet">
    <link href="{{asset('backend/assets/vendor/simple-datatables/style.css')}}" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="{{asset('backend/assets/css/style.css')}}" rel="stylesheet">

    <!-- =======================================================
  * Template Name: NiceAdmin
  * Template URL: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/
  * Updated: Apr 20 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body>

    @include('layouts.partials.header')
    @include('layouts.partials.sidebar')

    @yield('content')

    @include('layouts.partials.footer')

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>

    <!-- Vendor JS Files -->
    <script src="{{asset('backend/assets/vendor/apexcharts/apexcharts.min.js')}}"></script>
    <script src="{{asset('backend/assets/vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
    <script src="{{asset('backend/assets/vendor/chart.js/chart.umd.js')}}"></script>
    <script src="{{asset('backend/assets/vendor/echarts/echarts.min.js')}}"></script>
    <script src="{{asset('backend/assets/vendor/quill/quill.js')}}"></script>
    <script src="{{asset('backend/assets/vendor/simple-datatables/simple-datatables.js')}}"></script>
    <script src="{{asset('backend/assets/vendor/tinymce/tinymce.min.js')}}"></script>
    <script src="{{asset('backend/assets/vendor/php-email-form/validate.js')}}"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Template Main JS File -->
    <script src="{{asset('backend/assets/js/main.js')}}"></script>

    <!-- Scripts personnalisés des pages -->

    <!-- SweetAlert2 -->


    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Script de confirmation global (à placer avant la fermeture du body dans back.blade.php) -->
    <script>
        // Configuration globale pour les confirmations de suppression
        document.addEventListener('DOMContentLoaded', function() {

            // Fonction de confirmation personnalisée
            window.confirmDelete = function(options = {}) {
                const defaults = {
                    title: 'Êtes-vous sûr ?',
                    text: 'Cette action est irréversible !',
                    confirmButtonText: 'Oui, supprimer',
                    cancelButtonText: 'Annuler',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    focusCancel: true
                };

                const config = {
                    ...defaults,
                    ...options
                };

                return Swal.fire(config);
            };

            // Gestionnaire pour les confirmations d'invalidation/validation
            document.querySelectorAll('[data-confirm-action]').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();

                    const form = this.closest('form');
                    const itemName = this.getAttribute('data-item-name') || 'cet élément';
                    const customTitle = this.getAttribute('data-confirm-title') ||
                        'Confirmer l\'action';
                    const customText = this.getAttribute('data-confirm-text') ||
                        'Voulez-vous continuer ?';
                    const confirmBtnText = this.getAttribute('data-confirm-btn') || 'Confirmer';
                    const confirmBtnColor = this.getAttribute('data-confirm-color') || '#0d6efd';

                    Swal.fire({
                        title: customTitle,
                        text: customText,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: confirmBtnColor,
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: confirmBtnText,
                        cancelButtonText: 'Annuler',
                        focusCancel: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Traitement en cours...',
                                allowEscapeKey: false,
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            form.submit();
                        }
                    });
                });
            });

            // Gestionnaire global pour les formulaires de suppression
            const deleteButtons = document.querySelectorAll('[data-confirm-delete]');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();

                    const form = this.closest('form');
                    const itemName = this.getAttribute('data-item-name') || 'cet élément';
                    const customTitle = this.getAttribute('data-confirm-title');
                    const customText = this.getAttribute('data-confirm-text');

                    const options = {};
                    if (customTitle) options.title = customTitle;
                    if (customText) {
                        options.text = customText;
                    } else {
                        options.text =
                            `Voulez-vous vraiment supprimer "${itemName}" ? Cette action est irréversible.`;
                    }

                    confirmDelete(options).then((result) => {
                        if (result.isConfirmed) {
                            // Animation de chargement
                            Swal.fire({
                                title: 'Suppression en cours...',
                                allowEscapeKey: false,
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            form.submit();
                        }
                    });
                });
            });

            // Fonction utilitaire pour confirmation générique
            window.confirmAction = function(options = {}) {
                const defaults = {
                    title: 'Confirmer l\'action',
                    text: 'Voulez-vous continuer ?',
                    confirmButtonText: 'Confirmer',
                    cancelButtonText: 'Annuler',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#0d6efd',
                    cancelButtonColor: '#6c757d'
                };

                const config = {
                    ...defaults,
                    ...options
                };
                return Swal.fire(config);
            };

            // Notification de succès après suppression (si session flash existe)
            @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Succès !',
                text: '{{ session("success") }}',
                timer: 3000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
            @endif

            @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Erreur !',
                text: '{{ session("error") }}',
                confirmButtonText: 'OK',
                confirmButtonColor: '#dc3545'
            });
            @endif
            @if(session('warning'))
            Swal.fire({
                title: 'Action sensible',
                text: 'Cette action peut avoir des conséquences importantes',
                icon: 'warning',
                confirmButtonText: 'OK',
                confirmButtonColor: '#fd7e14',
                html: `
                <div class="text-start">
                    <p><strong>⚠️ Avertissement :</strong></p>
                    <ul class="text-muted small">
                        <li>Cette action peut affecter d'autres données</li>
                        <li>Assurez-vous de comprendre les implications</li>
                        <li>Une sauvegarde pourrait être recommandée</li>
                    </ul>
                </div>
            `
            });
            @endif
        });
    </script>

    @yield('scripts')

</body>

</html>