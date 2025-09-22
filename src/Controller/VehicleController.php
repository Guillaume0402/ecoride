<?php

namespace App\Controller;

use App\Entity\VehicleEntity;
use App\Repository\VehicleRepository;
use App\Service\Flash;
use App\Security\Csrf;


// Contrôleur véhicules: accès protégé + CRUD (create/edit/update/delete)
class VehicleController extends Controller
{
    // Dépôt d'accès aux données véhicules.     
    private VehicleRepository $vehicleRepository;

    // Initialise le repository et applique le contrôle d'accès (auth requis).     
    public function __construct()
    {
        parent::__construct();
        $this->vehicleRepository = new VehicleRepository();

        if (!isset($_SESSION['user'])) {
            Flash::add('Veuillez vous connecter.', 'danger');
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

    // Traite la création d'un véhicule (POST): validations, normalisations, persistance
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            abort(405);
        }

        // CSRF
        if (!Csrf::check($_POST['csrf'] ?? null)) {
            Flash::add('Requête invalide (CSRF).', 'danger');
            redirect('/vehicle/create');
        }

        $userId = (int) ($_SESSION['user']['id'] ?? 0);
        if ($userId <= 0) {
            Flash::add('Veuillez vous connecter.', 'danger');
            redirect('/login');
        }

        // places_dispo (int entre 1 et 9)
        $places = filter_input(
            INPUT_POST,
            'places_dispo',
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 9]]
        );
        if ($places === false) {
            Flash::add('Veuillez sélectionner un nombre de places valide.', 'danger');
            redirect('/vehicle/create');
        }

        // Date Y-m-d -> SQL (string|nullable)
        $dateFr = $_POST['date_premiere_immatriculation'] ?? '';
        $dateSql = null;
        if ($dateFr !== '') {
            $dt = \DateTime::createFromFormat('Y-m-d', $dateFr);
            if ($dt instanceof \DateTime) {
                $dateSql = $dt->format('Y-m-d');
            }
        }

        // Normalisation plaque via le repo (tu l’as ajoutée dans le repo 👍)
        $immatriculation = VehicleRepository::normalizePlate($_POST['immatriculation'] ?? '');

        // Whitelist des préférences (sécurité)
        $allowed = ['fumeur', 'non-fumeur', 'animaux', 'pas-animaux'];
        $prefs   = array_intersect($allowed, (array) ($_POST['preferences'] ?? []));
        $preferences = implode(',', $prefs);

        // Unicité (par utilisateur)
        if ($this->vehicleRepository->existsByImmatriculation($immatriculation, $userId)) {
            Flash::add('Cette immatriculation est déjà utilisée.', 'danger');
            redirect('/vehicle/create');
        }

        $vehicle = new VehicleEntity([
            'user_id'                       => $userId,
            'marque'                        => trim($_POST['marque'] ?? ''),
            'modele'                        => trim($_POST['modele'] ?? ''),
            'couleur'                       => trim($_POST['couleur'] ?? ''),
            'immatriculation'               => $immatriculation,
            'date_premiere_immatriculation' => $dateSql,
            'fuel_type_id'                  => ($_POST['fuel_type_id'] ?? null) ?: null,
            'places_dispo'                  => $places,
            'preferences'                   => $preferences,
            'custom_preferences'            => trim($_POST['custom_preferences'] ?? ''),
        ]);

        if ($this->vehicleRepository->create($vehicle)) {
            Flash::add('Véhicule ajouté avec succès.', 'success');
            redirect('/my-profil');
        }

        Flash::add("Erreur lors de l'ajout du véhicule.", 'danger');
        redirect('/vehicle/create');
    }


    // Affiche le formulaire d'édition (vérifie appartenance)
    public function edit(): void
    {
        $vehicleId = (int) ($_GET['id'] ?? 0);
        $userId = (int) ($_SESSION['user']['id'] ?? 0);

        $vehicle = $this->vehicleRepository->findById($vehicleId);

        // Protection: existence + autorisation (appartenance)
        if (!$vehicle || $vehicle->getUserId() !== $userId) {
            Flash::add('Véhicule introuvable ou non autorisé.', 'danger');
            redirect('/my-profil');
        }


        $this->render("pages/form-vehicule", [
            'vehicle' => $vehicle->toArray()
        ]);
    }

    // Traite la mise à jour (POST): existence, appartenance, unicité, persistance
    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            abort(405);
        }

        if (!Csrf::check($_POST['csrf'] ?? null)) {
            Flash::add('Requête invalide (CSRF).', 'danger');
            redirect('/my-profil');
        }

        $vehicleId = filter_input(INPUT_POST, 'vehicle_id', FILTER_VALIDATE_INT);
        $userId    = (int) $_SESSION['user']['id'];

        if (!$vehicleId) {
            Flash::add('ID de véhicule invalide.', 'danger');
            redirect('/my-profil');
        }

        $existingVehicle = $this->vehicleRepository->findById($vehicleId);
        if (!$existingVehicle || $existingVehicle->getUserId() !== $userId) {
            Flash::add('Véhicule introuvable ou non autorisé.', 'danger');
            redirect('/my-profil');
        }

        $immatriculation = VehicleRepository::normalizePlate($_POST['immatriculation'] ?? '');


        // ⚠️ Idéalement, une méthode qui exclut l’ID courant:
        // existsByImmatriculationForUserExcept($immat, $userId, $excludeId)
        if (
            $this->vehicleRepository->existsByImmatriculation($immatriculation, $userId)
            && $existingVehicle->getImmatriculation() !== $immatriculation
        ) {
            Flash::add("Cette immatriculation est déjà utilisée par un autre véhicule.", 'danger');
            redirect('/vehicle/edit?id=' . $vehicleId);
        }

        $dateFr = $_POST['date_premiere_immatriculation'] ?? '';
        $dateSql = null;
        if ($dateFr !== '') {
            $dt = \DateTime::createFromFormat('Y-m-d', $dateFr);
            if ($dt instanceof \DateTime) {
                $dateSql = $dt->format('Y-m-d');
            }
        }

        $allowed = ['fumeur', 'non-fumeur', 'animaux', 'pas-animaux'];
        $prefs   = array_intersect($allowed, (array) ($_POST['preferences'] ?? []));
        $preferences = implode(',', $prefs);

        $places = filter_input(
            INPUT_POST,
            'places_dispo',
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 9]]
        );

        $vehicle = new VehicleEntity([
            'id'                          => $vehicleId,
            'user_id'                     => $userId,
            'marque'                      => trim($_POST['marque'] ?? ''),
            'modele'                      => trim($_POST['modele'] ?? ''),
            'couleur'                     => trim($_POST['couleur'] ?? ''),
            'immatriculation'             => $immatriculation,
            'date_premiere_immatriculation' => $dateSql,
            'fuel_type_id'                => ($_POST['fuel_type_id'] ?? null) ?: null,
            'places_dispo'                => $places ?: (int) $existingVehicle->getPlacesDispo(),
            'preferences'                 => $preferences,
            'custom_preferences'          => trim($_POST['custom_preferences'] ?? ''),
        ]);

        $this->vehicleRepository->update($vehicle);

        Flash::add('Véhicule mis à jour avec succès.', 'success');
        redirect('/my-profil');
    }


    // Supprime un véhicule (POST) après vérification d'appartenance
    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            abort(405);
        }

        if (!Csrf::check($_POST['csrf'] ?? null)) {
            Flash::add('Requête invalide (CSRF).', 'danger');
            redirect('/my-profil');
        }

        $vehicleId = filter_input(INPUT_POST, 'vehicle_id', FILTER_VALIDATE_INT);
        if (!$vehicleId) {
            Flash::add('ID de véhicule invalide.', 'danger');
            redirect('/my-profil');
        }

        $vehicle = $this->vehicleRepository->findById($vehicleId);
        if (!$vehicle || $vehicle->getUserId() !== (int) $_SESSION['user']['id']) {
            Flash::add('Véhicule introuvable ou non autorisé.', 'danger');
            redirect('/my-profil');
        }

        $this->vehicleRepository->deleteById($vehicleId);
        Flash::add('Véhicule supprimé avec succès.', 'success');
        redirect('/my-profil');
    }
}
