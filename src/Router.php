<?php

namespace App;

class Router
{
    public static string $basePath = '';

    protected array $routes = [];

    /**
     * Déclare une route GET.
     */
    public function get(string $uri, $callback): void
    {
        $this->addRoute('GET', $uri, $callback);
    }

    /**
     * Déclare une route POST.
     */
    public function post(string $uri, $callback): void
    {
        $this->addRoute('POST', $uri, $callback);
    }

    /**
     * Ajoute une route à la liste.
     */
    protected function addRoute(string $method, string $uri, $callback): void
    {
        $uri = rtrim($uri, '/') ?: '/';
        $this->routes[strtoupper($method)][$uri] = $callback;
    }

    /**
     * Lance le dispatch de l'URL demandée.
     */
    public function dispatch(string $requestUri): void
    {
        $method = $_SERVER['REQUEST_METHOD'];

        // Nettoyage de l'URL (suppression du basePath)
        if (str_starts_with($requestUri, self::$basePath)) {
            $requestUri = substr($requestUri, strlen(self::$basePath));
        }

        $requestUri = rtrim($requestUri, '/') ?: '/';

        // Correspondance avec une route définie
        if (!isset($this->routes[$method][$requestUri])) {
            $this->abort404("Aucune route ne correspond à : '$requestUri'");
            return;
        }

        $callback = $this->routes[$method][$requestUri];

        // Si callback est "NomController@action"
        if (is_string($callback)) {
            [$controllerName, $methodName] = explode('@', $callback);
            $controllerClass = "App\\controller\\$controllerName";

            if (!class_exists($controllerClass)) {
                $this->abort500("Contrôleur introuvable : $controllerClass");
                return;
            }

            $controller = new $controllerClass();

            if (!method_exists($controller, $methodName)) {
                $this->abort500("Méthode '$methodName' introuvable dans $controllerClass");
                return;
            }

            call_user_func([$controller, $methodName]);
            return;
        }

        // Si callback est une fonction anonyme
        if (is_callable($callback)) {
            call_user_func($callback);
            return;
        }

        $this->abort500("Callback non valide pour la route : '$requestUri'");
    }

    /**
     * Affiche une erreur 404.
     */
    protected function abort404(string $message = ''): void
    {
        http_response_code(404);
        echo "<h1>404 - Page non trouvée</h1>";
        if ($message) echo "<p>$message</p>";
    }

    /**
     * Affiche une erreur 500.
     */
    protected function abort500(string $message = ''): void
    {
        http_response_code(500);
        echo "<h1>500 - Erreur serveur</h1>";
        if ($message) echo "<p>$message</p>";
    }
}
