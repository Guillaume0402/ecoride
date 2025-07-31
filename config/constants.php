<?php
// config/constants.php

// Racine du projet
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

// Racine publique (utile pour les uploads)
if (!defined('PUBLIC_ROOT')) {
    define('PUBLIC_ROOT', APP_ROOT . '/public');
}

// Dossier d'upload des fichiers
if (!defined('UPLOAD_DIR')) {
    define('UPLOAD_DIR', PUBLIC_ROOT . '/uploads');
}

// URL de base
if (!defined('BASE_URL')) {
    define('BASE_URL', '/');
}

define('SITE_URL', 'http://localhost:8080/');