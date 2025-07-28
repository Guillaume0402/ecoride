<?php

namespace App\Controller;

use App\Model\UserModel;
use App\Model\VehicleModel;

class UserController extends Controller
{
    private UserModel $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new UserModel();

        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = "Veuillez vous connecter.";
            redirect('/login');
        }
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            abort(405);
        }

        $userId = $_SESSION['user']['id'];
        $role = $_POST['role'] ?? 'passager';

        // 🔒 Empêche la perte du rôle admin
        $roleId = ($_SESSION['user']['role_id'] === 3) 
            ? 3 
            : $this->mapRoleToId($role);

        // Données utilisateur
        $data = [
            'id'       => $userId,
            'pseudo'   => $_POST['pseudo'] ?? '',
            'role_id'  => $roleId,
            'photo'    => null,
            'password' => !empty($_POST['new_password']) ? $_POST['new_password'] : null
        ];

        // 📷 Upload photo
        if (!empty($_FILES['photo']['name'])) {
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/';
            $fileName = uniqid() . '_' . basename($_FILES['photo']['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
                $data['photo'] = '/uploads/' . $fileName;
            }
        }

        // 🚗 Gestion du véhicule si chauffeur ou les deux
        if (in_array($role, ['chauffeur', 'les-deux'])) {
            $vehicleModel = new VehicleModel();

            $vehicleData = [
                'user_id' => $userId,
                'marque' => $_POST['model'] ?? '',
                'modele' => $_POST['model'] ?? '',
                'couleur' => 'N/A',
                'immatriculation' => $_POST['plate'] ?? '',
                'date_premiere_immatriculation' => $_POST['registration_date'] ?? '',
                'fuel_type_id' => $this->mapMotorType($_POST['motor_type'] ?? ''),
                'places_dispo' => $_POST['seats'] ?? 0
            ];

            $existingVehicle = $vehicleModel->findByUserId($userId);

            if ($existingVehicle) {
                $vehicleModel->update($userId, $vehicleData);
            } else {
                $vehicleModel->create($vehicleData);
            }
        }

        try {
            $this->userModel->updateProfil($data);

            // 🔄 Met à jour la session
            $user = $this->userModel->findById($userId);
            if ($user) {
                $_SESSION['user'] = $user->toArray();
            }

            $_SESSION['success'] = "Profil mis à jour avec succès ✅";
        } catch (\PDOException $e) {
            $_SESSION['error'] = "Erreur lors de la mise à jour du profil : " . $e->getMessage();
        }

        redirect('/my-profil');
    }

    private function mapMotorType(string $type): int
    {
        return match ($type) {
            'essence' => 1,
            'diesel' => 2,
            'electrique' => 3,
            'hybride' => 4,
            default => 0,
        };
    }

    private function mapRoleToId(string $role): int
    {
        return match ($role) {
            'passager'  => 1,
            'chauffeur' => 2,
            'les-deux'  => 3,
            default     => 1
        };
    }
}
