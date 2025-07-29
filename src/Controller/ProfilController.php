<?php

namespace App\Controller;

use App\Model\UserModel;
use App\Model\VehicleModel;

class ProfilController extends Controller
{
    private UserModel $userModel;
    private VehicleModel $vehicleModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new UserModel();
        $this->vehicleModel = new VehicleModel();

        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = "Vous devez être connecté pour accéder à votre profil.";
            redirect('/login');
        }
    }

    public function showForm(): void
    {
        $userId = $_SESSION['user']['id'];
        $user = $this->userModel->findById($userId);
        $userData = $user ? $user->toArray() : null;

        $this->render("pages/creation-profil", [
            'user' => $userData
        ]);
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            abort(405);
        }

        $userId = $_SESSION['user']['id'];
        $user = $this->userModel->findById($userId);

        //  On conserve le rôle actuel si aucun nouveau choisi
        $currentTravelRole = $_SESSION['user']['travel_role'] ?? 'passager';
        $newTravelRole = $_POST['travel_role'] ?? $currentTravelRole;

        // Gestion mot de passe
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $hashedPassword = null;

        if (!empty($newPassword)) {
            if (empty($currentPassword) || !password_verify($currentPassword, $user->getPassword())) {
                $_SESSION['error'] = "Mot de passe actuel incorrect.";
                redirect('/creation-profil');
            }
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        }

        // 🔹 Données à mettre à jour
        $data = [
            'id'          => $userId,
            'pseudo'      => trim($_POST['pseudo'] ?? $user->getPseudo()),
            'role_id'     => $_SESSION['user']['role_id'],
            'travel_role' => $newTravelRole,
            'photo'       => null,
            'password'    => $hashedPassword
        ];

        // 📸 Upload photo
        if (!empty($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $data['photo'] = $this->handlePhotoUpload($_FILES['photo']);
        }

        $this->userModel->updateProfil($data);

        // Détection de saisie véhicule
        $vehicleFieldsEmpty = empty($_POST['immatriculation']) &&
            empty($_POST['marque']) &&
            empty($_POST['modele']) &&
            empty($_POST['couleur']);

        if (
            in_array($data['travel_role'], ['chauffeur', 'les-deux']) &&
            !$vehicleFieldsEmpty
        ) {
            $this->handleVehicleUpdate($userId);
        } else {
            $_SESSION['success'] = "Profil mis à jour avec succès TOP ";
        }

        $_SESSION['user'] = $this->userModel->findById($userId)->toArray();
        redirect('/my-profil');
    }

    private function handlePhotoUpload(array $file): ?string
    {
        $targetDir = PUBLIC_ROOT . '/uploads/';
        $filename = uniqid() . '-' . basename($file['name']);
        $targetFile = $targetDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
            return null;
        }

        return '/uploads/' . $filename;
    }

    private function handleVehicleUpdate(int $userId): void
    {
        $dateSql = null;
        $dateFr = $_POST['date_premiere_immatriculation'] ?? '';
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

        if (empty($vehicle['marque']) && empty($vehicle['modele']) && empty($vehicle['immatriculation'])) {
            return;
        }

        if ($this->vehicleModel->existsByImmatriculation($vehicle['immatriculation'], $userId)) {
            $_SESSION['error'] = "Cette immatriculation est déjà utilisée par un autre utilisateur.";
            redirect('/creation-profil');
        }

        $existingVehicle = $this->vehicleModel->findByUserId($userId);

        // Si un véhicule existe ET que l'immatriculation envoyée = immatriculation existante → Update
        if ($existingVehicle && $existingVehicle['immatriculation'] === $vehicle['immatriculation']) {
            $this->vehicleModel->update($existingVehicle['id'], $vehicle);
            $_SESSION['success'] = "Véhicule mis à jour avec succès";
        } else {
            // Sinon → Ajout d'un nouveau véhicule
            $this->vehicleModel->create($vehicle);
            $_SESSION['success'] = "Nouveau véhicule ajouté avec succès";
        }
    }
}
