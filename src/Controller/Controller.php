<?php

namespace App\Controller;

class Controller
{
    protected function render(string $view, array $data = []): void
    {
        // echo "Tentative de rendu de : " . $view . "<br>";

        // Extraire les données pour les rendre disponibles dans la vue
        extract($data);
        
        // Chemin vers le fichier de vue
        $viewPath = APP_ROOT . '/src/View/' . $view . '.php';
        // echo "Chemin de la vue : " . $viewPath . "<br>";
        
        // Vérifier si le fichier existe
        if (!file_exists($viewPath)) {
            throw new \Exception("Le fichier de vue {$viewPath} n'existe pas.");
        }
        
        // Capturer le contenu de la vue
        ob_start();
        require $viewPath;
        $content = ob_get_clean();
        
        // Rendre avec le layout
        require APP_ROOT . '/src/View/layout.php';
    }
}