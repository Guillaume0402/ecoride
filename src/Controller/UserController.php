<?php

namespace App\Controller;

/**
 * Contrôleur dédié aux utilisateurs.
 * - Protège l'accès en vérifiant la session.
 * - Liste les utilisateurs et affiche un profil.
 */
class UserController extends Controller
{
    // Initialise les dépendances et protège les routes en exigeant une session utilisateur active.     
    public function __construct()
    {
        parent::__construct();

        // Si aucun utilisateur n'est authentifié, message d'erreur et redirection vers la page de connexion
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = "Veuillez vous connecter.";
            redirect('/login');
        }
    }

    /**
     * Affiche la liste des utilisateurs standard.
     * Récupère les utilisateurs via le dépôt puis rend la vue correspondante.
     */
    public function listUsers(): void
    {
        // Récupération de tous les utilisateurs (role_id = 1)
        $users = $this->userRepository->findAllUsers();

        // Injection des données dans la vue de liste
        $this->render('pages/user/list', [
            'users' => $users
        ]);
    }

    /**
     * Affiche le profil d'un utilisateur par son identifiant.
     * 404 si l'utilisateur n'existe pas.     *
     * @param int $id Identifiant de l'utilisateur
     */
    public function show(int $id): void
    {
        // Récupération de l'utilisateur
        $user = $this->userRepository->findById($id);

        // Si non trouvé, on interrompt avec une 404
        if (!$user) {
            abort(404, "Utilisateur non trouvé");
        }

        // Conversion en tableau via le service pour la vue
        $this->render('pages/user/profile', [
            'user' => $this->userService->toArray($user)
        ]);
    }
}
