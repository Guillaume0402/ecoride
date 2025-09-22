<?php

// Déterminer l'environnement le plus tôt possible (Heroku fournit APP_ENV)
$__appEnv = getenv('APP_ENV');
if ($__appEnv === false && isset($_ENV['APP_ENV'])) {
    $__appEnv = (string) $_ENV['APP_ENV'];
}
if (!is_string($__appEnv) || $__appEnv === '') {
    $__appEnv = 'prod';
}

// Affichage des erreurs: activé seulement en dev
if ($__appEnv === 'dev') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    ini_set('html_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('html_errors', '0');
    // E_STRICT n'existe plus en 8.x, supprimer du masque
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
}

// --- Sessions fiables derrière proxy Heroku ---
// Si la requête est passée en HTTPS via le routeur Heroku, on l'indique à PHP
if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

// Paramètres de cookie de session (secure en HTTPS, HttpOnly, SameSite=Lax)
if (session_status() !== PHP_SESSION_ACTIVE) {
    // Laisse le domaine par défaut (host courant). Path=/ pour toute l'app.
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    @session_start();
}

// Charger les constantes globales
require_once __DIR__ . '/../config/constants.php';

// Autoload Composer
require_once APP_ROOT . '/vendor/autoload.php';

// Charger les variables d'environnement (.env + .env.local)
use Dotenv\Dotenv;

// Charger les variables d'environnement depuis des fichiers si présents (sans exception si absents)
$dotenv = Dotenv::createMutable(dirname(__DIR__), ['.env', '.env.local']);
$dotenv->safeLoad();

// Définir le fuseau horaire par défaut (impacte toutes les DateTime)
// Utilise APP_TZ si défini dans l'environnement, sinon Europe/Paris
try {
    $tz = $_ENV['APP_TZ'] ?? 'Europe/Paris';
    if (is_string($tz) && $tz !== '') {
        date_default_timezone_set($tz);
    }
} catch (\Throwable $e) {
    // défaut silencieux si le TZ est invalide
    @date_default_timezone_set('Europe/Paris');
}

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
    if (($_ENV['APP_ENV'] ?? $__appEnv) === 'dev') {
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
