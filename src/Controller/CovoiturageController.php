<?php

namespace App\Controller;

use App\Repository\VehicleRepository;
use App\Repository\CovoiturageRepository;
use App\Entity\CovoiturageEntity;
use App\Security\Csrf;
use App\Service\Flash;

class CovoiturageController extends Controller
{
    private VehicleRepository $vehicleRepository;
    private CovoiturageRepository $covoiturageRepository;

    public function __construct()
    {
        parent::__construct();
        $this->vehicleRepository = new VehicleRepository();
        $this->covoiturageRepository = new CovoiturageRepository();
    }

    // POST /covoiturages/create (soumission classique)
    public function create(): void
    {
        if (!isset($_SESSION['user'])) {
            Flash::add('Veuillez vous connecter.', 'danger');
            redirect('/login');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            abort(405);
        }

        if (!Csrf::check($_POST['csrf'] ?? null)) {
            Flash::add('Requête invalide (CSRF).', 'danger');
            redirect('/');
        }

        $userId = (int) $_SESSION['user']['id'];
        $vehicleId = (int) ($_POST['vehicle_id'] ?? 0);
        $villeDepart = trim($_POST['ville_depart'] ?? '');
        $villeArrivee = trim($_POST['ville_arrivee'] ?? '');
        $date = trim($_POST['date'] ?? '');
        $time = trim($_POST['time'] ?? '');
        $prixRaw = $_POST['prix'] ?? '';
        $prix = is_numeric($prixRaw) ? (float) $prixRaw : -1;

        if ($vehicleId <= 0 || $villeDepart === '' || $villeArrivee === '' || $date === '' || $time === '' || $prix < 0) {
            Flash::add('Champs requis manquants ou invalides.', 'danger');
            redirect('/');
        }

        $vehicle = $this->vehicleRepository->findById($vehicleId);
        if (!$vehicle || $vehicle->getUserId() !== $userId) {
            Flash::add('Véhicule introuvable ou non autorisé.', 'danger');
            redirect('/');
        }

        $departDt = \DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $time);
        if (!$departDt) {
            Flash::add('Date/heure invalides.', 'danger');
            redirect('/');
        }
        $arriveeDt = (clone $departDt)->modify('+1 hour');

        $c = new CovoiturageEntity([
            'driver_id' => $userId,
            'vehicle_id' => $vehicleId,
            'adresse_depart' => $villeDepart,
            'adresse_arrivee' => $villeArrivee,
            'depart' => $departDt->format('Y-m-d H:i:s'),
            'arrivee' => $arriveeDt->format('Y-m-d H:i:s'),
            'prix' => $prix,
            'status' => 'en_attente',
        ]);

        if ($this->covoiturageRepository->create($c)) {
            Flash::add('Covoiturage créé avec succès.', 'success');
            redirect('/liste-covoiturages');
        }

        Flash::add("Erreur lors de la création du covoiturage.", 'danger');
        redirect('/');
    }
    // POST /api/covoiturages/create
    public function apiCreate(): void
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Authentification requise']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }

        if (!Csrf::check($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Requête invalide (CSRF).']);
            return;
        }

        $userId = (int) $_SESSION['user']['id'];
        $vehicleId = (int) ($_POST['vehicle_id'] ?? 0);
        $villeDepart = trim($_POST['ville_depart'] ?? '');
        $villeArrivee = trim($_POST['ville_arrivee'] ?? '');
        $date = trim($_POST['date'] ?? '');
        $time = trim($_POST['time'] ?? '');
        $prix = (float) ($_POST['prix'] ?? 0);

        // Validations simples
        if ($vehicleId <= 0 || $villeDepart === '' || $villeArrivee === '' || $date === '' || $time === '' || $prix < 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Champs requis manquants ou invalides.']);
            return;
        }

        $vehicle = $this->vehicleRepository->findById($vehicleId);
        if (!$vehicle || $vehicle->getUserId() !== $userId) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Véhicule introuvable ou non autorisé.']);
            return;
        }

        // Assemble date/heure
        $departDt = \DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $time);
        if (!$departDt) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Date/heure invalides.']);
            return;
        }

        // Pas d’heure d’arrivée côté UI pour le moment, on fixe +1h simple
        $arriveeDt = (clone $departDt)->modify('+1 hour');

        $c = new CovoiturageEntity([
            'driver_id' => $userId,
            'vehicle_id' => $vehicleId,
            'adresse_depart' => $villeDepart,
            'adresse_arrivee' => $villeArrivee,
            'depart' => $departDt->format('Y-m-d H:i:s'),
            'arrivee' => $arriveeDt->format('Y-m-d H:i:s'),
            'prix' => $prix,
            'status' => 'en_attente',
        ]);

        if ($this->covoiturageRepository->create($c)) {
            echo json_encode(['success' => true, 'message' => 'Covoiturage créé.', 'id' => $c->getId()]);
            return;
        }

        http_response_code(500);
        echo json_encode(['success' => false, 'message' => "Erreur lors de la création."]);
    }
}
