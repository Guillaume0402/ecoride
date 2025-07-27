<?php


namespace App\Controller;

use App\Entity\User;
use App\Model\UserModel;

class AuthController
{
    // Modèle utilisateur pour accéder aux données des utilisateurs
    private UserModel $userModel;

    public function __construct()
    {
        // Instancie le modèle UserModel à chaque création du contrôleur
        $this->userModel = new UserModel();
    }

    // Affiche la page de connexion (route classique)
    public function showLogin(): void
    {
        // Charge la vue de connexion
        require_once APP_ROOT . '/src/View/pages/login.php';
    }

    // API : Inscription d'un nouvel utilisateur (appelée via AJAX)
    public function apiRegister(): void
    {
        header('Content-Type: application/json');

        try {
            // Récupère les données JSON envoyées par le client
            $data = json_decode(file_get_contents('php://input'), true);

            // Vérifie que tous les champs sont remplis
            if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
                throw new \Exception('Tous les champs sont obligatoires');
            }

            // Vérifie la correspondance des mots de passe
            if ($data['password'] !== $data['confirmPassword']) {
                throw new \Exception('Les mots de passe ne correspondent pas');
            }

            // Vérifie si l'email existe déjà
            if ($this->userModel->findByEmail($data['email'])) {
                throw new \Exception('Cet email est déjà utilisé');
            }

            // Vérifie si le pseudo existe déjà
            if ($this->userModel->findByPseudo($data['username'])) {
                throw new \Exception('Ce pseudo est déjà pris');
            }

            // Crée un nouvel utilisateur et hash le mot de passe
            $user = new User($data['username'], $data['email']);
            $user->hashPassword($data['password']);

            // Sauvegarde l'utilisateur en base de données
            if ($this->userModel->save($user)) {
                echo json_encode(['success' => true, 'message' => 'Inscription réussie !']);
            } else {
                throw new \Exception('Erreur lors de l\'inscription');
            }
        } catch (\Exception $e) {
            // Retourne l'erreur au format JSON
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    // API : Connexion utilisateur (appelée via AJAX)
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

            // Récupération complète depuis toArray()
            $userArray = $user->toArray();

            // 🔥 Ajout manuel de compatibilité pour le header
            $userArray['roleId']  = $user->getRoleId();       // pour ton switch redirection
            $userArray['role_id'] = $user->getRoleId();       // pour ton menu admin
            $userArray['avatar']  = $user->getPhoto() ?: "/assets/images/logo.svg";

            // Session utilisateur
            $_SESSION['user'] = $userArray;

            // Redirection selon rôle
            switch ($user->getRoleId()) {
                case 3:
                    $redirectUrl = '/admin/dashboard';
                    break;
                case 2:
                    $redirectUrl = '/employe';
                    break;
                default:
                    $redirectUrl = '/';
                    break;
            }

            echo json_encode([
                'success' => true,
                'message' => 'Connexion réussie !',
                'user'    => ['pseudo' => $user->getPseudo()],
                'redirect' => $redirectUrl
            ]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }

        exit;
    }


    // API : Déconnexion utilisateur (appelée via AJAX)
    public function apiLogout(): void
    {
        // Détruit la session côté serveur
        session_destroy();
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    }

    // Déconnexion classique (redirection après logout)
    public function logout(): void
    {
        // Détruit la session et redirige vers la page d'accueil avec un paramètre
        session_destroy();
        header('Location: /?logout=1');
        exit;
    }
}
