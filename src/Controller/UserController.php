<?php

namespace App\Controller;

use App\Model\UserModel;
use App\Model\VehicleModel;
use App\Entity\Vehicle;

class UserController extends Controller
{
    private UserModel $userModel;

    public function __construct()
    {

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }


        $this->userModel = new UserModel();
    }

    // Traitement du formulaire d'ajout/mise à jour de profil
    public function update(): void
    {
        $userId = $_SESSION['user']['id'];
        $role = $_POST['role'] ?? 'passager';

        // Sécurité : un admin garde son rôle admin même s’il modifie son profil
        if ($_SESSION['user']['role_id'] === 3) {
            $roleId = 3; // on force le rôle admin
        } else {
            $roleId = $this->mapRoleToId($role);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Méthode non autorisée');
        }

        if (empty($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
            http_response_code(403);
            exit('Utilisateur non connecté');
        }

        // Données du formulaire
        $data = [
            'id'           => $userId,
            'pseudo'       => $_POST['pseudo'] ?? '',
            'role_id'      => $roleId,
            'photo'        => null,
            'password'     => !empty($_POST['new_password']) ? $_POST['new_password'] : null
        ];


        // Photo
        if (!empty($_FILES['photo']['name'])) {
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/';
            $fileName = uniqid() . '_' . basename($_FILES['photo']['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
                $data['photo'] = '/uploads/' . $fileName;
            }
        }


        if (in_array($role, ['chauffeur', 'les-deux'])) {
            $vehicleModel = new VehicleModel();

            // Regroupe les données du véhicule
            $vehicleData = [
                'user_id' => $userId,
                'marque' => $_POST['model'] ?? '',
                'modele' => $_POST['model'] ?? '',
                'couleur' => 'N/A', // à adapter si tu as le champ
                'immatriculation' => $_POST['plate'] ?? '',
                'date_premiere_immatriculation' => $_POST['registration_date'] ?? '',
                'fuel_type_id' => $this->mapMotorType($_POST['motor_type'] ?? ''),
                'places_dispo' => $_POST['seats'] ?? 0
            ];

            // Vérifie si un véhicule existe déjà pour l'utilisateur
            $existingVehicle = $vehicleModel->findByUserId($userId);

            if ($existingVehicle) {
                $vehicleModel->update($userId, $vehicleData);
            } else {
                $vehicleModel->create($vehicleData);
            }
        }

        try {
            // Mise à jour
            $this->userModel->updateProfil($data);

            // Recharge les données en session
            $user = $this->userModel->findById($userId);
            if ($user) {
                $_SESSION['user'] = $user->toArray();
            }

            $_SESSION['success'] = "Profil mis à jour avec succès ✅";
        } catch (\PDOException $e) {
            $_SESSION['error'] = "Erreur lors de la mise à jour du profil : " . $e->getMessage();
        }

        header("Location: /my-profil");
        exit;
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
            'passager'     => 1,
            'chauffeur'    => 2,
            'les-deux'     => 3,
            default        => 1
        };
    }
}
