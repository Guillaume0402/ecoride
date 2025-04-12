<?php
// public/index.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Router;

$router = new Router();

// Définition des routes
$router->get('/', 'HomeController@index');
$router->get('/home', 'HomeController@index');

// Récupérer et nettoyer l'URI
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Dispatcher la requête
$router->dispatch($requestUri);
