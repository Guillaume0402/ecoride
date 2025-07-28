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

        $currentTravelRole = $_SESSION['user']['travel_role'] ?? 'passager';

        $data = [
            'id'           => $userId,
            'pseudo'       => trim($_POST['pseudo'] ?? ''),
            'role_id'      => $_SESSION['user']['role_id'],
            'travel_role'  => !empty($_POST['travel_role']) ? $_POST['travel_role'] : $currentTravelRole,
            'photo'        => null,
            'password'     => !empty($_POST['new_password']) ? $_POST['new_password'] : null
        ];

        if (!empty($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $data['photo'] = $this->handlePhotoUpload($_FILES['photo']);
        }

        $this->userModel->updateProfil($data);

        if (in_array($data['travel_role'], ['chauffeur', 'les-deux'])) {
            $this->handleVehicleUpdate($userId);
        }

        $_SESSION['user'] = $this->userModel->findById($userId)->toArray();
        $_SESSION['success'] = "Profil mis à jour avec succès.";
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
    // Conversion de la date
    $dateSql = null;
    $dateFr = $_POST['date_premiere_immatriculation'] ?? '';
    if (!empty($dateFr)) {
        $dateSql = \DateTime::createFromFormat('Y-m-d', $dateFr)?->format('Y-m-d');
    }

    // Préparation des données
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

    // Vérification des champs obligatoires
    $requiredFieldsFilled = !empty($vehicle['marque']) &&
                            !empty($vehicle['modele']) &&
                            !empty($vehicle['immatriculation']);

    if (!$requiredFieldsFilled) {
        return; // ❌ On sort si le formulaire véhicule n'est pas rempli
    }

    // Vérification de l'immatriculation unique
    if ($this->vehicleModel->existsByImmatriculation($vehicle['immatriculation'], $userId)) {
        $_SESSION['error'] = "Cette immatriculation est déjà utilisée par un autre utilisateur.";
        redirect('/creation-profil');
    }

    // Création du véhicule
    $this->vehicleModel->create($vehicle);
}

}
