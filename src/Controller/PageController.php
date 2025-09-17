<?php

namespace App\Controller;

use App\Repository\VehicleRepository;
use App\Repository\CovoiturageRepository;

// Contrôleur des pages (publiques/protégées): statiques, covoiturages, profil
class PageController extends Controller
{
    // Dépôt pour interagir avec les véhicules des utilisateurs.
    private VehicleRepository $vehicleRepository;

    // Initialisation des dépendances nécessaires aux pages.
    public function __construct()
    {
        parent::__construct();
        $this->vehicleRepository = new VehicleRepository();
    }

    // Page d'accueil.
    public function home(): void
    {
        $this->render('home');
    }

    // Page de contact.
    public function contact(): void
    {
        $this->render('pages/contact');
    }

    // Liste des covoiturages (vue listant les annonces).
    public function listeCovoiturages(): void
    {
        // Lecture des critères GET (simples)
        $depart = isset($_GET['depart']) ? trim((string)$_GET['depart']) : null;
        $arrivee = isset($_GET['arrivee']) ? trim((string)$_GET['arrivee']) : null;
        $date = isset($_GET['date']) ? trim((string)$_GET['date']) : null; // format YYYY-MM-DD
        // Pref peut être string (ancienne UI) ou tableau (multi sélection)
        $prefParam = $_GET['pref'] ?? null;
        $prefs = [];
        if (is_array($prefParam)) {
            $prefs = array_values(array_filter(array_map('strval', $prefParam)));
        } elseif (is_string($prefParam) && $prefParam !== '') {
            $prefs = [$prefParam];
        }
        $sort = isset($_GET['sort']) ? trim((string)$_GET['sort']) : null; // 'date' | 'price'
        $dir  = isset($_GET['dir'])  ? trim((string)$_GET['dir'])  : null; // 'asc' | 'desc'

        $results = [];
        try {
            $repo = new CovoiturageRepository();
            // Toujours effectuer la recherche pour permettre le tri même sans filtres
            $currentUserId = isset($_SESSION['user']) ? (int) $_SESSION['user']['id'] : null;
            $results = $repo->search($depart, $arrivee, $date, $prefs, $sort, $dir, $currentUserId);
        } catch (\Throwable $e) {
            error_log('Search error: ' . $e->getMessage());
        }

        $this->render('pages/liste-covoiturages', [
            'criteria' => [
                'depart' => $depart,
                'arrivee' => $arrivee,
                'date' => $date,
                'pref' => $prefs,
                'sort' => $sort,
                'dir' => $dir,
            ],
            'results' => $results,
        ]);
    }

    // Page de création d'un covoiturage.
    public function creationCovoiturage(): void
    {
        $this->render('pages/creation-covoiturage');
    }

    // Page de création/édition du profil (protégée), précharge le véhicule
    public function creationProfil(): void
    {
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = 'Vous devez être connecté pour accéder à cette page.';
            redirect('/login');
        }

        $user = $_SESSION['user'];

        $vehicleEntity = !empty($_GET['id'])
            ? $this->vehicleRepository->findById((int) $_GET['id'])
            : $this->vehicleRepository->findByUserId($user['id']);

        $vehicle = $vehicleEntity ? $vehicleEntity->toArray() : null;

