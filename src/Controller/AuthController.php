<?php

namespace App\Controller;

use App\Entity\UserEntity;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        $this->render("pages/login");
    }

    public function apiRegister(): void
    {
        $this->jsonResponse(function () {
            $data = json_decode(file_get_contents('php://input'), true);

            // Validation basique
            if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
                throw new \Exception('Tous les champs sont obligatoires');
            }
            if ($data['password'] !== ($data['confirmPassword'] ?? '')) {
                throw new \Exception('Les mots de passe ne correspondent pas');
            }

            // Vérification existence utilisateur
            if ($this->userRepository->findByEmail($data['email'])) {
                throw new \Exception('Cet email est déjà utilisé');
            }
            if ($this->userRepository->findByPseudo($data['username'])) {
                throw new \Exception('Ce pseudo est déjà pris');
            }

            // Création de l’objet User
            $user = (new UserEntity())
                ->setPseudo($data['username'])
                ->setEmail($data['email']);

            // Validation avancée via service
            $errors = $this->userService->validate($user);
            if (!empty($errors)) {
                throw new \Exception(implode(', ', $errors));
            }

            // Hash du mot de passe
            $this->userService->hashPassword($user, $data['password']);

            // Sauvegarde en DB
            if (!$this->userRepository->create($user)) {
                throw new \Exception('Erreur lors de l\'inscription');
            }

            // Récupérer le user complet depuis la DB
            $newUser = $this->userRepository->findByEmail($data['email']);

            // Vérifier si le compte est actif avant connexion auto
            if (!$newUser->getIsActive()) {
                throw new \Exception('Votre compte a été créé mais désactivé. Contactez l\'administrateur.');
            }

            // Créer la session sécurisée
            $this->createUserSession($newUser);

            $redirectUrl = match ((int) $newUser->getRoleId()) {
                3 => '/admin',
                2 => '/employe',
                default => '/',
            };

            return [
                'success' => true,
                'message' => 'Inscription réussie et connexion automatique !',
                'user'    => ['pseudo' => $newUser->getPseudo()],
                'redirect' => $redirectUrl
            ];
        });
    }

    public function apiLogin(): void
    {
        $this->jsonResponse(function () {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['email']) || empty($data['password'])) {
                throw new \Exception('Email et mot de passe requis');
            }

            // Recherche utilisateur
            $user = $this->userRepository->findByEmail($data['email']);
            if (!$user || !$this->userService->verifyPassword($user, $data['password'])) {
                throw new \Exception('Email ou mot de passe incorrect');
            }

            // Vérification du statut
            if (!$user->getIsActive()) {
                throw new \Exception('Votre compte a été désactivé. Contactez l\'administrateur.');
            }

            // Créer la session sécurisée
            $this->createUserSession($user);

            $redirectUrl = match ((int) $user->getRoleId()) {
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


    // Helper pour les réponses JSON avec gestion d’erreurs

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


    // Création de session uniforme et sécurisée

    private function createUserSession(UserEntity $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'        => $user->getId(),
            'pseudo'    => $user->getPseudo(),
            'email'     => $user->getEmail(),
            'role_id'   => $user->getRoleId(),
            'role_name' => $this->userService->getRoleName($user),
            'photo'     => $user->getPhoto() ?: '/assets/images/logo.svg'
        ];
    }
}
