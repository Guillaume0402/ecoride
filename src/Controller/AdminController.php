<?php

namespace App\Controller;

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

    // Page de gestion des utilisateurs
    public function users(): void
    {
        $userModel = new \App\Model\UserModel();

        $employees = $userModel->findAllEmployees();
        $users = $userModel->findAllUsers();

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

        $userModel = new \App\Model\UserModel();
        if ($userModel->findByEmail($email)) {
            $errors[] = "Un compte avec cet email existe déjà.";
        }

        if (!empty($errors)) {
            $employees = $userModel->findAllEmployees();
            $users = $userModel->findAllUsers();

            // 🔹 Rendu direct (PAS de redirection)
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

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $user = new \App\Entity\User($email, $hashedPassword);
        $user->setPseudo($pseudo)
            ->setRoleId(2)
            ->setCredits(20);

        $userModel->create($user);

        $_SESSION['success'] = "Employé ajouté avec succès.";
        redirect('/admin/users');
    }



    // Met à jour le statut d'un employé (actif/inactif)
    public function toggleEmployeeStatus(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            abort(405, "Méthode non autorisée");
        }

        $userModel = new \App\Model\UserModel();

        if ($userModel->toggleActive($id)) {
            $_SESSION['success'] = "Statut de l'employé mis à jour avec succès.";
        } else {
            $_SESSION['error'] = "Impossible de mettre à jour le statut.";
        }

        redirect('/admin/users');
    }
    // Supprime un employé
    public function deleteEmployee(int $id): void
    {
        $userModel = new \App\Model\UserModel();

        // 🔹 Vérifie si c’est un utilisateur ou un employé
        $user = $userModel->findById($id);

        if ($userModel->delete($id)) {
            $_SESSION['success'] = "Compte supprimé avec succès.";

            // Définit l’onglet actif
            $_SESSION['active_tab'] = ($user->getRoleId() === 1) ? 'utilisateurs' : 'employes';
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression.";
        }

        redirect('/admin/users');
    }
}
