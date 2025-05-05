<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>EcoRide</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />

    <link rel="stylesheet" href="<?= asset('css/style.css') ?>" />
</head>

<body class="page-container d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow-sm fixed-height-80">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center mx-auto" href="<?= url('/') ?>">
                <img class="logo" src="<?= asset('images/logo.svg') ?>" alt="Logo EcoRide">
                <span class="logo-title ms-2">Ecoride</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown"
                aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end mobile-menu me-5" id="navbarNavDropdown">
                <ul class="navbar-nav gap-3">
                    <li class="nav-item ">
                        <a class="navlink nav-link " aria-current="page" href="<?= url('/') ?>">Accueil</a>
                    </li>
                    <li class="nav-item ">
                        <a class="navlink nav-link " href="<?= url('covoiturages') ?>">Covoiturages</a>
                    </li>
                    <li class="nav-item ">
                        <a class="navlink nav-link " href="<?= url('contact') ?>">Contact</a>
                    </li>
                    <li class="nav-item dropdown">
                        <button class="btn btn-inscription" data-bs-toggle="modal" data-bs-target="#authModal" data-start="login">
                            Connexion
                        </button>


                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <main class="flex-fill">