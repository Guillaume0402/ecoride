<?php


// Forcer l'affichage des erreurs sur Linux en développement
ini_set('display_errors', 1);           // Affiche les erreurs à l'écran
ini_set('display_startup_errors', 1);   // Affiche les erreurs au démarrage de PHP
ini_set('html_errors', 1);              // Formate les erreurs en HTML (avec couleurs)
error_reporting(E_ALL);                 // Affiche tous les types d'erreurs

// Debug : Afficher l'URL demandée
// echo "URL demandée : " . $_SERVER["REQUEST_URI"] . "<br>";

// Chemin racine de l'application (dossier parent de /public)
define('APP_ROOT', dirname(__DIR__));


// Nom du fichier de configuration d'environnement
define('APP_ENV', ".env.local");

// Inclusion de l'autoloader de Composer pour charger automatiquement les classes
require_once __DIR__ . '/../vendor/autoload.php';


// Import de la classe Router
use App\Routing\Router;

// Création d'une instance du routeur
// echo "Création du router...<br>";
$router = new Router();

// Traitement de la requête HTTP entrante
// $_SERVER["REQUEST_URI"] contient l'URL demandée par l'utilisateur
// echo "Traitement de la requête...<br>";
$router->handleRequest($_SERVER["REQUEST_URI"]);
// echo "Fin du traitement.<br>";





