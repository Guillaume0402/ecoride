<?php

namespace App\Routing;

use App\Controller\ErrorController;


class Router
{

    private $routes;
    public function __construct()
    {
        // Chargement des routes depuis le fichier de configuration
        $this->routes = require_once APP_ROOT . "/config/routes.php";
    }


    public function handleRequest(string $uri)
    {
        try {
            // Normalisation de l'URI pour correspondre au format des routes
            $path = $this->normalizePath($uri);

            // Vérification de l'existence de la route
            if (!isset($this->routes[$path])) {
                throw new \Exception("Route not found for path: " . $path);
            }

            // Récupération des informations de la route
            $route = $this->routes[$path];
            $controllerPath = $route['controller'];
            $action = $route['action'];

            // Vérification de l'existence de la classe contrôleur
            if (!class_exists($controllerPath)) {
                throw new \Exception("Controller class not found: " . $controllerPath);
            }

            // Instanciation du contrôleur
            $controller = new $controllerPath();

            // Vérification de l'existence de la méthode (action)
            if (!method_exists($controller, $action)) {
                throw new \Exception("Action not found: " . $action . " in controller " . $controllerPath);
            }
            error_log("PATH DEMANDE : " . $path);
            // Exécution de l'action
            $controller->$action();
        } catch (\Exception $e) {
            // Gestion centralisée des erreurs
            $errorController = new ErrorController();
            $errorController->show404("Route not found: " . $uri);
        }
    }


    public static function normalizePath(string $uri): string
    {
        // Extraction du chemin sans paramètres GET ni ancres
        $path = parse_url($uri, PHP_URL_PATH);

        // Pour la racine, garder "/"
        if ($path === '/') {
            return '/';
        }

        // Pour les autres chemins, supprimer les slashes de fin
        return rtrim($path, '/');
    }

    public static function isActiveRoute(string $path): bool
    {
        // Comparaison entre l'URI actuelle normalisée et le chemin donné
        return self::normalizePath($_SERVER["REQUEST_URI"]) === $path;
    }
}
