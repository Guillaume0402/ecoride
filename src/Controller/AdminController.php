<?php

namespace App\Controller;

class AdminController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = "Veuillez vous connecter.";
            redirect('/login');
        }

        if ($_SESSION['user']['role_id'] !== 3) {
            abort(403, "Accès interdit");
        }
    }

    /**
     * Page d'accueil de l'administration
     */
    public function dashboard(): void
    {
        $this->render("pages/admin/dashboard");
    }

    public function stats(): void
    {
        $this->render("pages/admin/stats", [
        'isAdminPage' => true
    ]);
    }

    /**
     * Page de gestion des utilisateurs/employés
     */
    public function users(): void
    {
        $userModel = new \App\Model\UserModel();
        $users = $userModel->findAllWithRoles([1, 2]);

        $this->render("pages/admin/users", [
            'users' => $users
        ]);
    }
}
