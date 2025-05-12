<?php
session_start();
// Simulation : si tu veux "simuler une connexion", mets ceci :
$_SESSION['user'] = [
    'name' => 'John Doe',
    'avatar' => 'assets/images/télé1.jpeg',
];
?>






<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>EcoRide</title>

    <!-- Ajoute le CSS de Bootstrap AVANT ton CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <link rel="stylesheet" href="<?= asset('css/style.css') ?>" />
</head>

<body class="page-container d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow-sm fixed-height-80">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center mx-auto" href="<?= url('/') ?>">
                <img class="logo" src="<?= asset('images/logo.svg') ?>" alt="Logo EcoRide">
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
                        <a class="navlink nav-link " aria-current="page" href="<?= url('/') ?>">Accueil</a>
                    </li>
                    <li class="nav-item ">
                        <a class="navlink nav-link " href="<?= url('liste-covoiturages') ?>">Covoiturages</a>
                    </li>
                    <li class="nav-item ">
                        <a class="navlink nav-link " href="<?= url('contact') ?>">Contact</a>
                    </li>
                    <li class="nav-item dropdown d-flex flex-column align-items-center">
                        <?php if (isset($_SESSION['user'])): ?>
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
                                <img src="<?= $_SESSION['user']['avatar'] ?>" alt="Avatar" class="avatar-img" />
                                <span class="d-none d-lg-inline text-white "><?= $_SESSION['user']['name'] ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end animate__animated animate__fadeIn">
                                <li><a class="dropdown-item" href="<?= url('my-profil') ?>"><i class="bi bi-person me-2"></i> Mon profil</a></li>
                                <li><a class="dropdown-item" href="<?= url('creation-covoiturage') ?>"><i class="bi bi-plus-circle me-2"></i> Créer un trajet</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="#"><i class="bi bi-box-arrow-right me-2"></i> Déconnexion</a></li>
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