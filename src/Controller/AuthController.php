<?php

namespace App\Controller;

use App\Entity\UserEntity;

/**
 * Contrôleur d'authentification.
 * - Affiche la page de connexion.
 * - Fournit des endpoints API JSON pour l'inscription, la connexion et la déconnexion.
 * - Gère la création de session utilisateur et des réponses JSON standardisées.
 */
class AuthController extends Controller
{
    // Affiche la page de connexion (vue HTML classique).
    
    public function showLogin(): void
    {
        $this->render("pages/login");
    }

    /**
     * Endpoint API d'inscription (JSON).
     * - Valide les données reçues en JSON
     * - Vérifie l'unicité email/pseudo
     * - Crée l'utilisateur, hash le mot de passe et persiste en base
     * - Crée la session et retourne l'URL de redirection en JSON     
     */
    public function apiRegister(): void
    {
        $this->jsonResponse(function () {
            // Récupération du payload JSON
            $data = json_decode(file_get_contents('php://input'), true);

            // Validation basique des champs requis
            if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
                throw new \Exception('Tous les champs sont obligatoires');
            }
            if ($data['password'] !== ($data['confirmPassword'] ?? '')) {
                throw new \Exception('Les mots de passe ne correspondent pas');
            }

            // Vérifications d'unicité (email et pseudo)
            if ($this->userRepository->findByEmail($data['email'])) {
                throw new \Exception('Cet email est déjà utilisé');
            }
            if ($this->userRepository->findByPseudo($data['username'])) {
                throw new \Exception('Ce pseudo est déjà pris');
            }

            // Construction de l'entité utilisateur minimale
            $user = (new UserEntity())
                ->setPseudo($data['username'])
                ->setEmail($data['email']);

            // Validation métier via le service
            $errors = $this->userService->validate($user);
            if (!empty($errors)) {
                throw new \Exception(implode(', ', $errors));
            }

            // Hash sécurisé du mot de passe
            $this->userService->hashPassword($user, $data['password']);

            // Persistance en base de données
            if (!$this->userRepository->create($user)) {
                throw new \Exception('Erreur lors de l\'inscription');
            }

            // Récupération de l'utilisateur complet (avec champs par défaut DB)
            $newUser = $this->userRepository->findByEmail($data['email']);

            // Refus de connexion si le compte est désactivé
            if (!$newUser->getIsActive()) {
                throw new \Exception('Votre compte a été créé mais désactivé. Contactez l\'administrateur.');
            }

            // Création de la session sécurisée
            $this->createUserSession($newUser);

            // Détermination de l'URL de redirection selon le rôle
            $redirectUrl = match ((int) $newUser->getRoleId()) {
                3 => '/admin',
                2 => '/employe',
                default => '/',
            };

            // Réponse JSON standardisée
            return [
                'success' => true,
                'message' => 'Inscription réussie et connexion automatique !',
                'user'    => ['pseudo' => $newUser->getPseudo()],
                'redirect' => $redirectUrl
            ];
        });
    }

    /**
     * Endpoint API de connexion (JSON).
     * - Valide les identifiants
     * - Vérifie l'état du compte
     * - Crée la session et retourne l'URL de redirection     
     */
    public function apiLogin(): void
    {
        $this->jsonResponse(function () {
            // Récupération du payload JSON
            $data = json_decode(file_get_contents('php://input'), true);

            // Validation des champs requis
            if (empty($data['email']) || empty($data['password'])) {
                throw new \Exception('Email et mot de passe requis');
            }

            // Recherche de l'utilisateur puis vérification du mot de passe
            $user = $this->userRepository->findByEmail($data['email']);
            if (!$user || !$this->userService->verifyPassword($user, $data['password'])) {
                throw new \Exception('Email ou mot de passe incorrect');
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

    /**
     * Endpoint API de déconnexion (JSON).
     * Détruit la session puis renvoie un statut de succès.     
     */
    public function apiLogout(): void
    {
        session_destroy();
        $this->json(['success' => true]);
    }

    /**
     * Déconnexion (flux classique HTML) puis redirection vers l'accueil.
     * @return void
     */
    public function logout(): void
    {
        session_destroy();
        redirect('/?logout=1');
    }


    // Helper pour les réponses JSON avec gestion d’erreurs

    /**
     * Enveloppe utilitaire pour retourner des réponses JSON avec gestion d'exceptions.
     * - Définit les en-têtes JSON
     * - Exécute un callback et sérialise son résultat
     * - Capture les exceptions et renvoie un message d'erreur standardisé
     * - Termine l'exécution du script
     * @param callable $callback Fonction exécutée dont le résultat est renvoyé en JSON     
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

    /**
     * Renvoie une réponse JSON simple et termine l'exécution.
     * @param array $data Données à sérialiser en JSON     
     */
    private function json(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }


    // Création de session uniforme et sécurisée

    /**
     * Crée/rafraîchit la session utilisateur de manière sécurisée (regeneration ID)
     * et stocke les informations nécessaires à l'application.
     * @param UserEntity $user Utilisateur authentifié     
     */
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
            'photo'     => $user->getPhoto() ?: '/assets/images/logo.svg'
        ];
    }
}
