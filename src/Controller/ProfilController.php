<?php

namespace App\Controller;

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

        $_SESSION['success'] = "Profil mis à jour avec succès.";

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
}
