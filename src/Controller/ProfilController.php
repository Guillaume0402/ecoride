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

        // Charger uniquement les infos utilisateur
        $user = $this->userModel->findById($userId);
        $this->render("pages/creation-profil", [
            'user' => $user
        ]);
    }

    // Met à jour le profil (via formulaire)
    public function update(): void
    {
        error_log('POST reçu dans ProfilController::update');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Méthode non autorisée');
        }

        if (empty($_SESSION['user']['id'])) {
            http_response_code(403);
            exit('Utilisateur non connecté');
        }

        $userId = $_SESSION['user']['id'];

        // Données utilisateur
        $data = [
            'id' => $userId,
            'pseudo' => trim($_POST['pseudo'] ?? ''),
            'role_id' => $_SESSION['user']['role_id'],
            'travel_role' => $_POST['travel_role'] ?? 'passager',
            'photo' => null,
            'password' => !empty($_POST['new_password']) ? $_POST['new_password'] : null
        ];
        error_log('POST reçu dans ProfilController::update');
        // Upload photo
        if (!empty($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $data['photo'] = $this->handlePhotoUpload($_FILES['photo']);
        }

        // Met à jour le profil utilisateur
        $this->userModel->updateProfil($data);

        // Ajout de véhicule UNIQUEMENT si l'utilisateur est chauffeur ou les-deux
        if (in_array($data['travel_role'], ['chauffeur', 'les-deux'])) {
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

            if (!empty($vehicle['immatriculation'])) {
                // Vérifie si l'immatriculation existe déjà chez un autre utilisateur
                if ($this->vehicleModel->existsByImmatriculation($vehicle['immatriculation'], $userId)) {
                    $_SESSION['error'] = "Cette immatriculation est déjà utilisée par un autre utilisateur.";
                    header("Location: /creation-profil");
                    exit;
                }

                if (
                    empty($vehicle['marque']) ||
                    empty($vehicle['modele']) ||
                    empty($vehicle['immatriculation'])
                ) {
                    $_SESSION['error'] = "Merci de remplir tous les champs obligatoires du véhicule.";
                    error_log('Immatriculation déjà utilisée par un autre utilisateur');

                    header("Location: /creation-profil");
                    exit;
                }
                error_log("Véhicule ajouté");
                // Sinon on crée le véhicule
                $this->vehicleModel->create($vehicle);
            }
        }

        // Mise à jour session
        $_SESSION['user'] = $this->userModel->findById($userId)->toArray();
        $_SESSION['success'] = "Profil mis à jour avec succès.";
        header("Location: /my-profil");
        exit;
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
