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
                throw new \Exception('Requête invalide (CSRF)', 400);
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

            // Génération d'un token de vérification email
            $token = bin2hex(random_bytes(16));
            $expires = (new \DateTimeImmutable('+24 hours'))->format('Y-m-d H:i:s');
            $user->setEmailVerified(0)
                ->setEmailVerificationToken($token)
                ->setEmailVerificationExpires(new \DateTimeImmutable($expires));

            // Persistance
            if (!$this->userRepository->create($user)) {
                throw new \Exception('Erreur lors de l\'inscription.');
            }

            // Envoi du mail de confirmation
            $verifyUrl = SITE_URL . 'verify-email?token=' . urlencode($token) . '&email=' . urlencode($email);
            $subject = 'Confirmez votre adresse email';
            $body = '<p>Bonjour ' . htmlspecialchars($username) . ',</p>' .
                '<p>Merci pour votre inscription sur EcoRide. Veuillez confirmer votre adresse email en cliquant sur le lien ci-dessous&nbsp;:</p>' .
                '<p><a href="' . htmlspecialchars($verifyUrl) . '">Confirmer mon email</a></p>' .
                '<p>Ce lien expirera dans 24 heures.</p>' .
                '<p>— L\'équipe EcoRide</p>';
            (new \App\Service\Mailer())->send($email, $subject, $body);

            return [
                'success'  => true,
                'message'  => 'Inscription réussie ! Un email de confirmation vous a été envoyé.',
                'user'     => ['pseudo' => $username],
                'redirect' => '/login'
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
                throw new \Exception('Email et mot de passe requis', 400);
            }


            $email = trim($data['email'] ?? '');
            $email = mb_strtolower($email);

            // Recherche de l'utilisateur puis vérification du mot de passe
            $user = $this->userRepository->findByEmail($email);
            $password = (string)($data['password'] ?? '');
            if (!$user || !$this->userService->verifyPassword($user, $password)) {
                throw new \Exception('Email ou mot de passe incorrect', 401);
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
                throw new \Exception('Votre compte a été désactivé. Contactez l\'administrateur.', 403);
            }

            // Vérification email confirmé
            if (method_exists($user, 'getEmailVerified') && (int)$user->getEmailVerified() !== 1) {
                throw new \Exception('Veuillez confirmer votre adresse email avant de vous connecter.', 403);
            }

            // Création de la session sécurisée
            $this->createUserSession($user);

            // Détermination de l'URL de redirection
            // Priorité au rôle (admin/employé), sinon retomber sur la redirection fournie, puis sur '/'
            $payloadRedirect = isset($data['redirect']) ? (string)$data['redirect'] : null;
            $roleRedirect = match ((int) $user->getRoleId()) {
                3 => '/admin',
                2 => '/employe',
                default => null,
            };
            $redirectUrl = $roleRedirect ?: ($payloadRedirect ?: '/');

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
        unset($_SESSION['user']);       // déconnecte l’utilisateur
        session_regenerate_id(true);    // hygiène (anti fixation)
        $this->json(['success' => true]);
    }

    // Vérifie l'email à partir d'un lien de confirmation
    public function verifyEmail(): void
    {
        $token = isset($_GET['token']) ? trim((string)$_GET['token']) : '';
        $email = isset($_GET['email']) ? trim((string)$_GET['email']) : '';
        if ($token === '' || $email === '') {
            Flash::add('Lien de vérification invalide.', 'danger');
            redirect('/login');
        }
        try {
            $ok = $this->userRepository->verifyEmailByToken($email, $token);
        } catch (\Throwable $e) {
            $ok = false;
            error_log('[verifyEmail] ' . $e->getMessage());
        }
        if ($ok) {
            Flash::add('Votre adresse email a été confirmée, vous pouvez vous connecter.', 'success');
        } else {
            Flash::add('Lien de vérification invalide ou expiré.', 'danger');
        }
        redirect('/login');
    }

    // API logout JSON: détruit la session et renvoie success
    public function logout(): void
    {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

        if (!Csrf::check($token)) {
            http_response_code(403);
            $this->json(['success' => false, 'message' => 'CSRF invalide']);
            return;
        }
        unset($_SESSION['user']);       // déconnecte l’utilisateur
        session_regenerate_id(true);    // hygiène (anti fixation)
        $this->json(['success' => true]);
    }





    // Helper pour les réponses JSON avec gestion d’erreurs
    // Helper: exécute un callback et renvoie une réponse JSON (capture exceptions)
    private function jsonResponse(callable $callback): void
    {
        header('Content-Type: application/json');
        try {
            echo json_encode($callback());
        } catch (\Throwable $e) {
            // Distinction erreurs applicatives (Exception) vs erreurs système (Error)
            $appEnv = $GLOBALS['_ENV']['APP_ENV'] ?? ($_ENV['APP_ENV'] ?? 'prod');
            if ($e instanceof \Exception) {
                $status = (int) $e->getCode();
                if ($status < 400 || $status >= 600) {
                    $status = 400; // défaut pour erreur fonctionnelle
                }
                http_response_code($status);
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage(),
                ]);
            } else {
                // \Error, \TypeError ... => masquer le détail en prod
                $msg = $appEnv === 'dev' ? $e->getMessage() : 'Erreur serveur';
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => $msg]);
            }
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
            'photo'     => $user->getPhoto() ?: (defined('DEFAULT_AVATAR_URL') ? DEFAULT_AVATAR_URL : '/assets/images/logo.svg'),
            // Important: conserver le rôle de voyage (passager|chauffeur|les-deux) en session
            'travel_role' => $user->getTravelRole(),
        ];
    }
}
