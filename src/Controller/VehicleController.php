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

        $userId = (int) $_SESSION['user']['id'];

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

        // Date Y-m-d -> SQL (string|nullable) + validation: pas dans le futur
        $dateFr = $_POST['date_premiere_immatriculation'] ?? '';
        $dateSql = null;
        if ($dateFr !== '') {
            $dt = \DateTime::createFromFormat('Y-m-d', $dateFr);
            if ($dt instanceof \DateTime) {
                $dt->setTime(0, 0, 0);
                $today = new \DateTime('today');
                if ($dt > $today) {
                    Flash::add("La date de première immatriculation ne peut pas être postérieure à aujourd'hui.", 'danger');
                    redirect('/vehicle/create');
                    return;
                }
                $dateSql = $dt->format('Y-m-d');
            }
        }

        // Normalisation plaque via le repo (tu l’as ajoutée dans le repo 👍)
        $immatriculation = VehicleRepository::normalizePlate($_POST['immatriculation'] ?? '');

        // Whitelist des préférences (sécurité) + exclusivité logique
        $allowed = ['fumeur', 'non-fumeur', 'animaux', 'pas-animaux'];
        $prefs   = array_values(array_intersect($allowed, (array) ($_POST['preferences'] ?? [])));
        // Conflits exclusifs: fumeur vs non-fumeur, animaux vs pas-animaux
        if (in_array('fumeur', $prefs, true) && in_array('non-fumeur', $prefs, true)) {
            Flash::add('Vous ne pouvez pas sélectionner à la fois Fumeur et Non-fumeur.', 'danger');
            redirect('/vehicle/create');
            return;
        }
        if (in_array('animaux', $prefs, true) && in_array('pas-animaux', $prefs, true)) {
            Flash::add("Vous ne pouvez pas sélectionner à la fois 'Animaux acceptés' et 'Pas d'animal'.", 'danger');
            redirect('/vehicle/create');
            return;
        }
        $preferences = implode(',', $prefs);

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
        $userId = (int) ($_SESSION['user']['id'] ?? 0);

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
        $userId    = (int) $_SESSION['user']['id'];

        if (!$vehicleId) {
            Flash::add('ID de véhicule invalide.', 'danger');
            redirect('/my-profil');
            return;
        }

        $existingVehicle = $this->vehicleRepository->findById($vehicleId);
        if (!$existingVehicle || $existingVehicle->getUserId() !== $userId) {
            Flash::add('Véhicule introuvable ou non autorisé.', 'danger');
            redirect('/my-profil');
            return;
        }

        $immatriculation = VehicleRepository::normalizePlate($_POST['immatriculation'] ?? '');


        // méthode qui exclut l’ID courant:
        // existsByImmatriculationForUserExcept($immat, $userId, $excludeId)
        if ($this->vehicleRepository->existsByImmatriculationGlobal($immatriculation, (int)$vehicleId)) {
            Flash::add("Cette immatriculation est déjà utilisée par un autre véhicule.", 'danger');
            redirect('/vehicle/edit?id=' . $vehicleId);
            return;
        }


        $dateFr = $_POST['date_premiere_immatriculation'] ?? '';
        $dateSql = null;
        if ($dateFr !== '') {
            $dt = \DateTime::createFromFormat('Y-m-d', $dateFr);
            if ($dt instanceof \DateTime) {
                $dt->setTime(0, 0, 0);
                $today = new \DateTime('today');
                if ($dt > $today) {
                    Flash::add("La date de première immatriculation ne peut pas être postérieure à aujourd'hui.", 'danger');
                    redirect('/vehicle/edit?id=' . $vehicleId);
                    return;
                }
                $dateSql = $dt->format('Y-m-d');
            }
        }

        $allowed = ['fumeur', 'non-fumeur', 'animaux', 'pas-animaux'];
        $prefs   = array_values(array_intersect($allowed, (array) ($_POST['preferences'] ?? [])));
        if (in_array('fumeur', $prefs, true) && in_array('non-fumeur', $prefs, true)) {
            Flash::add('Vous ne pouvez pas sélectionner à la fois Fumeur et Non-fumeur.', 'danger');
            redirect('/vehicle/edit?id=' . $vehicleId);
            return;
        }
        if (in_array('animaux', $prefs, true) && in_array('pas-animaux', $prefs, true)) {
            Flash::add("Vous ne pouvez pas sélectionner à la fois 'Animaux acceptés' et 'Pas d'animal'.", 'danger');
            redirect('/vehicle/edit?id=' . $vehicleId);
            return;
        }
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
        return;
    }


    // Supprime un véhicule (POST) après vérification d'appartenance
    public function delete(): void
    {
        $this->requirePost();
        $this->requireCsrf($_POST['csrf'] ?? null, '/my-profil');

        $userId = (int) $_SESSION['user']['id'];

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
}
