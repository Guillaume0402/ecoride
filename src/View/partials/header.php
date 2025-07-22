<header class="page-container d-flex flex-column">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow-sm fixed-height-80">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center mx-auto" href="/">
                <img class="logo" src="/assets/images/logo.svg"  alt="Logo EcoRide">
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
                                <img src="<?= $_SESSION['user']['avatar'] ?>" alt="Avatar" class="avatar-img" />
                                <span class="d-none d-lg-inline text-white "><?= $_SESSION['user']['name'] ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end animate__animated animate__fadeIn">
                                <li><a class="dropdown-item" href="/my-profil"><i class="bi bi-person me-2"></i> Mon profil</a></li>
                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#covoitModal"><i class="bi bi-plus-circle me-2"></i> Créer un trajet</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="/logout"><i class="bi bi-box-arrow-right me-2"></i> Déconnexion</a></li>
                            </ul>
                        <?php else: ?>
                            <button class="btn btn-inscription mt-3 mt-lg-1" data-bs-toggle="modal" data-bs-target="#authModal" data-start="login">
                                Connexion
                            </button>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>