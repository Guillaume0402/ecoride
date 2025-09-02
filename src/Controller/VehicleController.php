<?php

namespace App\Controller;

use App\Entity\VehicleEntity;
use App\Repository\VehicleRepository;

/**
 * Contrôleur de gestion des véhicules.
 * - Protège l'accès: nécessite un utilisateur connecté.
 * - CRUD véhicule: création, lecture (form d'édition), mise à jour, suppression.
 */
class VehicleController extends Controller
{
    // Dépôt d'accès aux données véhicules.     
    private VehicleRepository $vehicleRepository;

    // Initialise le repository et applique le contrôle d'accès (auth requis).     
    public function __construct()
    {
        parent::__construct();
        $this->vehicleRepository = new VehicleRepository();

        // Si l'utilisateur n'est pas connecté, on redirige vers la page de login
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = "Veuillez vous connecter.";
            redirect('/login');
        }
    }

    // Affiche le formulaire de création d'un véhicule (vide).     
    public function create(): void
    {
        // Affiche le formulaire vide pour ajouter un véhicule
        $this->render("pages/form-vehicule", [
            'vehicle' => []
        ]);
    }

    /**
     * Traite la soumission de création d'un véhicule.
     * - Autorise uniquement POST
     * - Valide le nombre de places
     * - Normalise la date pour la base (Y-m-d)
     * - Vérifie l'unicité de l'immatriculation
     * - Persiste en base puis redirige avec message     
     */
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            abort(405);
        }

        $userId = $_SESSION['user']['id'];

        // Validation simple des places disponibles
        if (empty($_POST['places_dispo']) || !is_numeric($_POST['places_dispo'])) {
            $_SESSION['error'] = "Veuillez sélectionner un nombre de places valide.";
            redirect('/vehicle/create');
        }

        // Conversion de la date en format SQL si fournie
        $dateFr = $_POST['date_premiere_immatriculation'] ?? '';
        $dateSql = !empty($dateFr)
            ? \DateTime::createFromFormat('Y-m-d', $dateFr)?->format('Y-m-d')
            : null;

        // Construction de l'entité Vehicle à partir du POST
        $vehicle = new VehicleEntity([
            'user_id' => $userId,
            'marque' => trim($_POST['marque'] ?? ''),
            'modele' => trim($_POST['modele'] ?? ''),
            'couleur' => trim($_POST['couleur'] ?? ''),
            'immatriculation' => trim($_POST['immatriculation'] ?? ''),
            'date_premiere_immatriculation' => $dateSql,
            'fuel_type_id' => $_POST['fuel_type_id'] ?? null,
            'places_dispo' => (int) $_POST['places_dispo'],
            'preferences' => isset($_POST['preferences']) ? implode(',', $_POST['preferences']) : '',
            'custom_preferences' => trim($_POST['custom_preferences'] ?? '')
        ]);

        // Vérifie l'unicité de l'immatriculation pour cet utilisateur
        // Note: on pourrait utiliser $userId ici pour restreindre la vérification à l'utilisateur courant
        if ($this->vehicleRepository->existsByImmatriculation($vehicle->getImmatriculation(), 0)) {
            $_SESSION['error'] = "Cette immatriculation est déjà utilisée.";
            redirect('/vehicle/create');
        }

        // Persistance + retour utilisateur
        if ($this->vehicleRepository->create($vehicle)) {
            $_SESSION['success'] = "Véhicule ajouté avec succès.";
            redirect('/my-profil');
        } else {
            $_SESSION['error'] = "Erreur lors de l'ajout du véhicule.";
            redirect('/vehicle/create');
        }
    }

    /**
     * Affiche le formulaire d'édition prérempli pour un véhicule existant.
     * - Vérifie l'appartenance du véhicule à l'utilisateur courant
     */
    public function edit(): void
    {
        $vehicleId = (int) ($_GET['id'] ?? 0);
        $userId = $_SESSION['user']['id'];

        $vehicle = $this->vehicleRepository->findById($vehicleId);

        // Protection: existence + autorisation (appartenance)
        if (!$vehicle || $vehicle->getUserId() !== $userId) {
            $_SESSION['error'] = "Véhicule introuvable ou non autorisé.";
            redirect('/my-profil');
        }

        $this->render("pages/form-vehicule", [
            'vehicle' => $vehicle->toArray()
        ]);
    }

    /**
     * Traite la mise à jour d'un véhicule.
     * - Autorise uniquement POST
     * - Vérifie l'existence et l'appartenance
     * - Vérifie l'unicité de l'immatriculation (hors véhicule en cours)
     * - Met à jour en base et redirige
     */
    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            abort(405);
        }

        $vehicleId = (int) ($_POST['vehicle_id'] ?? 0);
        $userId = $_SESSION['user']['id'];

        $existingVehicle = $this->vehicleRepository->findById($vehicleId);

        // Protection: existence + autorisation
        if (!$existingVehicle || $existingVehicle->getUserId() !== $userId) {
            $_SESSION['error'] = "Véhicule introuvable ou non autorisé.";
            redirect('/vehicle/edit');
        }

        $immatriculation = trim($_POST['immatriculation'] ?? '');

        // Unicité de l'immatriculation pour l'utilisateur, hors véhicule en cours d'édition
        if (
            $this->vehicleRepository->existsByImmatriculation($immatriculation, $userId)
            && $existingVehicle->getImmatriculation() !== $immatriculation
        ) {
            $_SESSION['error'] = "Cette immatriculation est déjà utilisée par un autre véhicule.";
            redirect('/vehicle/edit');
        }

        // Conversion de la date en format SQL si fournie
        $dateFr = $_POST['date_premiere_immatriculation'] ?? '';
        $dateSql = !empty($dateFr)
            ? \DateTime::createFromFormat('Y-m-d', $dateFr)?->format('Y-m-d')
            : null;

        // Normalisation des préférences multi-choix
        $preferences = isset($_POST['preferences']) ? implode(',', $_POST['preferences']) : '';

        // Construction d'une nouvelle entité Vehicle avec les valeurs mises à jour
        $vehicle = new VehicleEntity([
            'id' => $vehicleId,
            'user_id' => $userId,
            'marque' => trim($_POST['marque'] ?? ''),
            'modele' => trim($_POST['modele'] ?? ''),
            'couleur' => trim($_POST['couleur'] ?? ''),
            'immatriculation' => $immatriculation,
            'date_premiere_immatriculation' => $dateSql,
            'fuel_type_id' => $_POST['fuel_type_id'] ?? null,
            'places_dispo' => (int) $_POST['places_dispo'],
            'preferences' => $preferences,  // valeurs multi sélectionnées sous forme de CSV
            'custom_preferences' => trim($_POST['custom_preferences'] ?? '')
        ]);

        $this->vehicleRepository->update($vehicle);

        $_SESSION['success'] = "Véhicule mis à jour avec succès.";
        redirect('/my-profil');
    }

    /**
     * Supprime un véhicule appartenant à l'utilisateur courant.
     * - Autorise uniquement POST
     * - Vérifie l'appartenance avant suppression
     */
    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            abort(405);
        }

        $vehicleId = (int) ($_POST['vehicle_id'] ?? 0);
        if ($vehicleId <= 0) {
            $_SESSION['error'] = "ID de véhicule invalide.";
            redirect('/my-profil');
        }

        $vehicle = $this->vehicleRepository->findById($vehicleId);
        if (!$vehicle || $vehicle->getUserId() !== $_SESSION['user']['id']) {
            $_SESSION['error'] = "Véhicule introuvable ou non autorisé.";
            redirect('/my-profil');
        }

        $this->vehicleRepository->deleteById($vehicleId);
        $_SESSION['success'] = "Véhicule supprimé avec succès.";
        redirect('/my-profil');
    }
}
