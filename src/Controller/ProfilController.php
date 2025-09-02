<?php

namespace App\Controller;

use App\Repository\VehicleRepository;

/**
 * Contrôleur de gestion du profil utilisateur.
 * - Protège l'accès (nécessite une session utilisateur).
 * - Affiche le formulaire de profil.
 * - Met à jour les informations du profil (pseudo, rôle de voyage, mot de passe, photo).
 */
class ProfilController extends Controller
{
    //Dépôt véhicules (utile si on expose/associe des véhicules au profil).
     
    private VehicleRepository $vehicleRepository;

    //Initialise les dépendances et applique le contrôle d'accès (auth requis).
     
    public function __construct()
    {
        parent::__construct();
        $this->vehicleRepository = new VehicleRepository();

        // Vérifie qu'un utilisateur est connecté, sinon redirige vers la connexion
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = "Vous devez être connecté pour accéder à votre profil.";
            redirect('/login');
        }
    }

    /**
     * Affiche le formulaire d'édition du profil.
     * Charge l'utilisateur courant depuis la base pour disposer des données à jour.    
     */
    public function showForm(): void
    {
        $userId = $_SESSION['user']['id'];
        $user = $this->userRepository->findById($userId);

        $this->render("pages/creation-profil", [
            'user' => $user ? $this->userService->toArray($user) : null
        ]);
    }

    /**
     * Traite la mise à jour du profil utilisateur.
     * Étapes:
     * - Vérifie la méthode HTTP
     * - Récupère l'utilisateur courant
     * - Gère le changement de mot de passe (vérification de l'actuel + hash du nouveau)
     * - Met à jour pseudo, rôle de voyage, photo
     * - Persiste en base puis rafraîchit la session
     * - Redirige vers la page de profil     
     */
    public function update(): void
    {
        // Autorise uniquement POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            abort(405);
        }

        $userId = $_SESSION['user']['id'];
        $user = $this->userRepository->findById($userId);

        // Vérifie l'existence de l'utilisateur
        if (!$user) {
            $_SESSION['error'] = "Utilisateur introuvable.";
            redirect('/creation-profil');
        }

        // Détermine le nouveau rôle de voyage (ou conserve l'actuel)
        $currentTravelRole = $_SESSION['user']['travel_role'] ?? 'passager';
        $newTravelRole = $_POST['travel_role'] ?? $currentTravelRole;

        // Gestion du changement de mot de passe
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';

        if (!empty($newPassword)) {
            // Exige le mot de passe actuel et le vérifie avant de changer
            if (empty($currentPassword) || !$this->userService->verifyPassword($user, $currentPassword)) {
                $_SESSION['error'] = "Mot de passe actuel incorrect.";
                redirect('/creation-profil');
            }
            // Hash du nouveau mot de passe dans l'entité
            $this->userService->hashPassword($user, $newPassword);
        }

        // Mise à jour des champs de l'entité User
        $user->setPseudo(trim($_POST['pseudo'] ?? $user->getPseudo()));
        $user->setTravelRole($newTravelRole);
        $user->setRoleId($_SESSION['user']['role_id']);

        // Gestion de l'upload de photo (si fournie)
        if (!empty($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $user->setPhoto($this->handlePhotoUpload($_FILES['photo']));
        }

        // Persistance des modifications
        $this->userRepository->update($user);

        // Message de succès
        $_SESSION['success'] = "Profil mis à jour avec succès.";

        // Rafraîchit les données en session depuis la base
        $updatedUser = $this->userRepository->findById($userId);
        $_SESSION['user'] = $this->userService->toArray($updatedUser);

        // Redirection vers le profil
        redirect('/my-profil');
    }

    /**
     * Déplace le fichier uploadé dans le répertoire public et retourne son chemin relatif.
     * @param array $file Métadonnées du fichier uploadé (structure $_FILES['...'])
     * @return string|null Chemin web de la ressource ou null si l'upload a échoué
     */
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
