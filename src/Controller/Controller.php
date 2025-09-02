<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\UserService;

/**
 * Contrôleur de base de l'application.
 * - Démarre la session si nécessaire.
 * - Initialise les services et dépôts communs.
 * - Fournit une méthode render() pour afficher les vues avec le layout global.
 */
class Controller
{
    //Accès au dépôt des utilisateurs pour les opérations de persistance.
    
    protected UserRepository $userRepository;

    // Service métier lié aux utilisateurs (logique applicative).
     
    protected UserService $userService;

    // Initialise la session et les services/dépôts communs.     
    public function __construct()
    {
        // Démarre la session PHP si elle n'est pas déjà active
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Initialisation des services communs
        $this->userRepository = new UserRepository();
        $this->userService = new UserService();
    }

    /**
     * Rend une vue au sein du layout global.
     * @param string $view Chemin relatif de la vue (sans extension), ex: 'pages/home'.
     * @param array $data Données à exposer à la vue (variables extraites).
     * @return void
     * @throws \Exception Si le fichier de vue n'existe pas.
     */
    protected function render(string $view, array $data = []): void
    {
        // Expose les clés de $data comme variables accessibles dans la vue
        extract($data);

        // Construit le chemin absolu de la vue et vérifie son existence
        $viewPath = APP_ROOT . '/src/View/' . ltrim($view, '/') . '.php';
        if (!file_exists($viewPath)) {
            throw new \Exception("Le fichier de vue {$viewPath} n'existe pas.");
        }

        // Capture le rendu de la vue dans $content
        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        // Inclut le layout qui utilise $content pour afficher la page complète
        require APP_ROOT . '/src/View/layout.php';
    }
}
