<?php

namespace App\Controller;

use App\Entity\UserEntity;

/**
 * Contrôleur d'administration.
 * - Protège l'accès: utilisateur connecté + rôle administrateur (role_id = 3).
 * - Pages: dashboard, statistiques, gestion utilisateurs/employés.
 * - Actions: création d'employé, bascule du statut actif, suppression de compte.
 */
class AdminController extends Controller
{
    // Initialise les dépendances et applique les gardes d'accès (authentification + rôle admin).
     
    public function __construct()
    {
        parent::__construct();

        // Vérifie qu'un utilisateur est authentifié
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = "Veuillez vous connecter.";
            redirect('/login');
        }

        // Vérifie que l'utilisateur a le rôle administrateur (role_id = 3)
        if ($_SESSION['user']['role_id'] !== 3) {
            abort(403, "Accès interdit");
        }
    }

    // Page d'accueil du tableau de bord administrateur.     
    public function dashboard(): void
    {
        $this->render("pages/admin/dashboard");
    }

    // Page de statistiques (expose un indicateur de page admin à la vue).
     
    public function stats(): void
    {
        $this->render("pages/admin/stats", [
            'isAdminPage' => true
        ]);
    }

    /**
     * Page de gestion des utilisateurs et des employés.
     * Récupère les listes et les passe à la vue.     
     */
    public function users(): void
    {
        $employees = $this->userRepository->findAllEmployees();
        $users = $this->userRepository->findAllUsers();

        $this->render("pages/admin/users", [
            'employees' => $employees,
            'users' => $users
        ]);
    }

    /**
     * Création d'un nouvel employé.
     * - Méthode HTTP requise: POST.
     * - Valide le formulaire, hash le mot de passe, persiste en base.
     * - En cas d'erreur, réaffiche la page avec les messages et anciennes valeurs.     
     */
    public function createEmployee(): void
    {
        // Restreint à la méthode POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            abort(405, "Méthode non autorisée");
        }

        // Récupération et normalisation des champs
        $pseudo = trim($_POST['pseudo'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirmPassword'] ?? '';

        $errors = [];

        // Validations simples
        if (empty($pseudo) || empty($email) || empty($password)) {
            $errors[] = "Tous les champs sont obligatoires.";
        }

        if ($password !== $confirm) {
            $errors[] = "Les mots de passe ne correspondent pas.";
        }

        // Unicité de l'email
        if ($this->userRepository->findByEmail($email)) {
            $errors[] = "Un compte avec cet email existe déjà.";
        }

        // Si erreurs: réaffiche la page avec les messages et valeurs saisies
        if (!empty($errors)) {
            $employees = $this->userRepository->findAllEmployees();
            $users = $this->userRepository->findAllUsers();

            $this->render("pages/admin/users", [
                'employees' => $employees,
                'users' => $users,
                'formErrors' => $errors,
                'old' => [
                    'pseudo' => $pseudo,
                    'email' => $email
                ]
            ]);
            return;
        }

        // Création et préparation de l'entité User (employé)
        $user = (new UserEntity())
            ->setPseudo($pseudo)
            ->setEmail($email)
            ->setRoleId(2) // employé
            ->setCredits(20);

        // Hash du mot de passe via le service
        $this->userService->hashPassword($user, $password);

        // Insertion en base de données
        $this->userRepository->create($user);

        // Message de succès + redirection
        $_SESSION['success'] = "Employé ajouté avec succès.";
        redirect('/admin/users');
    }

    /**
     * Bascule le statut actif/inactif d'un employé.
     * - Méthode HTTP requise: POST.
     * @param int $id Identifiant de l'utilisateur à basculer     
     */
    public function toggleEmployeeStatus(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            abort(405, "Méthode non autorisée");
        }

        if ($this->userRepository->toggleActive($id)) {
            $_SESSION['success'] = "Statut de l'employé mis à jour avec succès.";
        } else {
            $_SESSION['error'] = "Impossible de mettre à jour le statut.";
        }

        redirect('/admin/users');
    }

    /**
     * Supprime un employé ou un utilisateur standard.
     * Met à jour l'onglet actif selon le type de compte supprimé.
     * @param int $id Identifiant de l'utilisateur à supprimer    
     */
    public function deleteEmployee(int $id): void
    {
        $user = $this->userRepository->findById($id);

        if ($this->userRepository->delete($id)) {
            $_SESSION['success'] = "Compte supprimé avec succès.";
            $_SESSION['active_tab'] = ($user->getRoleId() === 1) ? 'utilisateurs' : 'employes';
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression.";
        }

        redirect('/admin/users');
    }
}
