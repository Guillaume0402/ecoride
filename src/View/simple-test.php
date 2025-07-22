<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('APP_ROOT', dirname(__DIR__));
define('APP_ENV', ".env.local");

require_once __DIR__ . '/../vendor/autoload.php';

use App\Routing\Router;

$router = new Router();
$router->handleRequest($_SERVER["REQUEST_URI"]);