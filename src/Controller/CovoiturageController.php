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
        $timeArrivee = trim($_POST['time_arrivee'] ?? '');
        $prixRaw = $_POST['prix'] ?? '';
        $places = filter_input(INPUT_POST, 'places', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 9]]);
        $prix = is_numeric($prixRaw) ? (float) $prixRaw : -1;

        if ($vehicleId <= 0 || $villeDepart === '' || $villeArrivee === '' || $date === '' || $time === '' || $timeArrivee === '' || $prix < 0 || $places === false) {
            Flash::add('Champs requis manquants ou invalides.', 'danger');
            redirect('/');
        }

        $vehicle = $this->vehicleRepository->findById($vehicleId);
        if (!$vehicle || $vehicle->getUserId() !== $userId) {
            Flash::add('Véhicule introuvable ou non autorisé.', 'danger');
            redirect('/');
        }
        if ($places > $vehicle->getPlacesDispo()) {
            Flash::add("Le nombre de places demandées dépasse la capacité du véhicule.", 'danger');
            redirect('/');
        }

        $departDt = \DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $time);
        if (!$departDt) {
            Flash::add('Date/heure invalides.', 'danger');
            redirect('/');
        }
        // Interdit une date de départ passée (tolère la minute courante)
        $now = new \DateTime('now');
        if ($departDt < $now) {
            Flash::add('La date/heure de départ ne peut pas être dans le passé.', 'danger');
            redirect('/');
        }

        $arriveeDt = \DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $timeArrivee);
        if (!$arriveeDt || $arriveeDt <= $departDt) {
            Flash::add("L'heure d'arrivée doit être postérieure à l'heure de départ.", 'danger');
            redirect('/');
        }

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
        $timeArrivee = trim($_POST['time_arrivee'] ?? '');
        $places = filter_input(INPUT_POST, 'places', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 9]]);

        // Validations simples
        if ($vehicleId <= 0 || $villeDepart === '' || $villeArrivee === '' || $date === '' || $time === '' || $timeArrivee === '' || $prix < 0 || $places === false) {
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
        if ($places > $vehicle->getPlacesDispo()) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Le nombre de places demandées dépasse la capacité du véhicule."]);
            return;
        }

        // Assemble date/heure
        $departDt = \DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $time);
        if (!$departDt) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Date/heure invalides.']);
            return;
        }
        $now = new \DateTime('now');
        if ($departDt < $now) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "La date/heure de départ ne peut pas être dans le passé."]);
            return;
        }

        $arriveeDt = \DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $timeArrivee);
        if (!$arriveeDt || $arriveeDt <= $departDt) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "L'heure d'arrivée doit être postérieure à l'heure de départ."]);
            return;
        }

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

    // POST /covoiturages/cancel/{id}
    public function cancel(int $id): void
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
        }
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            abort(405);
        }
        if (!Csrf::check($_POST['csrf'] ?? null)) {
            Flash::add('Requête invalide (CSRF).', 'danger');
            redirect('/mes-covoiturages');
        }

        $userId = (int) $_SESSION['user']['id'];
        $ride = $this->covoiturageRepository->findOneWithVehicleById($id);
        if (!$ride) {
            Flash::add('Trajet introuvable.', 'danger');
            redirect('/mes-covoiturages');
        }
        // Autorisation: seul le conducteur peut annuler
        if ((int)$ride['driver_id'] !== $userId) {
            Flash::add('Action non autorisée.', 'danger');
            redirect('/mes-covoiturages');
        }
        // Interdire si déjà terminé/annulé
        if (in_array(($ride['status'] ?? ''), ['annule', 'termine'], true)) {
            Flash::add('Trajet déjà clôturé.', 'warning');
            redirect('/mes-covoiturages');
        }

        // Annule le covoiturage et les participations associées
        try {
            $pdo = \App\Db\Mysql::getInstance()->getPDO();
            $pdo->beginTransaction();
            // annuler le covoiturage
            $stmt = $pdo->prepare("UPDATE covoiturages SET status='annule' WHERE id=:id");
            $stmt->execute([':id' => $id]);
            // marquer toutes les participations comme annulées
            $stmt2 = $pdo->prepare("UPDATE participations SET status='annulee' WHERE covoiturage_id=:id AND status <> 'annulee'");
            $stmt2->execute([':id' => $id]);
            $pdo->commit();
            Flash::add('Trajet annulé. Les passagers ont été prévenus.', 'success');
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('[cancel covoit] ' . $e->getMessage());
            Flash::add('Erreur lors de l\'annulation.', 'danger');
        }

        redirect('/mes-covoiturages');
    }
}
