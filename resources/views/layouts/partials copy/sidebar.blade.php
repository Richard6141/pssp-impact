<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

        <li class="nav-item">
            <a class="nav-link" href="{{ route('dashboard') }}">
                <i class="bi bi-grid"></i>
                <span>Dashboard</span>
            </a>
        </li><!-- End Dashboard Nav -->

        <li class="nav-heading">Gestion des Déchets</li>

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
                <li>
                    <a href="{{ route('sites.create') }}">
                        <i class="bi bi-circle"></i><span>Nouveau Site</span>
                    </a>
                </li>
            </ul>
        </li><!-- End Sites Nav -->

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
                <li>
                    <a href="{{ route('type_dechets.create') }}">
                        <i class="bi bi-circle"></i><span>Nouveau Type</span>
                    </a>
                </li>
            </ul>
        </li><!-- End Types de Déchets Nav -->

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
                <li>
                    <a href="{{ route('collectes.create') }}">
                        <i class="bi bi-circle"></i><span>Nouvelle Collecte</span>
                    </a>
                </li>
            </ul>
        </li><!-- End Collectes Nav -->

        <li class="nav-item">
            <a class="nav-link collapsed" href="{{ route('observations.index') }}">
                <i class="bi bi-eye"></i>
                <span>Observations</span>
            </a>
        </li><!-- End Observations Nav -->

        <li class="nav-item">
            <a class="nav-link collapsed" href="{{ route('validations.index') }}">
                <i class="bi bi-check-circle"></i>
                <span>Validations</span>
            </a>
        </li><!-- End Validations Nav -->

        <li class="nav-heading">Gestion Financière</li>

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
                <li>
                    <a href="{{ route('factures.create') }}">
                        <i class="bi bi-circle"></i><span>Nouvelle Facture</span>
                    </a>
                </li>
            </ul>
        </li><!-- End Factures Nav -->

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
                <li>
                    <a href="{{ route('paiements.create') }}">
                        <i class="bi bi-circle"></i><span>Nouveau Paiement</span>
                    </a>
                </li>
            </ul>
        </li><!-- End Paiements Nav -->

        <li class="nav-heading">Administration</li>

        <li class="nav-item">
            <a class="nav-link collapsed" href="{{ route('configuration') }}">
                <i class="bi bi-gear"></i>
                <span>Configuration</span>
            </a>
        </li><!-- End Configuration Nav -->

        <li class="nav-heading">Mon Compte</li>

        <li class="nav-item">
            <a class="nav-link collapsed" href="{{ route('profile.show') }}">
                <i class="bi bi-person"></i>
                <span>Mon Profil</span>
            </a>
        </li><!-- End Profile Page Nav -->

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