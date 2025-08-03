<?php

namespace App\Controller;

use App\Entity\UserEntity;

class AdminController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = "Veuillez vous connecter.";
            redirect('/login');
        }

        if ($_SESSION['user']['role_id'] !== 3) {
            abort(403, "Accès interdit");
        }
    }

    // Page d'accueil du tableau de bord administrateur
    public function dashboard(): void
    {
        $this->render("pages/admin/dashboard");
    }

    // Page de statistiques
    public function stats(): void
    {
        $this->render("pages/admin/stats", [
            'isAdminPage' => true
        ]);
    }

    // Page de gestion des utilisateurs et employés
    public function users(): void
    {
        $employees = $this->userRepository->findAllEmployees();
        $users = $this->userRepository->findAllUsers();

        $this->render("pages/admin/users", [
            'employees' => $employees,
            'users' => $users
        ]);
    }

    // Création d'un nouvel employé
    public function createEmployee(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            abort(405, "Méthode non autorisée");
        }

        $pseudo = trim($_POST['pseudo'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirmPassword'] ?? '';

        $errors = [];

        if (empty($pseudo) || empty($email) || empty($password)) {
            $errors[] = "Tous les champs sont obligatoires.";
        }

        if ($password !== $confirm) {
            $errors[] = "Les mots de passe ne correspondent pas.";
        }

        // Vérifier si l’email existe déjà
        if ($this->userRepository->findByEmail($email)) {
            $errors[] = "Un compte avec cet email existe déjà.";
        }

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

        // ✅ Création de l'entité User
        $user = (new UserEntity())
            ->setPseudo($pseudo)
            ->setEmail($email)
            ->setRoleId(2) // employé
            ->setCredits(20);

        // ✅ Hash du mot de passe
        $this->userService->hashPassword($user, $password);

        // ✅ Insertion en DB
        $this->userRepository->create($user);

        $_SESSION['success'] = "Employé ajouté avec succès.";
        redirect('/admin/users');
    }

    // Met à jour le statut d'un employé (actif/inactif)
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

    // Supprime un employé ou utilisateur
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
