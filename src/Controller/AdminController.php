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
        // Fenêtre configurable (7/15/30 jours)
        $allowedDays = [7, 15, 30];
        $days = (int)($_GET['days'] ?? 15);
        if (!in_array($days, $allowedDays, true)) {
            $days = 15;
        }

        $ridesSeries = $covoitRepo->seriesByDay($days);
        $creditsSeries = $covoitRepo->sumPrixByDay($days);
        $confirmRate = $partRepo->confirmationRateLastDays(30);

        // Sécuriser l'ordre chronologique (clés = dates YYYY-MM-DD)
        if (is_array($ridesSeries)) {
            ksort($ridesSeries);
        }
        if (is_array($creditsSeries)) {
            ksort($creditsSeries);
        }

        // KPIs dérivés (fenêtre choisie)
        $daysCount = max(1, count($ridesSeries ?? []));
        $totalRidesWindow = array_sum($ridesSeries ?? []);
        $avgRidesPerDayWindow = $totalRidesWindow / $daysCount;
        $creditsWindow = array_sum($creditsSeries ?? []);
        $avgCreditsPerDayWindow = $creditsWindow / max(1, count($creditsSeries ?? []));
        $avgCreditsPerRide = $totalRidesWindow > 0 ? ($creditsWindow / $totalRidesWindow) : 0.0;

        // Meilleurs jours (trajets et crédits)
        $bestRideDayDate = null;
        $bestRideDayValue = 0;
        foreach (($ridesSeries ?? []) as $d => $v) {
            if ($v > $bestRideDayValue) {
                $bestRideDayValue = (int)$v;
                $bestRideDayDate = $d;
            }
        }
        $bestCreditDayDate = null;
        $bestCreditDayValue = 0.0;
        foreach (($creditsSeries ?? []) as $d => $v) {
            if ($v > $bestCreditDayValue) {
                $bestCreditDayValue = (float)$v;
                $bestCreditDayDate = $d;
            }
        }

        // Totaux "depuis toujours"
        $totalRidesAll = $covoitRepo->countAll();
        $totalCreditsAll = $covoitRepo->sumPrixAll();

        // Covoiturages aujourd'hui (aperçu rapide)
        $todayRides = $covoitRepo->countToday();

        // Préparation UI (labels JSON pour charts, classes boutons période)
        $labelsR = json_encode(array_keys($ridesSeries ?? []));
        $valuesR = json_encode(array_values($ridesSeries ?? []));
        $labelsC = json_encode(array_keys($creditsSeries ?? []));
        $valuesC = json_encode(array_values($creditsSeries ?? []));
        $btn7  = ($days === 7)  ? 'btn-success' : 'btn-outline-success';
        $btn15 = ($days === 15) ? 'btn-success' : 'btn-outline-success';
        $btn30 = ($days === 30) ? 'btn-success' : 'btn-outline-success';

        // Formats strings pour la vue (évite la logique dans le template)
        $summaryStrings = [
            'todayRides' => (int)$todayRides,
            'totalRidesWindow' => (int)$totalRidesWindow,
            'avgRidesPerDayWindow' => number_format((float)$avgRidesPerDayWindow, 1, ',', ' '),
            'totalCreditsWindow' => number_format((float)$creditsWindow, 0, ',', ' '),
            'avgCreditsPerRide' => number_format((float)$avgCreditsPerRide, 1, ',', ' '),
            'avgCreditsPerDayWindow' => number_format((float)$avgCreditsPerDayWindow, 1, ',', ' '),
            'bestRideDayLabel' => $bestRideDayDate ? date('d/m', strtotime($bestRideDayDate)) : '-',
            'bestRideDayValue' => (int)$bestRideDayValue,
            'bestCreditDayLabel' => $bestCreditDayDate ? date('d/m', strtotime($bestCreditDayDate)) : '-',
            'bestCreditDayValue' => number_format((float)$bestCreditDayValue, 0, ',', ' '),
            'usersCount' => (int)$users,
            'confirmRate' => number_format((float)$confirmRate, 2, ',', ' '),
            'totalRidesAll' => (int)$totalRidesAll,
            'totalCreditsAll' => number_format((float)$totalCreditsAll, 0, ',', ' '),
        ];

        $this->render("pages/admin/stats", [
            'isAdminPage' => true,
            'days' => $days,
            'usersCount' => $users,
            'ridesSeries' => $ridesSeries,
            'creditsSeries' => $creditsSeries,
            'confirmRate' => $confirmRate,
            'ui' => [
                'btn7' => $btn7,
                'btn15' => $btn15,
                'btn30' => $btn30,
                'labelsR' => $labelsR,
                'valuesR' => $valuesR,
                'labelsC' => $labelsC,
                'valuesC' => $valuesC,
            ],
            // Résumé enrichi
            'summary' => [
                'totalRidesWindow' => $totalRidesWindow,
                'avgRidesPerDayWindow' => $avgRidesPerDayWindow,
                'totalCreditsWindow' => $creditsWindow,
                'avgCreditsPerRide' => $avgCreditsPerRide,
                'avgCreditsPerDayWindow' => $avgCreditsPerDayWindow,
                'bestRideDay' => ['date' => $bestRideDayDate, 'value' => $bestRideDayValue],
                'bestCreditDay' => ['date' => $bestCreditDayDate, 'value' => $bestCreditDayValue],
                'todayRides' => $todayRides,
                'totalRidesAll' => $totalRidesAll,
                'totalCreditsAll' => $totalCreditsAll,
            ],
            'summaryStrings' => $summaryStrings,
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

    // Liste globale des covoiturages (passés, en cours, à venir) avec filtre "scope"
    public function covoiturages(): void
    {
        $scope = (string)($_GET['scope'] ?? 'all');
        if (!in_array($scope, ['all','past','ongoing','future'], true)) {
            $scope = 'all';
        }
        $limit = (int)($_GET['limit'] ?? 200);
        $limit = max(1, min(1000, $limit));

        $repo = new \App\Repository\CovoiturageRepository();
        $rows = $repo->findAllAdmin($scope, $limit);

        $this->render('pages/admin/covoiturages', [
            'isAdminPage' => true,
            'scope' => $scope,
            'limit' => $limit,
            'rows' => $rows,
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
