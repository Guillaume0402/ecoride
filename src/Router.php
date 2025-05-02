<?php

namespace App;

class Router
{
    public static string $basePath = '';
    protected array $routes = [];

    public function get(string $uri, $callback): void
    {
        $this->addRoute('GET', $uri, $callback);
    }

    public function post(string $uri, $callback): void
    {
        $this->addRoute('POST', $uri, $callback);
    }

    protected function addRoute(string $method, string $uri, $callback): void
    {
        $uri = '/' . trim($uri, '/'); // normalise avec 1 seul slash
        if ($uri === '/') {
            $uri = '/';
        }
        $this->routes[strtoupper($method)][$uri] = $callback;
    }

    public function dispatch(string $requestUri): void
    {
        $method = $_SERVER['REQUEST_METHOD'];

        // Nettoie l’URL : supprime les query string et basePath
        $requestUri = parse_url($requestUri, PHP_URL_PATH);
        if (!empty(self::$basePath) && str_starts_with($requestUri, self::$basePath)) {
            $requestUri = substr($requestUri, strlen(self::$basePath));
        }

        $requestUri = '/' . trim($requestUri, '/');
        if ($requestUri === '/') {
            $requestUri = '/';
        }

        if (!isset($this->routes[$method][$requestUri])) {
            $this->abort404("Aucune route ne correspond à : '$requestUri'");
            return;
        }

        $callback = $this->routes[$method][$requestUri];

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

        if (is_callable($callback)) {
            call_user_func($callback);
            return;
        }

        $this->abort500("Callback non valide pour la route : '$requestUri'");
    }

    protected function abort404(string $message = ''): void
    {
        http_response_code(404);
        view('error-404', ['message' => $message]);
        
    }

    protected function abort500(string $message = ''): void
    {
        http_response_code(500);
        view('error-500', ['message' => $message]);
        exit;
    }
}
