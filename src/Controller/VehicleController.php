<?php

namespace App\Controller;

use App\Model\VehicleModel;

class VehicleController extends Controller
{
    private VehicleModel $vehicleModel;

    public function __construct()
    {
        $this->vehicleModel = new VehicleModel();
    }

    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Méthode non autorisée');
        }

        if (empty($_POST['vehicle_id']) || !is_numeric($_POST['vehicle_id'])) {
            $_SESSION['error'] = "ID de véhicule invalide.";
            header('Location: /my-profil');
            exit;
        }

        $vehicleId = (int) $_POST['vehicle_id'];

        // Optionnel : sécurité → vérifier que le véhicule appartient bien à l'utilisateur
        $vehicle = $this->vehicleModel->findById($vehicleId);
        if (!$vehicle || $vehicle['user_id'] !== $_SESSION['user']['id']) {
            $_SESSION['error'] = "Véhicule introuvable ou non autorisé.";
            header('Location: /my-profil');
            exit;
        }

        $this->vehicleModel->deleteById($vehicleId);

        $_SESSION['success'] = "Véhicule supprimé avec succès.";
        header('Location: /my-profil');
        exit;
    }
    public function edit(): void
    {
        if (empty($_SESSION['user']['id'])) {
            $_SESSION['error'] = "Veuillez vous connecter.";
            header("Location: /login");
            exit;
        }

        $userId = $_SESSION['user']['id'];
        $vehicle = $this->vehicleModel->findByUserId($userId);

        if (!$vehicle) {
            $_SESSION['error'] = "Aucun véhicule à modifier.";
            header("Location: /my-profil");
            exit;
        }

        $this->render("pages/edit-vehicule", [
            'vehicle' => $vehicle
        ]);
    }
    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Méthode non autorisée');
        }

        $userId = $_SESSION['user']['id'];

        // Vérification rapide (optionnel)
        $existing = $this->vehicleModel->findByUserId($userId);
        if (!$existing) {
            $_SESSION['error'] = "Véhicule introuvable.";
            header("Location: pages/edit-vehicule");
            exit;
        }

        $dateFr = $_POST['date_premiere_immatriculation'] ?? '';
        $dateSql = null;
        if (!empty($dateFr)) {
            $dateSql = \DateTime::createFromFormat('Y-m-d', $dateFr)?->format('Y-m-d');
        }

        $vehicle = [
            'user_id' => $userId,
            'marque' => trim($_POST['marque'] ?? ''),
            'modele' => trim($_POST['modele'] ?? ''),
            'couleur' => trim($_POST['couleur'] ?? ''),
            'immatriculation' => trim($_POST['immatriculation'] ?? ''),
            'date_premiere_immatriculation' => $dateSql,
            'fuel_type_id' => $_POST['fuel_type_id'] ?? null,
            'places_dispo' => $_POST['places_dispo'] ?? null,
            'preferences' => $_POST['preferences'] ?? [],
            'custom_preferences' => $_POST['custom_preferences'] ?? ''
        ];

        $this->vehicleModel->insert($vehicle);

        $_SESSION['success'] = "Véhicule mis à jour avec succès.";
        header("Location: /my-profil");
        exit;
    }
}
