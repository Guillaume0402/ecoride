<?php

namespace App\Controller;

use App\Model\VehicleModel;

class VehicleController extends Controller
{
    private VehicleModel $vehicleModel;

    public function __construct()
    {
        parent::__construct();
        $this->vehicleModel = new VehicleModel();

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

        $vehicle = $this->vehicleModel->findById($vehicleId);
        if (!$vehicle || $vehicle['user_id'] !== $_SESSION['user']['id']) {
            $_SESSION['error'] = "Véhicule introuvable ou non autorisé.";
            redirect('/my-profil');
        }

        $this->vehicleModel->deleteById($vehicleId);
        $_SESSION['success'] = "Véhicule supprimé avec succès.";
        redirect('/my-profil');
    }

    public function edit(): void
    {
        $vehicleId = (int) ($_GET['id'] ?? 0);
        $userId = $_SESSION['user']['id'];

        $vehicle = $this->vehicleModel->findById($vehicleId);

        if (!$vehicle || $vehicle['user_id'] !== $userId) {
            $_SESSION['error'] = "Véhicule introuvable ou non autorisé.";
            redirect('/my-profil');
        }

        $this->render("pages/edit-vehicule", ['vehicle' => $vehicle]);
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            abort(405);
        }

        $vehicleId = (int) ($_POST['vehicle_id'] ?? 0);
        $userId = $_SESSION['user']['id'];

        $existingVehicle = $this->vehicleModel->findById($vehicleId);

        if (!$existingVehicle || $existingVehicle['user_id'] !== $userId) {
            $_SESSION['error'] = "Véhicule introuvable ou non autorisé.";
            redirect('/vehicle/edit');
        }

        $immatriculation = trim($_POST['immatriculation'] ?? '');

        // ✅ Vérifie si l'immatriculation existe déjà pour un autre véhicule
        if (
            $this->vehicleModel->existsByImmatriculation($immatriculation, $userId)
            && $existingVehicle['immatriculation'] !== $immatriculation
        ) {
            $_SESSION['error'] = "Cette immatriculation est déjà utilisée par un autre véhicule.";
            redirect('/vehicle/edit');
        }

        $dateFr = $_POST['date_premiere_immatriculation'] ?? '';
        $dateSql = !empty($dateFr)
            ? \DateTime::createFromFormat('Y-m-d', $dateFr)?->format('Y-m-d')
            : null;

        $vehicle = [
            'marque' => trim($_POST['marque'] ?? ''),
            'modele' => trim($_POST['modele'] ?? ''),
            'couleur' => trim($_POST['couleur'] ?? ''),
            'immatriculation' => $immatriculation,
            'date_premiere_immatriculation' => $dateSql,
            'fuel_type_id' => $_POST['fuel_type_id'] ?? null,
            'places_dispo' => (int) $_POST['places_dispo'],
            'preferences' => $_POST['preferences'] ?? [],
            'custom_preferences' => $_POST['custom_preferences'] ?? ''
        ];

        $this->vehicleModel->update($vehicleId, $vehicle);

        $_SESSION['success'] = "Véhicule mis à jour avec succès.";
        redirect('/my-profil');
    }






    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            abort(405);
        }

        $userId = $_SESSION['user']['id'];

        // ✅ Validation du champ places_dispo
        if (empty($_POST['places_dispo']) || !is_numeric($_POST['places_dispo'])) {
            $_SESSION['error'] = "Veuillez sélectionner un nombre de places valide.";
            redirect('/vehicle/create');
        }

        $dateFr = $_POST['date_premiere_immatriculation'] ?? '';
        $dateSql = !empty($dateFr)
            ? \DateTime::createFromFormat('Y-m-d', $dateFr)?->format('Y-m-d')
            : null;

        $vehicle = [
            'user_id' => $userId,
            'marque' => trim($_POST['marque'] ?? ''),
            'modele' => trim($_POST['modele'] ?? ''),
            'couleur' => trim($_POST['couleur'] ?? ''),
            'immatriculation' => trim($_POST['immatriculation'] ?? ''),
            'date_premiere_immatriculation' => $dateSql,
            'fuel_type_id' => $_POST['fuel_type_id'] ?? null,
            'places_dispo' => (int) $_POST['places_dispo'], // ✅ conversion en INT
            'preferences' => $_POST['preferences'] ?? [],
            'custom_preferences' => $_POST['custom_preferences'] ?? ''
        ];

        if ($this->vehicleModel->existsByImmatriculation($vehicle['immatriculation'], 0)) {
            $_SESSION['error'] = "Cette immatriculation est déjà utilisée.";
            redirect('/vehicle/create');
        }

        if ($this->vehicleModel->create($vehicle)) {
            $_SESSION['success'] = "Véhicule ajouté avec succès.";
            redirect('/my-profil');
        } else {
            $_SESSION['error'] = "Erreur lors de l'ajout du véhicule.";
            redirect('/vehicle/create');
        }
    }
}
