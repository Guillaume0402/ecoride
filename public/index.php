<?php

// Forcer l'affichage des erreurs (en dev)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('html_errors', 1);
error_reporting(E_ALL);

// Charger les constantes globales
require_once __DIR__ . '/../config/constants.php';

// Définir l'environnement si nécessaire
if (!defined('APP_ENV')) {
    define('APP_ENV', '.env.local');
}

// Inclure la configuration
require_once APP_ROOT . '/config/app.php';

// Inclusion du helper global
require_once APP_ROOT . '/src/helpers.php';

// Autoload Composer
require_once APP_ROOT . '/vendor/autoload.php';

// Import du Router
use App\Routing\Router;

// Initialisation et traitement de la requête
$router = new Router();
$router->handleRequest($_SERVER["REQUEST_URI"]);

// Gestion des fichiers statiques (serveur PHP intégré)
if (php_sapi_name() === 'cli-server') {
    $path = PUBLIC_ROOT . parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
    if (is_file($path)) {
        return false;
    }
}
