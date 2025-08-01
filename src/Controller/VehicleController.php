<?php

namespace App\Controller;

use App\Entity\Vehicle;
use App\Repository\VehicleRepository;

class VehicleController extends Controller
{
    private VehicleRepository $vehicleRepository;

    public function __construct()
    {
        parent::__construct();
        $this->vehicleRepository = new VehicleRepository();

        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = "Veuillez vous connecter.";
            redirect('/login');
        }
    }

    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            abort(405);
        }

        $vehicleId = (int) ($_POST['vehicle_id'] ?? 0);
        if ($vehicleId <= 0) {
            $_SESSION['error'] = "ID de véhicule invalide.";
            redirect('/my-profil');
        }

        $vehicle = $this->vehicleRepository->findById($vehicleId);
        if (!$vehicle || $vehicle->getUserId() !== $_SESSION['user']['id']) {
            $_SESSION['error'] = "Véhicule introuvable ou non autorisé.";
            redirect('/my-profil');
        }

        $this->vehicleRepository->deleteById($vehicleId);
        $_SESSION['success'] = "Véhicule supprimé avec succès.";
        redirect('/my-profil');
    }

    public function edit(): void
    {
        $vehicleId = (int) ($_GET['id'] ?? 0);
        $userId = $_SESSION['user']['id'];

        $vehicle = $this->vehicleRepository->findById($vehicleId);

        if (!$vehicle || $vehicle->getUserId() !== $userId) {
            $_SESSION['error'] = "Véhicule introuvable ou non autorisé.";
            redirect('/my-profil');
        }

        $this->render("pages/edit-vehicule", [
            'vehicle' => $vehicle->toArray()
        ]);
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            abort(405);
        }

        $vehicleId = (int) ($_POST['vehicle_id'] ?? 0);
        $userId = $_SESSION['user']['id'];

        $existingVehicle = $this->vehicleRepository->findById($vehicleId);

        if (!$existingVehicle || $existingVehicle->getUserId() !== $userId) {
            $_SESSION['error'] = "Véhicule introuvable ou non autorisé.";
            redirect('/vehicle/edit');
        }

        $immatriculation = trim($_POST['immatriculation'] ?? '');

        if (
            $this->vehicleRepository->existsByImmatriculation($immatriculation, $userId)
            && $existingVehicle->getImmatriculation() !== $immatriculation
        ) {
            $_SESSION['error'] = "Cette immatriculation est déjà utilisée par un autre véhicule.";
            redirect('/vehicle/edit');
        }

        $dateFr = $_POST['date_premiere_immatriculation'] ?? '';
        $dateSql = !empty($dateFr)
            ? \DateTime::createFromFormat('Y-m-d', $dateFr)?->format('Y-m-d')
            : null;

        $vehicle = new Vehicle([
            'id' => $vehicleId,
            'user_id' => $userId,
            'marque' => trim($_POST['marque'] ?? ''),
            'modele' => trim($_POST['modele'] ?? ''),
            'couleur' => trim($_POST['couleur'] ?? ''),
            'immatriculation' => $immatriculation,
            'date_premiere_immatriculation' => $dateSql,
            'fuel_type_id' => $_POST['fuel_type_id'] ?? null,
            'places_dispo' => (int) $_POST['places_dispo']
        ]);

        $this->vehicleRepository->update($vehicle);

        $_SESSION['success'] = "Véhicule mis à jour avec succès.";
        redirect('/my-profil');
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            abort(405);
        }

        $userId = $_SESSION['user']['id'];

        if (empty($_POST['places_dispo']) || !is_numeric($_POST['places_dispo'])) {
            $_SESSION['error'] = "Veuillez sélectionner un nombre de places valide.";
            redirect('/vehicle/create');
        }

        $dateFr = $_POST['date_premiere_immatriculation'] ?? '';
        $dateSql = !empty($dateFr)
            ? \DateTime::createFromFormat('Y-m-d', $dateFr)?->format('Y-m-d')
            : null;

        $vehicle = new Vehicle([
            'user_id' => $userId,
            'marque' => trim($_POST['marque'] ?? ''),
            'modele' => trim($_POST['modele'] ?? ''),
            'couleur' => trim($_POST['couleur'] ?? ''),
            'immatriculation' => trim($_POST['immatriculation'] ?? ''),
            'date_premiere_immatriculation' => $dateSql,
            'fuel_type_id' => $_POST['fuel_type_id'] ?? null,
            'places_dispo' => (int) $_POST['places_dispo']
        ]);

        if ($this->vehicleRepository->existsByImmatriculation($vehicle->getImmatriculation(), 0)) {
            $_SESSION['error'] = "Cette immatriculation est déjà utilisée.";
            redirect('/vehicle/create');
        }

        if ($this->vehicleRepository->create($vehicle)) {
            $_SESSION['success'] = "Véhicule ajouté avec succès.";
            redirect('/my-profil');
        } else {
            $_SESSION['error'] = "Erreur lors de l'ajout du véhicule.";
            redirect('/vehicle/create');
        }
    }
}
