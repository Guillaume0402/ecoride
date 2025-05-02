<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/Router.php';

use App\Router;

// Définir le chemin de base pour construire les URLs
// ⚠️ Change '/EcoRide/public' si ton projet est ailleurs
Router::$basePath = '';

$router = new Router();

$router->get('/', 'HomeController@index');
$router->get('/covoiturages', 'CovoituragesController@index');
$router->get('/login', 'LoginController@index');
$router->get('/contact', 'ContactController@index');
$router->get('/signin', 'SigninController@index');
$router->get('/test500', 'FakeController@boom');


$router->dispatch($_SERVER['REQUEST_URI']);