<?php

namespace App\Controller;

class AdminController
{
    public function __construct()
    {
        // Vérifie que l'utilisateur est connecté et est admin
        if (!isset($_SESSION['user']) || $_SESSION['user']['roleId'] !== 3) {
            http_response_code(403);
            exit('Accès interdit');
        }
    }

    /**
     * Page d'accueil de l'administration
     */
    public function dashboard(): void
    {
        ob_start();
        require_once APP_ROOT . '/src/View/pages/admin/dashboard.php';
        $content = ob_get_clean();

        require_once APP_ROOT . '/src/View/layout.php';
    }

    public function stats(): void
    {
        ob_start();
        require_once APP_ROOT . '/src/View/pages/admin/stats.php';
        $content = ob_get_clean();

        require_once APP_ROOT . '/src/View/layout.php';
    }

    /**
     * Page de gestion des utilisateurs/employés
     */
    public function users(): void
    {
        // Ici on instancie UserModel
        $userModel = new \App\Model\UserModel();

        // On récupère uniquement les utilisateurs et employés
        $users = $userModel->findAllWithRoles([1, 2]);

        // Injection dans la vue
        ob_start();
        require_once APP_ROOT . '/src/View/pages/admin/users.php';
        $content = ob_get_clean();

        require_once APP_ROOT . '/src/View/layout.php';
    }
}
