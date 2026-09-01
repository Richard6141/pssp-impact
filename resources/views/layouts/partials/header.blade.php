<!-- ======= Header ======= -->
<header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
        <a href="{{ route('dashboard') }}" class="logo d-flex align-items-center">
            <img src="{{asset('backend/assets/img/logo.png')}}" alt="PSSP IMPACT+">
            <span class="d-none d-lg-block">PSSP IMPACT+</span>
        </a>
        <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->

    <div class="search-bar">
        <form class="search-form d-flex align-items-center" method="GET" action="{{ route('search.global') }}">
            <input type="text" id="globalSearchInput" name="query" placeholder="Rechercher un site, une collecte, un agent..."
                autocomplete="off"
                title="Rechercher des sites, collectes, factures..." value="{{ request('query') }}">
            <button type="submit" title="Rechercher"><i class="bi bi-search"></i></button>
        </form>
        <div id="globalSearchSuggestions" class="global-search-suggestions d-none"></div>
    </div><!-- End Search Bar -->

    <nav class="header-nav ms-auto">
        <ul class="d-flex align-items-center">

            <li class="nav-item d-block d-xl-none">
                <a class="nav-link nav-icon search-bar-toggle" href="#">
                    <i class="bi bi-search"></i>
                </a>
            </li><!-- End Search Icon-->

            <!-- Notifications contextuelles selon le rôle -->
            <li class="nav-item dropdown">
                <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
                    <i class="bi bi-bell"></i>
                    @php
                    $notifications = [];

                    // Notifications pour Agent collecte
                    if(auth()->user()->hasRole('Agent collecte')) {
                    // Collectes en attente de validation
                    $collectesPendantes = \App\Models\Collecte::where('statut', 'en_attente')->count();
                    if($collectesPendantes > 0) {
                    $notifications[] = [
                    'icon' => 'bi-truck',
                    'color' => 'warning',
                    'title' => 'Collectes en attente',
                    'message' => "$collectesPendantes collecte(s) en attente de validation",
                    'time' => 'Maintenant'
                    ];
                    }
                    }

                    // Notifications pour Comptable
                    if(auth()->user()->hasRole('Comptable')) {
                    // Factures impayées
                    $facturesImpayes = \App\Models\Facture::where('statut', 'impayee')->count();
                    if($facturesImpayes > 0) {
                    $notifications[] = [
                    'icon' => 'bi-receipt',
                    'color' => 'danger',
                    'title' => 'Factures impayées',
                    'message' => "$facturesImpayes facture(s) impayée(s)",
                    'time' => 'Urgent'
                    ];
                    }
                    }

                    // Notifications pour Coordonnateur
                    if(auth()->user()->hasRole('Coordonnateur')) {
                    // Validations en attente
                    $validationsPendantes = \App\Models\Validation::where('statut', 'en_attente')->count();
                    if($validationsPendantes > 0) {
                    $notifications[] = [
                    'icon' => 'bi-check-circle',
                    'color' => 'info',
                    'title' => 'Validations requises',
                    'message' => "$validationsPendantes validation(s) en attente",
                    'time' => '1 h'
                    ];
                    }
                    }

                    // Notifications pour tous : Observations récentes
                    $observationsRecentes = \App\Models\Observation::where('created_at', '>=',
                    now()->subDays(1))->count();
                    if($observationsRecentes > 0) {
                    $notifications[] = [
                    'icon' => 'bi-eye',
                    'color' => 'success',
                    'title' => 'Nouvelles observations',
                    'message' => "$observationsRecentes nouvelle(s) observation(s)",
                    'time' => '24h'
                    ];
                    }

                    $notificationCount = count($notifications);
                    @endphp

                    @if($notificationCount > 0)
                    <span class="badge bg-primary badge-number">{{ $notificationCount }}</span>
                    @endif
                </a><!-- End Notification Icon -->

                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
                    <li class="dropdown-header">
                        @if($notificationCount > 0)
                        Vous avez {{ $notificationCount }} nouvelle(s) notification(s)
                        <a href="#"><span class="badge rounded-pill bg-primary p-2 ms-2">Tout voir</span></a>
                        @else
                        Aucune nouvelle notification
                        @endif
                    </li>

                    @if($notificationCount > 0)
                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    @foreach($notifications as $notification)
                    <li class="notification-item">
                        <i class="bi {{ $notification['icon'] }} text-{{ $notification['color'] }}"></i>
                        <div>
                            <h4>{{ $notification['title'] }}</h4>
                            <p>{{ $notification['message'] }}</p>
                            <p>{{ $notification['time'] }}</p>
                        </div>
                    </li>

                    @if(!$loop->last)
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    @endif
                    @endforeach

                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li class="dropdown-footer">
                        <a href="#">Voir toutes les notifications</a>
                    </li>
                    @endif
                </ul><!-- End Notification Dropdown Items -->
            </li><!-- End Notification Nav -->

            <!-- Messages (pour coordination entre équipes) -->
            @canany(['users.view', 'collectes.validate_final'])
            <li class="nav-item dropdown">
                <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
                    <i class="bi bi-chat-left-text"></i>
                    <span class="badge bg-success badge-number">0</span>
                </a><!-- End Messages Icon -->

                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow messages">
                    <li class="dropdown-header">
                        Aucun nouveau message
                        <a href="#"><span class="badge rounded-pill bg-primary p-2 ms-2">Tout voir</span></a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li class="dropdown-footer">
                        <a href="#">Voir tous les messages</a>
                    </li>
                </ul><!-- End Messages Dropdown Items -->
            </li><!-- End Messages Nav -->
            @endcanany

            <!-- Profil utilisateur -->
            <li class="nav-item dropdown pe-3">
                <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                    @if(auth()->user()->profile_image)
                    <img src="{{ asset('storage/'.auth()->user()->profile_image) }}" alt="Profil" class="rounded-circle">
                    @else
                    <img src="https://st2.depositphotos.com/1104517/11967/v/950/depositphotos_119675554-stock-illustration-male-avatar-profile-picture-vector.jpg"
                        alt="Profil" class="rounded-circle">
                    @endif
                    <div class="d-none d-md-block ps-2">
                        <div class="fw-bold">{{ auth()->user()->firstname }} {{ auth()->user()->lastname }}</div>
                        <div class="small text-muted">{{ auth()->user()->getRoleNames()->first() ?? 'Visiteur' }}</div>
                    </div>
                </a><!-- End Profile Image Icon -->

                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                    <li class="dropdown-header">
                        <h6>{{ auth()->user()->firstname }} {{ auth()->user()->lastname }}</h6>
                        <span class="badge" style="background-color: {{ 
                            match(auth()->user()->getRoleNames()->first()) {
                                'Super Admin' => '#dc3545',
                                'Administrateur' => '#fd7e14', 
                                'Coordonnateur' => '#6f42c1',
                                'Comptable' => '#20c997',
                                'Agent marketing' => '#0dcaf0',
                                'Agent collecte' => '#198754',
                                'Responsable site' => '#ffc107',
                                'Agent santé' => '#6610f2',
                                default => '#6c757d'
                            }
                        }}; color: white; font-size: 0.75rem;">
                            {{ auth()->user()->getRoleNames()->first() ?? 'Aucun rôle' }}
                        </span>
                        @if(auth()->user()->site)
                        <small class="text-muted d-block mt-1">
                            <i class="bi bi-geo-alt"></i> {{ auth()->user()->site->nom }}
                        </small>
                        @endif
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="{{ route('profile.show') }}">
                            <i class="bi bi-person"></i>
                            <span>Mon Profil</span>
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <!-- Accès rapide selon le rôle -->
                    @hasrole('Agent collecte')
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="{{ route('collectes.create') }}">
                            <i class="bi bi-truck"></i>
                            <span>Nouvelle Collecte</span>
                        </a>
                    </li>
                    @endhasrole

                    @hasrole('Comptable')
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="{{ route('factures.index') }}">
                            <i class="bi bi-receipt"></i>
                            <span>Mes Factures</span>
                        </a>
                    </li>
                    @endhasrole

                    @hasrole('Coordonnateur')
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="{{ route('validations.index') }}">
                            <i class="bi bi-check-circle"></i>
                            <span>Validations</span>
                        </a>
                    </li>
                    @endhasrole

                    @can('configurations.view')
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="{{ route('configuration') }}">
                            <i class="bi bi-gear"></i>
                            <span>Configuration</span>
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    @endcan

                    <!-- Informations système pour Super Admin -->
                    @hasrole('Super Admin')
                    <li>
                        <div class="dropdown-item-text">
                            <small class="text-muted">
                                <i class="bi bi-clock"></i> Dernière connexion:
                                {{ auth()->user()->last_login_at?->diffForHumans() ?? 'Première fois' }}
                            </small>
                        </div>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    @endhasrole

                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="#"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Se déconnecter</span>
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </li>
                </ul><!-- End Profile Dropdown Items -->
            </li><!-- End Profile Nav -->

        </ul>
    </nav><!-- End Icons Navigation -->

