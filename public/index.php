<?php

/**
 * Front Controller (point d’entrée unique)
 * - Initialise l'environnement (dev/prod)
 * - Configure l’affichage d’erreurs
 * - Sécurise et démarre la session (cookies)
 * - Charge config + dépendances + .env
 * - Délègue au Router
 */

// --- (1) Serveur PHP intégré : laisser servir les fichiers statiques ---
// Avec `php -S`, si on retourne false, PHP renvoie le fichier tel quel (css, img, js...)
// IMPORTANT : doit être exécuté avant le routeur.
if (php_sapi_name() === 'cli-server') {
    $path = __DIR__ . parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
    if (is_file($path)) {
        return false;
    }
}

// --- (2) Déterminer l'environnement le plus tôt possible ---
$__appEnv = getenv('APP_ENV');
if ($__appEnv === false && isset($_ENV['APP_ENV'])) {
    $__appEnv = (string) $_ENV['APP_ENV'];
}
if (!is_string($__appEnv) || $__appEnv === '') {
    $__appEnv = 'prod';
}

// --- (3) Affichage des erreurs : activé seulement en dev ---
if ($__appEnv === 'dev') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    ini_set('html_errors', '1');
    error_reporting(E_ALL);
} else {
    // En prod : ne pas afficher les erreurs à l'écran (sécurité + UX)
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('html_errors', '0');
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
}

// --- (4) Sessions fiables derrière proxy Heroku ---
// Heroku termine TLS au niveau du routeur : PHP peut “voir” HTTP alors que l’utilisateur est en HTTPS.
// On s’appuie sur X-Forwarded-Proto (fourni par le routeur) pour que PHP sache qu’on est en HTTPS.
if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

// --- (5) Paramètres du cookie de session (Secure/Httponly/SameSite) ---
// - Secure : cookie envoyé seulement en HTTPS
// - HttpOnly : inaccessible en JavaScript (réduit l'impact d’un XSS)
// - SameSite=Lax : limite une partie des risques CSRF sans casser la navigation normale
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// --- (6) Charger les constantes globales ---
require_once __DIR__ . '/../config/constants.php';

// --- (7) Autoload Composer (classes + dépendances) ---
require_once APP_ROOT . '/vendor/autoload.php';

// --- (8) Charger les variables d'environnement (.env + .env.local) ---
// Permet de sortir les secrets/config du code (DB, SMTP, clés, etc.)
use Dotenv\Dotenv;
$dotenv = Dotenv::createMutable(dirname(__DIR__), ['.env', '.env.local']);
$dotenv->safeLoad(); // charge si présent, sans planter si absent

// --- (9) Définir le fuseau horaire par défaut ---
// Impacte toutes les DateTime : évite les incohérences serveur / dev / prod
try {
    $tz = $_ENV['APP_TZ'] ?? 'Europe/Paris';
    if (is_string($tz) && $tz !== '') {
        date_default_timezone_set($tz);
    }
} catch (\Throwable $e) {
    date_default_timezone_set('Europe/Paris');
}

// --- (10) Helpers globaux ---
require_once APP_ROOT . '/src/helpers.php';

// --- (11) Routage : déléguer la requête au Router ---
use App\Routing\Router;

$router = new Router();

try {
    $router->handleRequest($_SERVER["REQUEST_URI"]);
} catch (\Throwable $e) {
    // En dev : laisser remonter l’erreur (debug)
    if (($_ENV['APP_ENV'] ?? $__appEnv) === 'dev') {
        throw $e;
    }
    // En prod : afficher une page 500 propre
    (new \App\Controller\ErrorController())->show500();
}
