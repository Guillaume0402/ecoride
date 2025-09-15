<header class="page-container d-flex flex-column sticky-top">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm fixed-height-80">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center mx-auto" href="/">
                <img class="logo" src="/assets/images/logo.svg" alt="Logo EcoRide">
                <span class="logo-title ms-2">Ecoride</span>
            </a>
            <button class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarNavDropdown"
                aria-controls="navbarNavDropdown"
                aria-expanded="false"
                aria-label="Menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end mobile-menu" id="navbarNavDropdown">
                <ul class="navbar-nav gap-3">
                    <li class="nav-item ">
                        <a class="navlink nav-link " href="/contact">Qui sommes-nous ?</a>
                    </li>
                    <li class="nav-item align-items-center">
                        <a class="navlink nav-link " aria-current="page" href="/">Accueil</a>
                    </li>
                    <li class="nav-item ">
                        <a class="navlink nav-link " href="/liste-covoiturages">Covoiturages</a>
                    </li>
                    <li class="nav-item ">
                        <a class="navlink nav-link " href="/contact">Contact</a>
                    </li>

                    <li class="nav-item dropdown d-flex flex-column align-items-center">
                        <?php if (isset($_SESSION['user'])): ?>
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
                                <img src="<?= htmlspecialchars($_SESSION['user']['photo']) ?>"
                                    onerror="this.src='/assets/images/logo.svg';"
                                    alt="Avatar"
                                    class="rounded-circle"
                                    style="width: 40px; height: 40px; object-fit: cover;">
                                <span class="d-none d-lg-inline"><?= $_SESSION['user']['pseudo'] ?? '' ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end animate__animated animate__fadeIn">
                                <?php $__credits = isset($_SESSION['user']['credits']) ? (int)$_SESSION['user']['credits'] : 0; ?>
                                <li>
                                    <span class="dropdown-item-text d-flex align-items-center justify-content-between">
                                        <span><i class="bi bi-coin me-2"></i> Crédits</span>
                                        <span class="badge bg-secondary rounded-pill text-black ms-2"><?= $__credits ?></span>
                                    </span>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <?php if ((int) $_SESSION['user']['role_id'] === 3): ?>
                                    <li><a class="dropdown-item" href="/admin/dashboard"><i class="bi bi-speedometer2 me-2"></i> Dashboard admin</a></li>
                                    <li><a class="dropdown-item" href="/admin/users"><i class="bi bi-people me-2"></i> Gérer les utilisateurs</a></li>
                                    <li><a class="dropdown-item" href="/admin/stats"><i class="bi bi-bar-chart-line me-2"></i> Statistiques</a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                <?php else: ?>
                                    <li><a class="dropdown-item" href="/my-profil"><i class="bi bi-person me-2"></i> Mon profil</a></li>
                                    <?php
                                    // Petits compteurs (optionnels) injectés via variables de vue si disponibles
                                    $pendingCount = isset($pendingCount) ? (int)$pendingCount : null;
                                    $myTripsCount = isset($myTripsCount) ? (int)$myTripsCount : null;
                                    ?>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center justify-content-between" href="/mes-demandes">
                                            <span><i class="bi bi-inbox me-2"></i> Mes demandes</span>
                                            <?php if ($pendingCount !== null): ?>
                                                <span class="badge bg-danger rounded-pill ms-3 text-black"><?= $pendingCount ?></span>
                                            <?php endif; ?>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center justify-content-between" href="/mes-covoiturages">
                                            <span><i class="bi bi-list-check me-2"></i> Mes trajets</span>
                                            <?php if ($myTripsCount !== null): ?>
                                                <span class="badge bg-secondary rounded-pill ms-3 text-black"><?= $myTripsCount ?></span>
                                            <?php endif; ?>
                                        </a>
                                    </li>
                                    <?php if (!empty($hasVehicle)): ?>
                                        <li>
                                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#createCovoitModal">
                                                <i class="bi bi-plus-circle me-2"></i> Créer un trajet
                                            </a>
                                        </li>
                                    <?php else: ?>
                                        <li>
                                            <a class="dropdown-item" href="/vehicle/create" title="Ajoutez un véhicule pour pouvoir créer un covoiturage">
                                                <i class="bi bi-car-front me-2"></i> Ajouter un véhicule
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" id="logoutBtn" href="/logout"><i class="bi bi-box-arrow-right me-2"></i> Déconnexion</a></li>
                            </ul>
                        <?php else: ?>
                            <button class="btn btn-inscription mt-3 mt-lg-1" data-bs-toggle="modal" data-bs-target="#authModal" data-start="login">
                                Connexion
                            </button>
                        <?php endif; ?>
                    </li>
                    <li>
                        <!-- Dans ta navbar Bootstrap -->
                        <button id="themeToggleBtn" class="btn btn-outline ms-2" title="Changer de thème">
                            <i class="bi bi-moon-stars"></i>
                        </button>

                    </li>

                </ul>
            </div>
        </div>
    </nav>
</header>