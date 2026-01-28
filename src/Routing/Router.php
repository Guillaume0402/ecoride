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
        // Log de debug uniquement en environnement de développement
        $appEnv = $_ENV['APP_ENV'] ?? (getenv('APP_ENV') ?: 'prod');
        if ($appEnv === 'dev') {
            error_log("Routes chargées : " . print_r(array_keys($this->routes), true));
        }
    }

  
    // transforme URL en appel de contrôleur + action - cherche la route correspondante - instancie le contrôleur et appelle l'action
    public function handleRequest(string $uri): void
    {
        // Normalise le chemin de l'URI et récupère la méthode HTTP
        $path = $this->normalizePath($uri);
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Log de debug uniquement en environnement de développement
        $appEnv = $_ENV['APP_ENV'] ?? (getenv('APP_ENV') ?: 'prod');        
        if ($appEnv === 'dev') {
            error_log("Recherche de la route : $path");
        }

        // je prépare mes variables(controller+action) et les paramètres dynamiques (ex ['id' => 12]) 
        $matchedRoute = null;
        $params = [];

        // je boucle pour chercher une route correspondante
        foreach ($this->routes as $routePath => $routeConfig) {
            
            // Je transforme le chemin de la route en expression régulière {id} en (\d+)regex
            $pattern = preg_replace('#\{[a-zA-Z_]+\}#', '(\d+)', $routePath);

            // Je teste si la route correspond au chemin demandé
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

        // Si aucune route ne correspond → 404
        if (!$matchedRoute) {            
            (new \App\Controller\ErrorController())->show404("Route non trouvée : $path");
            return;
        }

        // Si la route existe mais pas pour cette méthode HTTP → 405
        if (!isset($matchedRoute['controller']) || !isset($matchedRoute['action'])) {
            (new \App\Controller\ErrorController())->show405("Méthode $method non autorisée pour $path");
            return;
        }

        // Récupère le contrôleur et l'action à appeler
        $controllerPath = $matchedRoute['controller'];
        $action = $matchedRoute['action'];


        // Vérifie que le contrôleur et l'action existent
        if (!class_exists($controllerPath)) {
            abort(500, "Controller introuvable : $controllerPath");
        }

        // Instancie le contrôleur
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
