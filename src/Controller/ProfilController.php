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
        $this->userModel = new UserModel();
        $this->vehicleModel = new VehicleModel();
    }

    // Affiche le formulaire prérempli
    public function showForm(): void
    {
        if (empty($_SESSION['user'])) {
            $_SESSION['error'] = "Vous devez être connecté pour accéder à votre profil.";
            header("Location: /login");
            exit;
        }

        $userId = $_SESSION['user']['id'];

        $user = $this->userModel->findById($userId);
        $vehicle = $this->vehicleModel->findByUserId($userId);

        $this->render("pages/creation-profil", [
            'user' => $user?->toArray() ?? [],
            'vehicle' => $vehicle
        ]);
    }

    // Met à jour le profil (via formulaire)
    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Méthode non autorisée');
        }

        if (empty($_SESSION['user']['id'])) {
            http_response_code(403);
            exit('Utilisateur non connecté');
        }

        $userId = $_SESSION['user']['id'];
        $role = $_POST['role'] ?? 'passager';



        // Récupération des données
        $data = [
            'id' => $userId,
            'pseudo' => trim($_POST['pseudo'] ?? ''),
            'role_id' => $this->mapRoleToId($role),
            'photo' => null,
            'password' => !empty($_POST['new_password']) ? $_POST['new_password'] : null
        ];

        // Gestion upload photo
        if (!empty($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $data['photo'] = $this->handlePhotoUpload($_FILES['photo']);
        }

        // Mise à jour utilisateur
        $this->userModel->updateProfil($data);


        // Mise à jour véhicule si chauffeur
        if (in_array($role, ['chauffeur', 'les-deux'])) {
            // Formatage correct de la date d'immatriculation
            $dateFr = $_POST['date_premiere_immatriculation'] ?? '';
            $dateSql = null;

            if (!empty($dateFr)) {
                $dateObj = \DateTime::createFromFormat('Y-m-d', $dateFr); // ✅ ou 'd/m/Y' selon ton <input>
                if ($dateObj && $dateObj->format('Y-m-d') === $dateFr) {
                    $dateSql = $dateObj->format('Y-m-d');
                } else {
                    $_SESSION['error'] = "La date est invalide. Format attendu : AAAA-MM-JJ.";
                    header("Location: /creation-profil");
                    exit;
                }
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

            $existingVehicle = $this->vehicleModel->findByUserId($userId);
            if ($existingVehicle) {
                $this->vehicleModel->update($userId, $vehicle);
            } else {
                $this->vehicleModel->create($vehicle);
            }
        }

        // Met à jour la session
        $_SESSION['user'] = $this->userModel->findById($userId)->toArray();

        $_SESSION['success'] = "Profil mis à jour avec succès.";
        header("Location: /my-profil");
        exit;
    }

    private function mapRoleToId(string $role): int
    {
        return match ($role) {
            'passager' => 1,
            'chauffeur' => 2,
            'les-deux' => 3,
            default => 1
        };
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
}
