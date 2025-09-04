<?php

namespace App\Routing;

class Router
{
    // Table de routage chargée depuis config/routes.php
    private array $routes;

    public function __construct()
    {
        // Charge la configuration des routes (tableau associatif)
        $this->routes = require_once APP_ROOT . "/config/routes.php";
        // Log de debug: liste des chemins déclarés
        error_log("Routes chargées : " . print_r(array_keys($this->routes), true));
    }

    // Point d'entrée: fait correspondre l'URI à une route et invoque le contrôleur
    public function handleRequest(string $uri): void
    {
        // Normalise le chemin et détermine la méthode HTTP courante
        $path = $this->normalizePath($uri);
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        error_log("Recherche de la route : $path");

        $matchedRoute = null;
        $params = [];

        foreach ($this->routes as $routePath => $routeConfig) {
            // Convertit /admin/users/toggle/{id} → regex
            // NOTE: ici, les paramètres sont limités à des chiffres (\d+). Adapter si besoin (ex: [^/]+).
            $pattern = preg_replace('#\{[a-zA-Z_]+\}#', '(\d+)', $routePath);

            if (preg_match("#^$pattern$#", $path, $matches)) {
                // Sélectionne la config spécifique à la méthode si définie, sinon la config par défaut
                $matchedRoute = $routeConfig[$method] ?? $routeConfig;
                // Extrait les noms des paramètres et associe leurs valeurs capturées (ex: id => 8)
                preg_match_all('#\{([a-zA-Z_]+)\}#', $routePath, $paramNames);
                array_shift($matches); // Retire la correspondance complète
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

        // Appel du contrôleur avec les paramètres (transmet les valeurs dans l'ordre défini par la route)
        call_user_func_array([$controller, $action], $params);
    }


    // Normalise un URI: extrait le path et supprime le slash de fin (sauf pour la racine)
    public static function normalizePath(string $uri): string
    {
        $path = parse_url($uri, PHP_URL_PATH);
        return rtrim($path, '/') ?: '/';
    }

    // Compare une route au chemin courant (normalisé) pour marquage "actif"
    public static function isActiveRoute(string $path): bool
    {
        return self::normalizePath($_SERVER["REQUEST_URI"]) === $path;
    }
}
