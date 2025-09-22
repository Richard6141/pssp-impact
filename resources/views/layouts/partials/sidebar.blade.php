<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

        <!-- Dashboard - Accessible à tous les utilisateurs connectés -->
        <li class="nav-item">
            <a class="nav-link" href="{{ route('dashboard') }}">
                <i class="bi bi-grid"></i>
                <span>Dashboard</span>
            </a>
        </li><!-- End Dashboard Nav -->

        <!-- Section Gestion des Déchets -->
        @canany(['sites.view', 'type_dechets.view', 'collectes.view', 'observations.view', 'validations.view'])
        <li class="nav-heading">Gestion des Déchets</li>
        @endcanany

        <!-- Sites -->
        @can('sites.view')
        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-target="#sites-nav" data-bs-toggle="collapse" href="#">
                <i class="bi bi-geo-alt"></i><span>Sites</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="sites-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                <li>
                    <a href="{{ route('sites.index') }}">
                        <i class="bi bi-circle"></i><span>Liste des Sites</span>
                    </a>
                </li>
                @can('sites.create')
                <li>
                    <a href="{{ route('sites.create') }}">
                        <i class="bi bi-circle"></i><span>Nouveau Site</span>
                    </a>
                </li>
                @endcan
            </ul>
        </li><!-- End Sites Nav -->
        @endcan

        <!-- Types de Déchets -->
        @can('type_dechets.view')
        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-target="#types-nav" data-bs-toggle="collapse" href="#">
                <i class="bi bi-trash"></i><span>Types de Déchets</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="types-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                <li>
                    <a href="{{ route('type_dechets.index') }}">
                        <i class="bi bi-circle"></i><span>Liste des Types</span>
                    </a>
                </li>
                @can('type_dechets.create')
                <li>
                    <a href="{{ route('type_dechets.create') }}">
                        <i class="bi bi-circle"></i><span>Nouveau Type</span>
                    </a>
                </li>
                @endcan
            </ul>
        </li><!-- End Types de Déchets Nav -->
        @endcan

        <!-- Collectes -->
        @can('collectes.view')
        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-target="#collectes-nav" data-bs-toggle="collapse" href="#">
                <i class="bi bi-truck"></i><span>Collectes</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="collectes-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                <li>
                    <a href="{{ route('collectes.index') }}">
                        <i class="bi bi-circle"></i><span>Liste des Collectes</span>
                    </a>
                </li>
                @can('collectes.create')
                <li>
                    <a href="{{ route('collectes.create') }}">
                        <i class="bi bi-circle"></i><span>Nouvelle Collecte</span>
                    </a>
                </li>
                @endcan
            </ul>
        </li><!-- End Collectes Nav -->
        @endcan

        <!-- Observations -->
        @can('observations.view')
        <li class="nav-item">
            <a class="nav-link collapsed" href="{{ route('observations.index') }}">
                <i class="bi bi-eye"></i>
                <span>Observations</span>
            </a>
        </li><!-- End Observations Nav -->
        @endcan

        <!-- Validations -->
        @can('validations.view')
        <li class="nav-item">
            <a class="nav-link collapsed" href="{{ route('validations.index') }}">
                <i class="bi bi-check-circle"></i>
                <span>Validations</span>
            </a>
        </li><!-- End Validations Nav -->
        @endcan

        <!-- Section Gestion Financière -->
        @canany(['factures.view', 'paiements.view'])
        <li class="nav-heading">Gestion Financière</li>
        @endcanany

        <!-- Factures -->
        @can('factures.view')
        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-target="#factures-nav" data-bs-toggle="collapse" href="#">
                <i class="bi bi-receipt"></i><span>Factures</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="factures-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                <li>
                    <a href="{{ route('factures.index') }}">
                        <i class="bi bi-circle"></i><span>Liste des Factures</span>
                    </a>
                </li>
                @can('factures.create')
                <li>
                    <a href="{{ route('factures.create') }}">
                        <i class="bi bi-circle"></i><span>Nouvelle Facture</span>
                    </a>
                </li>
                @endcan
            </ul>
        </li><!-- End Factures Nav -->
        @endcan

        <!-- Paiements -->
        @can('paiements.view')
        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-target="#paiements-nav" data-bs-toggle="collapse" href="#">
                <i class="bi bi-credit-card"></i><span>Paiements</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="paiements-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                <li>
                    <a href="{{ route('paiements.index') }}">
                        <i class="bi bi-circle"></i><span>Liste des Paiements</span>
                    </a>
                </li>
                @can('paiements.record')
                <li>
                    <a href="{{ route('paiements.create') }}">
                        <i class="bi bi-circle"></i><span>Nouveau Paiement</span>
                    </a>
                </li>
                @endcan
            </ul>
        </li><!-- End Paiements Nav -->
        @endcan

        <!-- Section Rapports (pour les rôles ayant accès) -->
        @can('rapports.view')
        <li class="nav-heading">Rapports</li>

        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-target="#rapports-nav" data-bs-toggle="collapse" href="#">
                <i class="bi bi-bar-chart"></i><span>Rapports</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="rapports-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                <li>
                    <a href="{{ route('rapports.collectes') }}">
                        <!-- À créer : route('rapports.collectes') -->
                        <i class="bi bi-circle"></i><span>Collectes</span>
                    </a>
                </li>
                @can('rapports.generate')
                <li>
                    <a href="#">
                        <!-- À créer : route('rapports.financier') -->
                        <i class="bi bi-circle"></i><span>Financier</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <!-- À créer : route('rapports.sites') -->
                        <i class="bi bi-circle"></i><span>Par Site</span>
                    </a>
                </li>
                @endcan
            </ul>
        </li><!-- End Rapports Nav -->
        @endcan

        <!-- Section Administration -->
        @canany(['users.view', 'roles.view', 'system.settings', 'configurations.view'])
        <li class="nav-heading">Administration</li>
        @endcanany

        <!-- Gestion des utilisateurs -->
        @can('users.view')
        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-target="#users-nav" data-bs-toggle="collapse" href="#">
                <i class="bi bi-people"></i><span>Utilisateurs</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="users-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                <li>
                    <a href="#">
                        <!-- À créer : route('users.index') -->
                        <i class="bi bi-circle"></i><span>Liste des Utilisateurs</span>
                    </a>
                </li>
                @can('users.create')
                <li>
                    <a href="#">
                        <!-- À créer : route('users.create') -->
                        <i class="bi bi-circle"></i><span>Nouvel Utilisateur</span>
                    </a>
                </li>
                @endcan
            </ul>
        </li><!-- End Users Nav -->
        @endcan

        <!-- Configuration générale -->
        @canany(['configurations.view', 'system.settings'])
        <li class="nav-item">
            <a class="nav-link collapsed" href="{{ route('configuration') }}">
                <i class="bi bi-gear"></i>
                <span>Configuration</span>
            </a>
        </li><!-- End Configuration Nav -->
        @endcanany

        <!-- Section Super Admin uniquement -->
        @hasrole('Super Admin')
        <li class="nav-heading">Super Admin</li>

        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-target="#system-nav" data-bs-toggle="collapse" href="#">
                <i class="bi bi-shield-lock"></i><span>Système</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="system-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                @can('system.logs')
                <li>
                    <a href="#">
                        <!-- À créer : route('system.logs') -->
                        <i class="bi bi-circle"></i><span>Logs Système</span>
                    </a>
                </li>
                @endcan
                @can('system.backup')
                <li>
                    <a href="#">
                        <!-- À créer : route('system.backup') -->
                        <i class="bi bi-circle"></i><span>Sauvegardes</span>
                    </a>
                </li>
                @endcan
                @can('system.maintenance')
                <li>
                    <a href="#">
                        <!-- À créer : route('system.maintenance') -->
                        <i class="bi bi-circle"></i><span>Maintenance</span>
                    </a>
                </li>
                @endcan
            </ul>
        </li><!-- End System Nav -->
        @endhasrole

        <!-- Section Mon Compte - Accessible à tous -->
        <li class="nav-heading">Mon Compte</li>

        <li class="nav-item">
            <a class="nav-link collapsed" href="{{ route('profile.show') }}">
                <i class="bi bi-person"></i>
                <span>Mon Profil</span>
            </a>
        </li><!-- End Profile Page Nav -->

        <!-- Badge de rôle utilisateur -->
        <li class="nav-item">
            <div class="nav-link collapsed" style="padding: 0.5rem 1rem; color: #899bbd; font-size: 0.8rem;">
                <i class="bi bi-badge-tm"></i>
                <span>{{ auth()->user()->getRoleNames()->first() ?? 'Aucun rôle' }}</span>
            </div>
        </li>

        <li class="nav-item">
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <a class="nav-link collapsed" href="#" onclick="event.preventDefault(); this.closest('form').submit();">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Déconnexion</span>
                </a>
            </form>
        </li><!-- End Logout Nav -->

    </ul>

</aside><!-- End Sidebar-->

<!-- CSS personnalisé pour le badge de rôle -->
<style>
    .nav-link .bi-badge-tm {
        color: #28a745;
    }

    /* Style conditionnel selon le rôle */
    @php $userRole=auth()->user()->getRoleNames()->first();
    $roleColors=[ 'Super Admin'=>'#dc3545',
    'Administrateur'=>'#fd7e14',
    'Coordonnateur'=>'#6f42c1',
    'Comptable'=>'#20c997',
    'Agent marketing'=>'#0dcaf0',
    'Agent collecte'=>'#198754',
    'Responsable site'=>'#ffc107',
    'Agent santé'=>'#6610f2'
    ];

    @endphp @if(isset($roleColors[$userRole])) <style>.nav-link .bi-badge-tm {
        color: {
                {
                $roleColors[$userRole]
            }
        }

        !important;
    }
</style>
@endif
</style>