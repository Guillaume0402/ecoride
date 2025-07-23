<?php


namespace App\Controller;

use App\Entity\User;
use App\Model\UserModel;

class AuthController
{
    private UserModel $userModel;

    public function __construct()
    {
        // error_log("CONSTRUCTEUR AuthController APPELÉ");
        $this->userModel = new UserModel();
    }

    // MÉTHODES POUR AFFICHER LES PAGES (vos routes existantes)
    public function showLogin(): void
    {
        // Afficher la page de connexion
        require_once APP_ROOT . '/src/View/pages/login.php';
    }

    // MÉTHODES API POUR VOTRE MODAL (nouvelles routes)
    public function apiRegister(): void
    {
        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
                throw new \Exception('Tous les champs sont obligatoires');
            }

            if ($data['password'] !== $data['confirmPassword']) {
                throw new \Exception('Les mots de passe ne correspondent pas');
            }

            if ($this->userModel->findByEmail($data['email'])) {
                throw new \Exception('Cet email est déjà utilisé');
            }

            // if (!class_exists('App\Entity\User')) {
            //     error_log("La classe App\\Entity\\User n'existe pas !");
            // } else {
            //     error_log("La classe App\\Entity\\User est bien trouvée !");
            // }
            $user = new User($data['username'], $data['email']);
            $user->hashPassword($data['password']);

            if ($this->userModel->save($user)) {
                echo json_encode(['success' => true, 'message' => 'Inscription réussie !']);
            } else {
                throw new \Exception('Erreur lors de l\'inscription');
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function apiLogin(): void
    {
        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['email']) || empty($data['password'])) {
                throw new \Exception('Email et mot de passe requis');
            }

            $user = $this->userModel->findByEmail($data['email']);

            if (!$user || !$user->verifyPassword($data['password'])) {
                throw new \Exception('Email ou mot de passe incorrect');
            }

            $_SESSION['user'] = [
                'name'   => $user->getPseudo(),
                'email'  => $user->getEmail(),
                'avatar' => "/assets/images/logo.svg" 
            ];

            echo json_encode([
                'success' => true,
                'message' => 'Connexion réussie !',
                'user' => ['pseudo' => $user->getPseudo()]
            ]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function apiLogout(): void
    {
        session_destroy();
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    }

    public function logout(): void
    {
        session_destroy();
        header('Location: /?logout=1');
        exit;
    }
}
