<?php

namespace App\Controller;

use App\Entity\VehicleEntity;
use App\Repository\VehicleRepository;
use App\Service\Flash;

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
        $this->requireAuth();
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
        $this->requirePost();
        $this->requireCsrf($_POST['csrf'] ?? null, '/vehicle/create');

        $user = $this->requireAuth();
        $userId = (int)$user['id'];

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
            return;
        }

        // Normalisation plaque via le repo (tu l’as ajoutée dans le repo 👍)
        $immatriculation = VehicleRepository::normalizePlate($_POST['immatriculation'] ?? '');

        try {
            $dateSql = $this->parseDateNotFuture($_POST['date_premiere_immatriculation'] ?? '');
            $preferences = $this->normalizePreferences((array)($_POST['preferences'] ?? []));
        } catch (\InvalidArgumentException $e) {
            Flash::add($e->getMessage(), 'danger');
            redirect('/vehicle/create');
            return;
        }

        // Unicité (globale)
        if ($this->vehicleRepository->existsByImmatriculationGlobal($immatriculation)) {
            Flash::add('Cette immatriculation est déjà utilisée.', 'danger');
            redirect('/vehicle/create');
            return;
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

        try {
            if ($this->vehicleRepository->create($vehicle)) {
                Flash::add('Véhicule ajouté avec succès.', 'success');
                redirect('/my-profil');
                return;
            }
        } catch (\PDOException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                Flash::add('Cette immatriculation est déjà utilisée.', 'danger');
                redirect('/vehicle/create');
                return;
            }
            throw $e;
        }

        Flash::add("Erreur lors de l'ajout du véhicule.", 'danger');
        redirect('/vehicle/create');
        return;
    }

    // Affiche le formulaire d'édition (vérifie appartenance)
    public function edit(): void
    {
        $vehicleId = (int) ($_GET['id'] ?? 0);
        $user = $this->requireAuth();
        $userId = (int) $user['id'];

        $vehicle = $this->vehicleRepository->findById($vehicleId);

        // Protection: existence + autorisation (appartenance)
        if (!$vehicle || $vehicle->getUserId() !== $userId) {
            Flash::add('Véhicule introuvable ou non autorisé.', 'danger');
            redirect('/my-profil');
            return;
        }


        $this->render("pages/form-vehicule", [
            'vehicle' => $vehicle->toArray()
        ]);
    }

    // Traite la mise à jour (POST): existence, appartenance, unicité, persistance
    public function update(): void
    {
        $this->requirePost();
        $this->requireCsrf($_POST['csrf'] ?? null, '/my-profil');

        $vehicleId = filter_input(INPUT_POST, 'vehicle_id', FILTER_VALIDATE_INT);
        if (!$vehicleId) {
            Flash::add('ID de véhicule invalide.', 'danger');
            redirect('/my-profil');
            return;
        }

        $user = $this->requireAuth();
        $userId = (int)$user['id'];

        $existingVehicle = $this->vehicleRepository->findById($vehicleId);
        if (!$existingVehicle || $existingVehicle->getUserId() !== $userId) {
            Flash::add('Véhicule introuvable ou non autorisé.', 'danger');
            redirect('/my-profil');
            return;
        }

        $immatriculation = VehicleRepository::normalizePlate($_POST['immatriculation'] ?? '');

        try {
            $dateSql = $this->parseDateNotFuture($_POST['date_premiere_immatriculation'] ?? '');
            $preferences = $this->normalizePreferences((array)($_POST['preferences'] ?? []));
        } catch (\InvalidArgumentException $e) {
            Flash::add($e->getMessage(), 'danger');
            redirect('/vehicle/edit?id=' . $vehicleId);
            return;
        }

        // Unicité globale en excluant le véhicule en cours
        if ($this->vehicleRepository->existsByImmatriculationGlobal($immatriculation, (int)$vehicleId)) {
            Flash::add("Cette immatriculation existe déjà. Vérifie la plaque.", 'danger');
            redirect('/vehicle/edit?id=' . $vehicleId);
            return;
        }

        $places = filter_input(
            INPUT_POST,
            'places_dispo',
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 9]]
        );

        $vehicle = new VehicleEntity([
            'id'                            => $vehicleId,
            'user_id'                       => $userId,
            'marque'                        => trim($_POST['marque'] ?? ''),
            'modele'                        => trim($_POST['modele'] ?? ''),
            'couleur'                       => trim($_POST['couleur'] ?? ''),
            'immatriculation'               => $immatriculation,
            'date_premiere_immatriculation' => $dateSql,
            'fuel_type_id'                  => ($_POST['fuel_type_id'] ?? null) ?: null,
            'places_dispo'                  => ($places === false || $places === null)
                ? (int) $existingVehicle->getPlacesDispo()
                : (int) $places,
            'preferences'                   => $preferences,
            'custom_preferences'            => trim($_POST['custom_preferences'] ?? ''),
        ]);

        $this->vehicleRepository->update($vehicle);

        Flash::add('Véhicule mis à jour avec succès.', 'success');
        redirect('/my-profil');
        return;
    }

    // Supprime un véhicule (POST) après vérification d'appartenance
    public function delete(): void
    {
        $this->requirePost();
        $this->requireCsrf($_POST['csrf'] ?? null, '/my-profil');

        $user = $this->requireAuth();
        $userId = (int)$user['id'];

        $vehicleId = filter_input(INPUT_POST, 'vehicle_id', FILTER_VALIDATE_INT);
        if (!$vehicleId) {
            Flash::add('ID de véhicule invalide.', 'danger');
            redirect('/my-profil');
            return;
        }

        $vehicle = $this->vehicleRepository->findById((int) $vehicleId);
        if (!$vehicle || $vehicle->getUserId() !== $userId) {
            Flash::add('Véhicule introuvable ou non autorisé.', 'danger');
            redirect('/my-profil');
            return;
        }

        $this->vehicleRepository->deleteById((int) $vehicleId);
        Flash::add('Véhicule supprimé avec succès.', 'success');
        redirect('/my-profil');
        return;
    }

    private function parseDateNotFuture(string $dateFr): ?string
    {
        if ($dateFr === '') return null;

        $dt = \DateTime::createFromFormat('Y-m-d', $dateFr);
        if (!$dt) return null;

        $dt->setTime(0, 0, 0);
        if ($dt > new \DateTime('today')) {
            throw new \InvalidArgumentException("La date de première immatriculation ne peut pas être postérieure à aujourd'hui.");
        }

        return $dt->format('Y-m-d');
    }

    private function normalizePreferences(array $input): string
    {
        $allowed = ['fumeur', 'non-fumeur', 'animaux', 'pas-animaux'];
        $prefs = array_values(array_intersect($allowed, $input));

        if (in_array('fumeur', $prefs, true) && in_array('non-fumeur', $prefs, true)) {
            throw new \InvalidArgumentException('Vous ne pouvez pas sélectionner à la fois Fumeur et Non-fumeur.');
        }
        if (in_array('animaux', $prefs, true) && in_array('pas-animaux', $prefs, true)) {
            throw new \InvalidArgumentException("Vous ne pouvez pas sélectionner à la fois 'Animaux acceptés' et 'Pas d'animal'.");
        }

        return implode(',', $prefs);
    }
}
