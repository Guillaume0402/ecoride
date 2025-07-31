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

    // Ajoute une route à la configuration
    public function handleRequest(string $uri): void
    {
        $path = $this->normalizePath($uri);
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        error_log("Recherche de la route : $path");

        $matchedRoute = null;
        $params = [];

        foreach ($this->routes as $routePath => $routeConfig) {
            // Convertir /admin/users/toggle/{id} → regex
            $pattern = preg_replace('#\{[a-zA-Z_]+\}#', '(\d+)', $routePath);

            if (preg_match("#^$pattern$#", $path, $matches)) {
                $matchedRoute = $routeConfig[$method] ?? $routeConfig;
                // Extraire les paramètres (ex: id = 8)
                preg_match_all('#\{([a-zA-Z_]+)\}#', $routePath, $paramNames);
                array_shift($matches); // Retirer la correspondance complète
                $params = array_combine($paramNames[1], $matches);
                break;
            }
        }

        if (!$matchedRoute) {
            abort(404, "Route non trouvée : $path");
        }

        $controllerPath = $matchedRoute['controller'];
        $action = $matchedRoute['action'];

        if (!class_exists($controllerPath)) {
            abort(500, "Controller introuvable : $controllerPath");
        }

        $controller = new $controllerPath();

        if (!method_exists($controller, $action)) {
            abort(500, "Méthode $action introuvable dans $controllerPath");
        }

        // Appel du contrôleur avec les paramètres
        call_user_func_array([$controller, $action], $params);
    }


    // Normalise le chemin de l'URI pour enlever les slash finaux
    public static function normalizePath(string $uri): string
    {
        $path = parse_url($uri, PHP_URL_PATH);
        return rtrim($path, '/') ?: '/';
    }

    // Vérifie si le chemin correspond à la route active
    public static function isActiveRoute(string $path): bool
    {
        return self::normalizePath($_SERVER["REQUEST_URI"]) === $path;
    }
}
