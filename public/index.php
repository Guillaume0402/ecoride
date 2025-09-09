<?php

// Forcer l'affichage des erreurs (en dev)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('html_errors', 1);
error_reporting(E_ALL);

// Charger les constantes globales
require_once __DIR__ . '/../config/constants.php';

// Autoload Composer
require_once APP_ROOT . '/vendor/autoload.php';

// Charger les variables d'environnement (.env + .env.local)
use Dotenv\Dotenv;
$dotenv = Dotenv::createMutable(dirname(__DIR__), ['.env', '.env.local']);
$dotenv->load();

// Inclusion du helper global
require_once APP_ROOT . '/src/helpers.php';

// Import du Router
use App\Routing\Router;

// Initialisation et traitement de la requête+
$router = new Router();

try {
    $router->handleRequest($_SERVER["REQUEST_URI"]);
} catch (\Throwable $e) {
    // En dev : laisser l'erreur remonter pour debugger
    if (($_ENV['APP_ENV'] ?? 'prod') === 'dev') {
        throw $e;
    }
    // En prod : page 500 propre
    (new \App\Controller\ErrorController())->show500();
}


// Gestion des fichiers statiques (serveur PHP intégré)
if (php_sapi_name() === 'cli-server') {
    $path = PUBLIC_ROOT . parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
    if (is_file($path)) {
        return false;
    }
}
