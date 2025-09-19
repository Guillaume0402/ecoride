<?php

namespace App\Controller;

use App\Entity\UserEntity;

// Contrôleur admin: gardes d'accès + dashboard, stats, gestion comptes
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
        // KPIs
        $userCount = $this->userRepository->countAll();
        $covoitRepo = new \App\Repository\CovoiturageRepository();
        $partRepo = new \App\Repository\ParticipationRepository();
        $todayRides = $covoitRepo->countToday();
        $totalCreditsEstime = array_sum($covoitRepo->sumPrixByDay(30));

        $this->render("pages/admin/dashboard", [
            'kpi_users' => $userCount,
            'kpi_rides_today' => $todayRides,
            'kpi_credits_30d' => $totalCreditsEstime,
        ]);
    }

    // Page de statistiques (expose un indicateur de page admin à la vue).

    public function stats(): void
    {
        $covoitRepo = new \App\Repository\CovoiturageRepository();
        $partRepo = new \App\Repository\ParticipationRepository();
        $users = $this->userRepository->countAll();
        $ridesSeries = $covoitRepo->seriesByDay(14);
        $creditsSeries = $covoitRepo->sumPrixByDay(14);
        $confirmRate = $partRepo->confirmationRateLastDays(30);

        $this->render("pages/admin/stats", [
            'isAdminPage' => true,
            'usersCount' => $users,
            'ridesSeries' => $ridesSeries,
            'creditsSeries' => $creditsSeries,
            'confirmRate' => $confirmRate,
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

    // Crée un employé (POST), validations + hash + persistance
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

    // Bascule le statut actif/inactif (POST)
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

    // Supprime un compte et positionne l'onglet actif selon le rôle
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
