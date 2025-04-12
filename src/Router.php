<?php
namespace App;

class Router {
    protected $routes = [];

    /**
     * Ajoute une route GET.
     *
     * @param string $uri URI à faire correspondre
     * @param mixed  $callback La callback associée (ex. "HomeController@index")
     */
    public function get($uri, $callback) {
        $this->addRoute('GET', $uri, $callback);
    }

    /**
     * Ajoute une route POST.
     *
     * @param string $uri URI à faire correspondre
     * @param mixed  $callback La callback associée
     */
    public function post($uri, $callback) {
        $this->addRoute('POST', $uri, $callback);
    }

    /**
     * Ajoute une route pour une méthode HTTP donnée.
     *
     * @param string $method Méthode HTTP (GET, POST, etc.)
     * @param string $uri URI à associer
     * @param mixed  $callback Fonction ou chaîne "Controller@method"
     */
    protected function addRoute($method, $uri, $callback) {
        $method = strtoupper($method);
        $this->routes[$method][$uri] = $callback;
    }

    /**
     * Dispatch l'URI demandée après avoir retiré le base path.
     *
     * @param string $requestUri L'URI complète de la requête
     */
    public function dispatch($requestUri) {
        // 1) Récupère la méthode HTTP (GET, POST, etc.)
        $method = $_SERVER['REQUEST_METHOD'];
        
        // 2) Définir ton base path (le chemin fixe de ton projet)
        // Adapte la valeur suivante selon l'emplacement de ton projet.
        $basePath = '/EcoRide/public';
        
        // 3) Retirer le base path de l'URI, si présent.
        if (strpos($requestUri, $basePath) === 0) {
            $requestUri = substr($requestUri, strlen($basePath));
        }
        
        // 4) Normaliser l'URI (supprime le slash final, et si vide, devient '/')
        $requestUri = rtrim($requestUri, '/') ?: '/';

        // 5) Vérifier si une route correspond
        if (isset($this->routes[$method][$requestUri])) {
            $callback = $this->routes[$method][$requestUri];

            // A) Si le callback est une chaîne de type "Controller@method"
            if (is_string($callback)) {
                list($controller, $action) = explode('@', $callback);

                // On suppose que nos contrôleurs se trouvent dans le namespace "App\controller\"
                $controller = "App\\controller\\" . $controller;
                if (class_exists($controller)) {
                    $controllerInstance = new $controller();
                    if (method_exists($controllerInstance, $action)) {
                        call_user_func([$controllerInstance, $action]);
                        return;
                    } else {
                        header("HTTP/1.0 500 Internal Server Error");
                        echo "Méthode '$action' inexistante dans le contrôleur $controller.";
                        return;
                    }
                } else {
                    header("HTTP/1.0 500 Internal Server Error");
                    echo "Contrôleur '$controller' introuvable.";
                    return;
                }
            }

            // B) Si c'est une fonction anonyme (Closure)
            if (is_callable($callback)) {
                call_user_func($callback);
                return;
            }
        }

        // 6) Si aucune route ne correspond, renvoyer un 404.
        header("HTTP/1.0 404 Not Found");
        echo "<h2>404 – Page introuvable</h2>";
    }
}
