<?php

// Racine publique (utile pour les uploads)
define('PUBLIC_ROOT', APP_ROOT . '/public');

// Dossier d'upload des fichiers (images, etc.)
define('UPLOAD_DIR', PUBLIC_ROOT . '/uploads');

// URL de base (à adapter si tu es en local ou en ligne)
define('BASE_URL', '/');

if (!defined('PUBLIC_ROOT')) {
    define('PUBLIC_ROOT', APP_ROOT . '/public');
    define('UPLOAD_DIR', PUBLIC_ROOT . '/uploads');
    define('BASE_URL', '/');
}