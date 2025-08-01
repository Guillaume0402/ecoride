<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Vehicle;
use App\Repository\UserRepository;
use App\Repository\VehicleRepository;

class ProfilController extends Controller
{
    private VehicleRepository $vehicleRepository;

    public function __construct()
    {
        parent::__construct();
        $this->vehicleRepository = new VehicleRepository();

        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = "Vous devez être connecté pour accéder à votre profil.";
            redirect('/login');
        }
    }

    public function showForm(): void
    {
        $userId = $_SESSION['user']['id'];
        $user = $this->userRepository->findById($userId);

        $this->render("pages/creation-profil", [
            'user' => $user ? $this->userService->toArray($user) : null
        ]);
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            abort(405);
        }

        $userId = $_SESSION['user']['id'];
        $user = $this->userRepository->findById($userId);

        if (!$user) {
            $_SESSION['error'] = "Utilisateur introuvable.";
            redirect('/creation-profil');
        }

        $currentTravelRole = $_SESSION['user']['travel_role'] ?? 'passager';
        $newTravelRole = $_POST['travel_role'] ?? $currentTravelRole;

        // Gestion du mot de passe
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';

        if (!empty($newPassword)) {
            if (empty($currentPassword) || !$this->userService->verifyPassword($user, $currentPassword)) {
                $_SESSION['error'] = "Mot de passe actuel incorrect.";
                redirect('/creation-profil');
            }
            $this->userService->hashPassword($user, $newPassword);
        }

        // Mise à jour de l'entité User
        $user->setPseudo(trim($_POST['pseudo'] ?? $user->getPseudo()));
        $user->setTravelRole($newTravelRole);
        $user->setRoleId($_SESSION['user']['role_id']);

        if (!empty($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $user->setPhoto($this->handlePhotoUpload($_FILES['photo']));
        }

        $this->userRepository->update($user);

        // Gestion du véhicule
        $vehicleFieldsEmpty = empty($_POST['immatriculation']) &&
            empty($_POST['marque']) &&
            empty($_POST['modele']) &&
            empty($_POST['couleur']);

        if (in_array($newTravelRole, ['chauffeur', 'les-deux']) && !$vehicleFieldsEmpty) {
            $this->handleVehicleUpdate($userId);
        } else {
            $_SESSION['success'] = "Profil mis à jour avec succès.";
        }

        $updatedUser = $this->userRepository->findById($userId);
        $_SESSION['user'] = $this->userService->toArray($updatedUser);

        redirect('/my-profil');
    }

    private function handlePhotoUpload(array $file): ?string
    {
        $targetDir = PUBLIC_ROOT . '/uploads/';
        $filename = uniqid() . '-' . basename($file['name']);
        $targetFile = $targetDir . $filename;

        return move_uploaded_file($file['tmp_name'], $targetFile) 
            ? '/uploads/' . $filename 
            : null;
    }

    private function handleVehicleUpdate(int $userId): void
    {
        $dateFr = $_POST['date_premiere_immatriculation'] ?? '';
        $dateSql = !empty($dateFr)
            ? \DateTime::createFromFormat('Y-m-d', $dateFr)?->format('Y-m-d')
            : null;

        $vehicleEntity = new Vehicle([
            'user_id' => $userId,
            'marque' => trim($_POST['marque'] ?? ''),
            'modele' => trim($_POST['modele'] ?? ''),
            'couleur' => trim($_POST['couleur'] ?? ''),
            'immatriculation' => trim($_POST['immatriculation'] ?? ''),
            'date_premiere_immatriculation' => $dateSql,
            'fuel_type_id' => $_POST['fuel_type_id'] ?? null,
            'places_dispo' => (int)($_POST['places_dispo'] ?? 0)
        ]);

        if (empty($vehicleEntity->getMarque()) && 
            empty($vehicleEntity->getModele()) && 
            empty($vehicleEntity->getImmatriculation())) {
            return;
        }

        if ($this->vehicleRepository->existsByImmatriculation($vehicleEntity->getImmatriculation(), $userId)) {
            $_SESSION['error'] = "Cette immatriculation est déjà utilisée par un autre utilisateur.";
            redirect('/creation-profil');
        }

        $existingVehicle = $this->vehicleRepository->findByUserId($userId);

        if ($existingVehicle && $existingVehicle->getImmatriculation() === $vehicleEntity->getImmatriculation()) {
            $vehicleEntity->setId($existingVehicle->getId());
            $this->vehicleRepository->update($vehicleEntity);
            $_SESSION['success'] = "Véhicule mis à jour avec succès";
        } else {
            $this->vehicleRepository->create($vehicleEntity);
            $_SESSION['success'] = "Nouveau véhicule ajouté avec succès";
        }
    }
}
