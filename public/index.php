<?php



// Forcer l'affichage des erreurs sur Linux en développement
ini_set('display_errors', 1);           // Affiche les erreurs à l'écran
ini_set('display_startup_errors', 1);   // Affiche les erreurs au démarrage de PHP
ini_set('html_errors', 1);              // Formate les erreurs en HTML (avec couleurs)
error_reporting(E_ALL);                 // Affiche tous les types d'erreurs

// Debug : Afficher l'URL demandée
// echo "URL demandée : " . $_SERVER["REQUEST_URI"] . "<br>";

define('APP_ROOT', dirname(__DIR__));
define('APP_ENV', '.env.local');

// Charger les constantes globales
require_once APP_ROOT . '/config/constants.php';


// Inclure la configuration
require_once __DIR__ . '/../config/app.php';

// Inclusion du helper global ✅
require_once APP_ROOT . '/src/helpers.php';


// Inclusion de l'autoloader de Composer pour charger automatiquement les classes
require_once __DIR__ . '/../vendor/autoload.php';




// Import de la classe Router
use App\Routing\Router;



// Création d'une instance du routeur
$router = new Router();

// Traitement de la requête HTTP entrante
// $_SERVER["REQUEST_URI"] contient l'URL demandée par l'utilisateur

$router->handleRequest($_SERVER["REQUEST_URI"]);


if (php_sapi_name() === 'cli-server') {
    $path = __DIR__ . parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
    if (is_file($path)) {
        return false;
    }
}



