<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\UserService;
use App\Repository\VehicleRepository;

// Contrôleur de base: session, services communs et rendu des vues avec layout
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

    // Rend une vue au sein du layout global (extrait $data, bufferise la vue, inclut layout)
    protected function render(string $view, array $data = []): void
    {
        // Expose les clés de $data comme variables accessibles dans la vue
        extract($data);

        // Variables globales utiles aux vues/partials
        $hasVehicle = false;
        $userVehicles = [];
        if (isset($_SESSION['user']) && !empty($_SESSION['user']['id'])) {
            try {
                $vehicleRepo = new VehicleRepository();
                // On peut retourner des entités; les vues existantes manipulent déjà des entités côté profil
                $userVehicles = $vehicleRepo->findAllByUserId((int) $_SESSION['user']['id']);
                $hasVehicle = !empty($userVehicles);
            } catch (\Throwable $e) {
                // N'écrase pas l'affichage si la DB est indisponible; expose juste false
                error_log('[render] Vehicle preload failed: ' . $e->getMessage());
            }
        }

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
