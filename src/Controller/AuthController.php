<?php

namespace App\Controller;

use App\Entity\User;
use App\Model\UserModel;

class AuthController extends Controller
{
    private UserModel $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new UserModel();
    }

    public function showLogin(): void
    {
        $this->render("pages/login");
    }

    public function apiRegister(): void
    {
        $this->jsonResponse(function () {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
                throw new \Exception('Tous les champs sont obligatoires');
            }

            if ($data['password'] !== ($data['confirmPassword'] ?? '')) {
                throw new \Exception('Les mots de passe ne correspondent pas');
            }

            if ($this->userModel->findByEmail($data['email'])) {
                throw new \Exception('Cet email est déjà utilisé');
            }

            if ($this->userModel->findByPseudo($data['username'])) {
                throw new \Exception('Ce pseudo est déjà pris');
            }

            $user = new User($data['username'], $data['email']);
            $user->hashPassword($data['password']);

            if (!$this->userModel->save($user)) {
                throw new \Exception('Erreur lors de l\'inscription');
            }

            return ['success' => true, 'message' => 'Inscription réussie !'];
        });
    }

    public function apiLogin(): void
    {
        $this->jsonResponse(function () {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['email']) || empty($data['password'])) {
                throw new \Exception('Email et mot de passe requis');
            }

            $user = $this->userModel->findByEmail($data['email']);
            if (!$user || !$user->verifyPassword($data['password'])) {
                throw new \Exception('Email ou mot de passe incorrect');
            }

            $userArray = $user->toArray();
            $userArray['roleId']  = $user->getRoleId();
            $userArray['role_id'] = $user->getRoleId();
            $userArray['avatar']  = $user->getPhoto() ?: "/assets/images/logo.svg";

            $_SESSION['user'] = $userArray;

            $redirectUrl = match ($user->getRoleId()) {
                3 => '/admin',
                2 => '/employe',
                default => '/',
            };

            return [
                'success' => true,
                'message' => 'Connexion réussie !',
                'user'    => ['pseudo' => $user->getPseudo()],
                'redirect' => $redirectUrl
            ];
        });
    }

    public function apiLogout(): void
    {
        session_destroy();
        $this->json(['success' => true]);
    }

    public function logout(): void
    {
        session_destroy();
        redirect('/?logout=1');
    }

    /** 
     * 🔥 Helper pour les réponses JSON avec gestion d’erreurs
     */
    private function jsonResponse(callable $callback): void
    {
        header('Content-Type: application/json');
        try {
            echo json_encode($callback());
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    private function json(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
