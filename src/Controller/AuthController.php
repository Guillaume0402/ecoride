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
            // Récupère les données JSON envoyées par le client
            $data = json_decode(file_get_contents('php://input'), true);

            // Vérifie que l'email et le mot de passe sont fournis
            if (empty($data['email']) || empty($data['password'])) {
                throw new \Exception('Email et mot de passe requis');
            }

            // Recherche l'utilisateur par email
            $user = $this->userModel->findByEmail($data['email']);

            // Vérifie l'existence de l'utilisateur et la validité du mot de passe
            if (!$user || !$user->verifyPassword($data['password'])) {
                throw new \Exception('Email ou mot de passe incorrect');
            }

            // Stocke les infos utilisateur en session (connexion)
            
            $_SESSION['user'] = [
                'name'   => $user->getPseudo(),
                'email'  => $user->getEmail(),
                'roleId' => $user->getRoleId(),
                'role'   => $user->getRoleName(),
                'avatar' => "/assets/images/logo.svg"
            ];

            // Détermine l'URL de redirection selon le rôle
            switch ($user->getRoleId()) {
                case 3: // admin
                    $redirectUrl = '/admin/dashboard';
                    break;
                case 2: // employé
                    $redirectUrl = '/employe';
                    break;
                case 1: // utilisateur
                default:
                    $redirectUrl = '/';
                    break;
            }

            // Retourne le succès et le pseudo au format JSON
            echo json_encode([
                'success' => true,
                'message' => 'Connexion réussie !',
                'user' => ['pseudo' => $user->getPseudo()],
                'redirect' => $redirectUrl
            ]);
        } catch (\Exception $e) {
            // Retourne l'erreur au format JSON
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
