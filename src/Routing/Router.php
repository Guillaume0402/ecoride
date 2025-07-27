<?php

namespace App\Routing;

use App\Controller\ErrorController;

class Router
{
    private array $routes;

    public function __construct()
    {
        // Chargement de toutes les routes définies dans le fichier config/routes.php
        $this->routes = require_once APP_ROOT . "/config/routes.php";
    }

    /**
     * Gère une requête entrante : résout le contrôleur et l'action correspondants à l'URI.
     */
    public function handleRequest(string $uri): void
    {
        try {
            $path = $this->normalizePath($uri);
            error_log("➡️ URI appelée : $uri");
            error_log("➡️ URI normalisée : $path");
            error_log("➡️ Méthode HTTP : " . $_SERVER['REQUEST_METHOD']);
            error_log("➡️ Routes disponibles : " . implode(', ', array_keys($this->routes)));            
            if (!isset($this->routes[$path])) {               
                throw new \Exception("Route not found for path: " . $path);
            }

            $route = $this->routes[$path];
            error_log("📦 Contenu de la route trouvée : " . print_r($route, true));

            // ✅ Cas A : Route définie selon la méthode HTTP (GET, POST, etc.)
            if (is_array($route) && isset($route[$_SERVER['REQUEST_METHOD']])) {
                $controllerPath = $route[$_SERVER['REQUEST_METHOD']]['controller'] ?? null;
                $action = $route[$_SERVER['REQUEST_METHOD']]['action'] ?? null;
            }
            // ✅ Cas B : Route classique (sans distinction de méthode)
            elseif (isset($route['controller']) && isset($route['action'])) {
                $controllerPath = $route['controller'];
                $action = $route['action'];
            } else {
                throw new \Exception("Route mal configurée pour : " . $path);
            }

            // Vérifie que le contrôleur existe
            if (!class_exists($controllerPath)) {
                error_log("❌ Contrôleur introuvable : $controllerPath");
                throw new \Exception("Controller class not found: $controllerPath");
            }

            $controller = new $controllerPath();

            // Vérifie que l'action existe dans le contrôleur
            if (!method_exists($controller, $action)) {
                error_log("❌ Action introuvable : $action dans $controllerPath");
                throw new \Exception("Action not found: $action in controller $controllerPath");
            }

            // ✅ Appel réel de la méthode
            $controller->$action();
        } catch (\Exception $e) {
            error_log("🔴 ROUTER ERROR → " . $e->getMessage());
            $errorController = new ErrorController();
            $errorController->show404("Route not found: " . $uri);
        }
    }

    /**
     * Normalise une URI pour enlever les slashs en trop ou les paramètres GET.
     */
    public static function normalizePath(string $uri): string
    {
        $path = parse_url($uri, PHP_URL_PATH);
        return rtrim($path, '/') ?: '/';
    }

    /**
     * Vérifie si une route est active (utile pour le menu par exemple).
     */
    public static function isActiveRoute(string $path): bool
    {
        return self::normalizePath($_SERVER["REQUEST_URI"]) === $path;
    }
}
