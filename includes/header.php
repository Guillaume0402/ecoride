<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>EcoRide</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />

    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>" />
</head>

<body class="page-container d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow-sm fixed-height-80">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= url('/') ?>"><img class="logo ms-5" src="<?= url('assets/images/logo.svg') ?>" alt=""></a>
            <a class="navbar-brand logo-title" href="<?= url('/') ?>">Ecoride</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown"
                aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNavDropdown">
                <ul class="navbar-nav">
                    <li class="nav-item me-5">
                        <a class="nav-link " aria-current="page" href="<?= url('/') ?>">Accueil</a>
                    </li>
                    <li class="nav-item me-5">
                        <a class="nav-link " href="<?= url('covoiturages') ?>">Covoiturages</a>
                    </li>
                    <li class="nav-item me-5">
                        <a class="nav-link " href="?page=contact">Contact</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link " href="?page=login"" role="button">
                            Connexion
                        </a>

                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <main>