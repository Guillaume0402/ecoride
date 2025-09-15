<?php

namespace App\Controller;

use App\Entity\UserEntity;
use App\Security\Csrf;
use App\Service\Flash;
use App\Security\PasswordPolicy;




// Contrôleur d'auth: vue login + API JSON (register/login/logout)
class AuthController extends Controller
{
    // Affiche la page de connexion (vue HTML classique).

    public function showLogin(): void
    {
        $this->render("pages/login");
    }

    // API inscription JSON: validations, création user, session, redirection
    public function apiRegister(): void
    {
        $this->jsonResponse(function () {
            // Payload JSON
            $data = json_decode(file_get_contents('php://input'), true);

            // --- CSRF check (JSON) ---
            $token = $data['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
            if (!Csrf::check($token)) {
                throw new \Exception('Requête invalide (CSRF)');
            }

            // Champs requis
            $username = trim($data['username'] ?? '');
            $email    = trim($data['email'] ?? '');
            $email = mb_strtolower($email);
            $password = (string)($data['password'] ?? '');
            $confirm  = (string)($data['confirmPassword'] ?? '');

            if ($username === '' || $email === '' || $password === '') {
                throw new \Exception('Tous les champs sont obligatoires.');
            }
            if ($password !== $confirm) {
                throw new \Exception('Les mots de passe ne correspondent pas.');
            }

            // Unicité
            if ($this->userRepository->findByEmail($email)) {
                throw new \Exception('Cet email est déjà utilisé.');
            }
            if ($this->userRepository->findByPseudo($username)) {
                throw new \Exception('Ce pseudo est déjà pris.');
            }

            // Politique de mot de passe (robustesse)
            $pwdErrors = PasswordPolicy::validate($password, $username, $email);
            if (!empty($pwdErrors)) {
                // On renvoie tous les messages d’un coup pour l’UX
                throw new \Exception(implode(' ', $pwdErrors));
            }

            // Entité minimale
            $user = (new UserEntity())
                ->setPseudo($username)
                ->setEmail($email);

            // Validation métier (service existant)
            $errors = $this->userService->validate($user);
            if (!empty($errors)) {
                throw new \Exception(implode(', ', $errors));
            }

            // Hash sécurisé via ton service (qui utilisera Argon2id/bcrypt)
            $this->userService->hashPassword($user, $password);

            // Persistance
            if (!$this->userRepository->create($user)) {
                throw new \Exception('Erreur lors de l\'inscription.');
            }

            // Récup utilisateur complet
            $newUser = $this->userRepository->findByEmail($email);

            if (!$newUser->getIsActive()) {
                throw new \Exception('Votre compte a été créé mais désactivé. Contactez l\'administrateur.');
            }

            // Session + redirection
            $this->createUserSession($newUser);
            $redirectUrl = match ((int) $newUser->getRoleId()) {
                3 => '/admin',
                2 => '/employe',
                default => '/',
            };

            return [
                'success'  => true,
                'message'  => 'Inscription réussie et connexion automatique !',
                'user'     => ['pseudo' => $newUser->getPseudo()],
                'redirect' => $redirectUrl
            ];
        });
    }


    // API login JSON: vérification identifiants + session + redirection
    public function apiLogin(): void
    {
        $this->jsonResponse(function () {
            // Récupération du payload JSON
            $data = json_decode(file_get_contents('php://input'), true);

            // --- CSRF check (JSON) ---
            $token = $data['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
            if (!Csrf::check($token)) {
                throw new \Exception('Requête invalide (CSRF)');
            }


            // Validation des champs requis
            if (empty($data['email']) || empty($data['password'])) {
                throw new \Exception('Email et mot de passe requis');
            }


            $email = trim($data['email'] ?? '');
            $email = mb_strtolower($email);

            // Recherche de l'utilisateur puis vérification du mot de passe
            $user = $this->userRepository->findByEmail($data['email']);
            if (!$user || !$this->userService->verifyPassword($user, $data['password'])) {
                throw new \Exception('Email ou mot de passe incorrect');
            }

            /** ─── Rehash automatique si nécessaire ─── */
            if ($this->userService->needsRehash($user)) {
                $newHash = $this->userService->rehash($user, $data['password']);
                if (!$this->userRepository->updatePasswordById($user->getId(), $newHash)) {
                    // Ici on pourrait logger l'erreur serveur, mais on ne bloque pas l'utilisateur
                }
                $user->setPassword($newHash); // synchronise l’objet en mémoire
            }

            // Vérification du statut actif
            if (!$user->getIsActive()) {
                throw new \Exception('Votre compte a été désactivé. Contactez l\'administrateur.');
            }

            // Création de la session sécurisée
            $this->createUserSession($user);

            // Détermination de l'URL de redirection selon le rôle
            $redirectUrl = match ((int) $user->getRoleId()) {
                3 => '/admin',
                2 => '/employe',
                default => '/',
            };

            // Réponse JSON standardisée
            return [
                'success' => true,
                'message' => 'Connexion réussie !',
                'user'    => ['pseudo' => $user->getPseudo()],
                'redirect' => $redirectUrl
            ];
        });
    }

    // API logout JSON: détruit la session et renvoie success
    public function apiLogout(): void
    {
        session_destroy();
        $this->json(['success' => true]);
    }

    // Déconnexion (HTML) + redirection
    public function logout(): void
    {
        // Ne PAS détruire la session avant le flash
        unset($_SESSION['user']);              // on déconnecte l’utilisateur
        Flash::add('Vous êtes bien déconnecté(e).', 'success');  // on stocke le message
        session_regenerate_id(true);                        // hygiène de session
        redirect('/');                                                     // le flash s’affichera sur la page d’arrivée
    }



    // Helper pour les réponses JSON avec gestion d’erreurs
    // Helper: exécute un callback et renvoie une réponse JSON (capture exceptions)
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

    // Helper: sérialise un tableau en JSON et termine
    private function json(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }


    // Création de session uniforme et sécurisée

    // Crée/rafraîchit la session utilisateur de manière sécurisée
    private function createUserSession(UserEntity $user): void
    {
        // Empêche la fixation de session
        session_regenerate_id(true);

        // Stockage des informations essentielles de l'utilisateur
        $_SESSION['user'] = [
            'id'        => $user->getId(),
            'pseudo'    => $user->getPseudo(),
            'email'     => $user->getEmail(),
            'role_id'   => $user->getRoleId(),
            'role_name' => $this->userService->getRoleName($user),
                'credits'   => $user->getCredits(),
            'photo'     => $user->getPhoto() ?: (defined('DEFAULT_AVATAR_URL') ? DEFAULT_AVATAR_URL : '/assets/images/logo.svg')
        ];
    }
}
