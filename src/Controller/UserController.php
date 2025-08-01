<?php

namespace App\Controller;

class UserController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = "Veuillez vous connecter.";
            redirect('/login');
        }
    }

    // ✅ Exemple de future méthode (liste des utilisateurs)
    public function listUsers(): void
    {
        $users = $this->userRepository->findAllUsers();

        $this->render('pages/user/list', [
            'users' => $users
        ]);
    }

    // ✅ Exemple : afficher un profil utilisateur
    public function show(int $id): void
    {
        $user = $this->userRepository->findById($id);

        if (!$user) {
            abort(404, "Utilisateur non trouvé");
        }

        $this->render('pages/user/profile', [
            'user' => $this->userService->toArray($user)
        ]);
    }
}
