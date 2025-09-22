<?php
// config/constants.php

// Racine du projet (chemin absolu) – pivot pour les chemins internes
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

// Dossier public (documents servis par le webserver)
if (!defined('PUBLIC_ROOT')) {
    define('PUBLIC_ROOT', APP_ROOT . '/public');
}

// Répertoire de stockage des fichiers uploadés
if (!defined('UPLOAD_DIR')) {
    define('UPLOAD_DIR', PUBLIC_ROOT . '/uploads');
}

// URL de base (préfixe utilisé pour générer des liens relatifs)
if (!defined('BASE_URL')) {
    define('BASE_URL', '/');
}

// URL complète du site (utile pour générer des URLs absolues)
define('SITE_URL', 'http://localhost:8080/');

// Délai avant auto-annulation d'un trajet non démarré (en minutes)
// Sert aussi de période de grâce côté Conducteur pour garder le trajet dans l'onglet
// et permettre un démarrage tardif avant bascule en historique
if (!defined('AUTO_CANCEL_MINUTES')) {
    define('AUTO_CANCEL_MINUTES', (int) ($_ENV['AUTO_CANCEL_MINUTES'] ?? 60));
}

// Délai pour considérer un trajet démarré comme expiré s'il n'a pas été terminé (en minutes)
if (!defined('AUTO_EXPIRE_STARTED_MINUTES')) {
    define('AUTO_EXPIRE_STARTED_MINUTES', (int) ($_ENV['AUTO_EXPIRE_STARTED_MINUTES'] ?? 120));
}

// Avatar par défaut (utilisé si l'utilisateur n'a pas de photo)
if (!defined('DEFAULT_AVATAR_URL')) {
    define('DEFAULT_AVATAR_URL', '/assets/images/logo.svg');
}
