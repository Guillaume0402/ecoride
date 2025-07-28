<?php

namespace App\Routing;

class Router
{
    private array $routes;

    public function __construct()
    {
        $this->routes = require_once APP_ROOT . "/config/routes.php";
        error_log("Routes chargées : " . print_r(array_keys($this->routes), true));
    }

    public function handleRequest(string $uri): void
    {
        $path = $this->normalizePath($uri);
        error_log("Recherche de la route : $path");

        if (!isset($this->routes[$path])) {
            abort(404, "Route non trouvée : $path");
        }

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        error_log("➡️ URI appelée : $path, méthode : $method");

        $routeConfig = $this->routes[$path];
        error_log("Config de la route $path : " . print_r($routeConfig, true));
        
        if (isset($routeConfig[$method])) {
            $route = $routeConfig[$method];
        } elseif (isset($routeConfig['controller'])) {
            $route = $routeConfig;
        } else {
            abort(405, "{$method} non autorisée pour $path");
        }

        $controllerPath = $route['controller'];
        $action = $route['action'];

        error_log("🔍 Vérification classe : $controllerPath");
        if (!class_exists($controllerPath)) {
            abort(500, "Controller introuvable : $controllerPath");
        }

        $controller = new $controllerPath();
        error_log("Classe trouvée, instance créée");

        error_log("🔍 Vérification méthode : $action");
        if (!method_exists($controller, $action)) {
            abort(500, "Méthode $action introuvable dans $controllerPath");
        }

        error_log("Méthode trouvée, exécution de $controllerPath::$action");
        $controller->$action();
    }

    public static function normalizePath(string $uri): string
    {
        $path = parse_url($uri, PHP_URL_PATH);
        return rtrim($path, '/') ?: '/';
    }

    public static function isActiveRoute(string $path): bool
    {
        return self::normalizePath($_SERVER["REQUEST_URI"]) === $path;
    }
}
