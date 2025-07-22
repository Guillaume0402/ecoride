<?php

namespace App; // Namespace pour éviter les conflits de noms de classes

class Router
{
    public static string $basePath = ''; // Chemin de base de l'application (ex: '/monapp' si dans un sous-dossier)
    protected array $routes = []; // Stocke toutes les routes définies [méthode][uri] => callback

    public function get(string $uri, $callback): void // Définit une route GET (ex: affichage de pages)
    {
        $this->addRoute('GET', $uri, $callback); // Délègue à addRoute avec la méthode GET
    }

    public function post(string $uri, $callback): void // Définit une route POST (ex: formulaires)
    {
        $this->addRoute('POST', $uri, $callback); // Délègue à addRoute avec la méthode POST
    }

    protected function addRoute(string $method, string $uri, $callback): void // Ajoute une route au tableau des routes
    {
        $uri = '/' . trim($uri, '/'); // Normalise l'URI : supprime les / au début/fin puis remet un / au début
        if ($uri === '/') { // Si l'URI devient juste '/'
            $uri = '/'; // On garde '/' (page d'accueil)
        }
        $this->routes[strtoupper($method)][$uri] = $callback; // Stocke la route : $routes['GET']['/contact'] = 'ContactController@index'
    }

    public function dispatch(string $requestUri): void // Traite la requête entrante et trouve la bonne route
    {
        $method = $_SERVER['REQUEST_METHOD']; // Récupère la méthode HTTP (GET, POST, etc.)

        // Nettoie l'URL : supprime les query string et basePath
        $requestUri = parse_url($requestUri, PHP_URL_PATH); // Supprime ?param=value pour ne garder que le chemin
        if (!empty(self::$basePath) && str_starts_with($requestUri, self::$basePath)) { // Si l'app est dans un sous-dossier
            $requestUri = substr($requestUri, strlen(self::$basePath)); // Supprime le basePath de l'URI
        }

        $requestUri = '/' . trim($requestUri, '/'); // Normalise l'URI comme dans addRoute
        if ($requestUri === '/') { // Si l'URI devient juste '/'
            $requestUri = '/'; // On garde '/' (page d'accueil)
        }

        if (!isset($this->routes[$method][$requestUri])) { // Si aucune route ne correspond
            $this->abort404("Aucune route ne correspond à : '$requestUri'"); // Affiche la page 404
            return; // Arrête l'exécution
        }

        $callback = $this->routes[$method][$requestUri]; // Récupère le callback associé à la route

        if (is_string($callback)) { // Si le callback est une string comme 'HomeController@index'
            [$controllerName, $methodName] = explode('@', $callback); // Sépare 'HomeController' et 'index'
            $controllerClass = "App\\Controller\\$controllerName"; // Construit le nom complet de la classe

            if (!class_exists($controllerClass)) { // Vérifie si la classe du contrôleur existe
                $this->abort500("Contrôleur introuvable : $controllerClass"); // Erreur 500 si introuvable
                return; // Arrête l'exécution
            }

            $controller = new $controllerClass(); // Instancie le contrôleur

            if (!method_exists($controller, $methodName)) { // Vérifie si la méthode existe dans le contrôleur
                $this->abort500("Méthode '$methodName' introuvable dans $controllerClass"); // Erreur 500 si méthode inexistante
                return; // Arrête l'exécution
            }

            call_user_func([$controller, $methodName]); // Appelle la méthode du contrôleur (ex: $homeController->index())
            return; // Arrête l'exécution
        }

        if (is_callable($callback)) { // Si le callback est une fonction anonyme
            call_user_func($callback); // Exécute la fonction directement
            return; // Arrête l'exécution
        }

        $this->abort500("Callback non valide pour la route : '$requestUri'"); // Erreur si le callback n'est ni string ni function
    }

    protected function abort404(string $message = ''): void // Gère les erreurs 404 (page non trouvée)
    {
        http_response_code(404); // Définit le code de statut HTTP à 404
        view('error-404', ['message' => $message]); // Charge la vue d'erreur 404 avec le message

    }

    protected function abort500(string $message = ''): void // Gère les erreurs 500 (erreur serveur)
    {
        http_response_code(500); // Définit le code de statut HTTP à 500
        view('error-500', ['message' => $message]); // Charge la vue d'erreur 500 avec le message
        exit; // Force l'arrêt du script pour éviter d'autres erreurs
    }
}