        $this->render('pages/creation-profil', [
            'user' => $user,
            'vehicle' => $vehicle,
        ]);
    }

    // Page listant les covoiturages de l'utilisateur courant.
    public function mesCovoiturages(): void
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
        }
        // Balayage léger de maintenance (annulations auto + rattrapage remboursements)
        try {
            (new \App\Service\MaintenanceService())->sweep();
        } catch (\Throwable $e) {
            error_log('[mesCovoiturages maintenance sweep] ' . $e->getMessage());
        }
        $userId = (int) $_SESSION['user']['id'];
        $covoitRepo = new CovoiturageRepository();
        $partRepo = new \App\Repository\ParticipationRepository();

        $asDriverAll = $covoitRepo->findByDriverId($userId);
        $asPassengerAll = $partRepo->findByPassagerId($userId);

        // Filtrage (Option B): aligner avec le header
        $now = new \DateTime();
        $asDriver = array_values(array_filter($asDriverAll, function ($c) use ($now) {
            try {
                $depart = new \DateTime($c['depart']);
            } catch (\Throwable $e) {
                return false;
            }
            $status = (string)($c['status'] ?? 'en_attente');
            return $depart >= $now && !in_array($status, ['annule', 'termine'], true);
        }));
        $asPassenger = array_values(array_filter($asPassengerAll, function ($p) use ($now) {
            $status = (string)($p['status'] ?? '');
            $cStatus = (string)($p['covoit_status'] ?? 'en_attente');
            $isUpcoming = false;
            try {
                $isUpcoming = (new \DateTime($p['depart'])) >= $now;
            } catch (\Throwable $e) {
            }
            // Montrer dans l'onglet Passager:
            // - demandes en attente de validation
            // - participations confirmées
            // uniquement pour des trajets à venir et non annulés/terminés
            return in_array($status, ['en_attente_validation', 'confirmee'], true)
                && $isUpcoming
                && !in_array($cStatus, ['annule', 'termine'], true);
        }));

        // Historique
        $historyDriver = array_values(array_filter($asDriverAll, function ($c) use ($now) {
            try {
                $depart = new \DateTime($c['depart']);
            } catch (\Throwable $e) {
                return false;
            }
            $status = (string)($c['status'] ?? 'en_attente');
            return $depart < $now || in_array($status, ['annule', 'termine'], true);
        }));
        $historyPassenger = array_values(array_filter($asPassengerAll, function ($p) use ($now) {
            $status = (string)($p['status'] ?? '');
            $cStatus = (string)($p['covoit_status'] ?? 'en_attente');
            $isPast = false;
            try {
                $isPast = (new \DateTime($p['depart'])) < $now;
            } catch (\Throwable $e) {
            }
            // Classer en historique si:
            // - participation annulée ou autre statut non actif
            // - trajet annulé/terminé
            // - trajet passé (même si la participation était en attente ou confirmée)
            $isActiveParticipation = in_array($status, ['en_attente_validation', 'confirmee'], true);
            $isActiveRide = !in_array($cStatus, ['annule', 'termine'], true);
            return !$isActiveParticipation || !$isActiveRide || $isPast;
        }));

        // Compte des validations en attente (participations confirmées dont le covoit est terminé mais non encore validées côté passager)
        $pendingValidations = 0;
        $txRepo = new \App\Repository\TransactionRepository();
        foreach ($asPassengerAll as $p) {
            if (($p['status'] ?? '') === 'confirmee' && ($p['covoit_status'] ?? '') === 'termine') {
                $driverId = (int)($p['driver_user_id'] ?? 0);
                $covoiturageId = (int)($p['covoiturage_id'] ?? 0);
                $motif = 'Crédit conducteur trajet #' . $covoiturageId . ' - passager #' . $userId;
                if (!$txRepo->existsForMotif($driverId, $motif)) {
                    $pendingValidations++;
                }
            }
        }

        $this->render('pages/mes-covoiturages', [
            'asDriver' => $asDriver,
            'asPassenger' => $asPassenger,
            'historyDriver' => $historyDriver,
            'historyPassenger' => $historyPassenger,
            'pendingValidations' => $pendingValidations,
        ]);
    }

    // Page profil (protégée), charge les véhicules de l'utilisateur
    public function profil(): void
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
        }

        $user = $_SESSION['user'];
        $vehicles = $this->vehicleRepository->findAllByUserId($user['id']);

        $this->render('pages/my-profil', [
            'user' => $user,
            'vehicles' => $vehicles,
        ]);
    }

    // Page dédiée aux crédits: liste des transactions récentes
    public function mesCredits(): void
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
        }

        $user = $_SESSION['user'];
        $transactions = [];
        try {
            $txRepo = new \App\Repository\TransactionRepository();
            $transactions = $txRepo->findByUserId((int)$user['id'], 50);
        } catch (\Throwable $e) {
            error_log('[mesCredits] transactions load failed: ' . $e->getMessage());
        }

        $this->render('pages/mes-credits', [
            'user' => $user,
            'transactions' => $transactions,
        ]);
    }

    // Page de connexion.
    public function login(): void
    {
        $this->render('pages/login');
    }

    // Page "À propos".
    public function about(): void
    {
        $this->render('pages/about');
    }

    // Page des conditions d'utilisation.
    public function terms(): void
    {
        $this->render('pages/terms');
    }

    // Page de politique de confidentialité.
    public function privacy(): void
    {
        $this->render('pages/privacy');
    }

    // Détail d'un covoiturage (public)
    public function showCovoiturage(int $id): void
    {
        $repo = new CovoiturageRepository();
        $ride = $repo->findOneWithVehicleById($id);
        if (!$ride) {
            abort(404, 'Covoiturage introuvable');
        }
        $this->render('pages/covoiturages/show', [
            'ride' => $ride,
        ]);
    }
}
