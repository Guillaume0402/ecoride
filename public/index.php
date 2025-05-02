<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/helpers.php';

use App\Router;

$router = new Router();

$router->get('/', 'HomeController@index');
$router->get('/covoiturages', 'CovoituragesController@index');
$router->get('/login', 'LoginController@index');
$router->get('/contact', 'ContactController@index');
$router->get('/signin', 'SigninController@index');

// Dispatcher la requête
$router->dispatch($_SERVER['REQUEST_URI']);