</header><!-- End Header -->

<!-- CSS personnalisé pour les améliorations visuelles -->
<style>
    /* Badge de rôle dans le profil */
    .profile .dropdown-header .badge {
        font-size: 0.75rem !important;
        padding: 0.25rem 0.5rem;
    }

    /* Icônes de notification colorées */
    .notification-item i {
        font-size: 1.2rem;
        margin-right: 0.5rem;
    }

    /* Style pour les informations du site */
    .dropdown-header small {
        font-size: 0.8rem;
        color: #6c757d !important;
    }

    /* Amélioration de la recherche */
    .search-form input {
        border-radius: 20px;
        padding-left: 1rem;
    }

    .search-form input::placeholder {
        font-style: italic;
        color: #adb5bd;
    }

    .search-bar {
        position: relative;
    }

    .global-search-suggestions {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        right: 0;
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 10px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        z-index: 1050;
        max-height: 360px;
        overflow-y: auto;
    }

    .global-search-item {
        display: block;
        padding: 10px 12px;
        color: #212529;
        text-decoration: none;
        border-bottom: 1px solid #f1f3f5;
    }

    .global-search-item:last-child {
        border-bottom: 0;
    }

    .global-search-item:hover {
        background: #f8f9fa;
    }

    .global-search-item .meta {
        font-size: 12px;
        color: #6c757d;
    }

    .global-search-empty {
        padding: 10px 12px;
        color: #6c757d;
        font-size: 14px;
    }

    /* Responsive pour mobile */
    @media (max-width: 768px) {
        .nav-profile span {
            display: none !important;
        }

        .dropdown-header h6 {
            font-size: 0.9rem;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('globalSearchInput');
        const box = document.getElementById('globalSearchSuggestions');
        if (!input || !box) return;

        let timer = null;
        let lastQuery = '';

        const hideBox = () => {
            box.classList.add('d-none');
            box.innerHTML = '';
        };

        const renderItems = (items, query) => {
            if (!items.length) {
                box.innerHTML = '<div class="global-search-empty">Aucune suggestion.</div>';
                box.classList.remove('d-none');
                return;
            }

            const html = items.map(item => `
                <a href="${item.url}" class="global-search-item">
                    <div><strong>${item.title ?? ''}</strong></div>
                    <div class="meta">${item.section ?? ''} | ${item.subtitle ?? ''}</div>
                </a>
            `).join('') + `
                <a href="{{ route('search.global') }}?query=${encodeURIComponent(query)}" class="global-search-item">
                    <strong>Voir tous les resultats</strong>
                </a>
            `;

            box.innerHTML = html;
            box.classList.remove('d-none');
        };

        input.addEventListener('input', function() {
            const query = input.value.trim();
            if (query.length < 2) {
                hideBox();
                return;
            }

            if (query === lastQuery) return;
            lastQuery = query;

            if (timer) clearTimeout(timer);
            timer = setTimeout(async () => {
                try {
                    const url = `{{ route('search.suggest') }}?query=${encodeURIComponent(query)}`;
                    const response = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) {
                        hideBox();
                        return;
                    }

                    const data = await response.json();
                    renderItems(data.items || [], query);
                } catch (e) {
                    hideBox();
                }
            }, 250);
        });

        input.addEventListener('focus', function() {
            if (box.innerHTML.trim() !== '') {
                box.classList.remove('d-none');
            }
        });

        document.addEventListener('click', function(e) {
            if (!box.contains(e.target) && e.target !== input) {
                hideBox();
            }
        });

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideBox();
            }
        });
    });
</script>
