<?php

use App\Router; // Importe la classe Router pour accéder à $basePath

if (!function_exists('url')) { // Vérifie si la fonction url() n'existe pas déjà (évite les conflits)
    function url(string $path = ''): string   // Génère une URL absolue à partir d'un chemin relatif
    { 
        return rtrim(Router::$basePath, '/') . '/' . ltrim($path, '/'); // Combine basePath + path en évitant les doubles slashes
    }
}

if (!function_exists('view')) { // Vérifie si la fonction view() n'existe pas déjà
    function view(string $viewName, array $data = []): void  // Charge et affiche une vue avec des données
    { 
        $viewPath = __DIR__ . "/View/{$viewName}.php"; // Construit le chemin vers le fichier de vue (dans src/view/)

        if (!file_exists($viewPath)) { // Vérifie si le fichier de vue existe
            http_response_code(500); // Définit le code d'erreur 500 si le fichier n'existe pas
            require __DIR__ . "/View/error-500.php"; // Charge la page d'erreur 500
            return; // Arrête l'exécution de la fonction
        }

        extract($data); // Transforme le tableau $data en variables : ['title' => 'Accueil'] devient $title = 'Accueil'
        ob_start(); // Démarre la mise en mémoire tampon de sortie (capture le HTML généré)
        require $viewPath; // Inclut le fichier de vue qui génère du HTML
        $content = ob_get_clean(); // Récupère le HTML généré et vide le tampon

        require __DIR__ . "/View/layout.php"; // Charge le template principal qui utilise $content
    }
}

if (!function_exists('asset')) { // Vérifie si la fonction asset() n'existe pas déjà
    function asset(string $path): string    // Génère l'URL complète vers un fichier asset (CSS, JS, images)
    { 
        return url('assets/' . ltrim($path, '/')); // Utilise url() en ajoutant 'assets/' au début du chemin
    }
}
