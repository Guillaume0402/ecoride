<?php

namespace App\Controller;

use App\Service\Flash;


// Contrôleur utilisateurs: garde d'accès + listing et affichage profil
class UserController extends Controller
{
    // Initialise les dépendances et protège les routes en exigeant une session utilisateur active.     
    public function __construct()
    {
        parent::__construct();

        // Si aucun utilisateur n'est authentifié, message d'erreur et redirection vers la page de connexion
        if (!isset($_SESSION['user'])) {
            Flash::add('Veuillez vous connecter.', 'danger');
            redirect('/login');
        }
    }

    // Liste les utilisateurs standard et rend la vue
    public function listUsers(): void
    {
        // Récupération de tous les utilisateurs (role_id = 1)
        $users = $this->userRepository->findAllUsers();

        // Injection des données dans la vue de liste
        $this->render('pages/user/list', [
            'users' => $users
        ]);
    }

    // Affiche un profil utilisateur; 404 si non trouvé
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
