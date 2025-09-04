<?php

namespace App\Controller;

use App\Repository\VehicleRepository;

// Contrôleur des pages (publiques/protégées): statiques, covoiturages, profil
class PageController extends Controller
{

    //Dépôt pour interagir avec les véhicules des utilisateurs.     
    private VehicleRepository $vehicleRepository;


    //Initialise les dépendances nécessaires aux pages.     
    public function __construct()
    {
        parent::__construct();
        // Instanciation du repository véhicule (accès DB véhicules)
        $this->vehicleRepository = new VehicleRepository();
    }

    // Page d'accueil.     
    public function home(): void
    {
        $this->render("home");
    }

    //Page de contact.

    public function contact(): void
    {
        $this->render("pages/contact");
    }

    //Liste des covoiturages (vue listant les annonces).

    public function listeCovoiturages(): void
    {
        $this->render("pages/liste-covoiturages");
    }

    // Page de création d'un covoiturage.    
    public function creationCovoiturage(): void
    {
        $this->render("pages/creation-covoiturage");
    }

    // Page de création/édition du profil (protégée), précharge le véhicule
    public function creationProfil(): void
    {
        // Vérifie que l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = "Vous devez être connecté pour accéder à cette page.";
            redirect('/login');
        }

        // Récupère l'utilisateur de la session
        $user = $_SESSION['user'];

        // Si un id de véhicule est fourni, on charge ce véhicule, sinon le véhicule associé au user (s'il existe)
        $vehicleEntity = !empty($_GET['id'])
            ? $this->vehicleRepository->findById((int) $_GET['id'])
            : $this->vehicleRepository->findByUserId($user['id']);

        // Normalise en tableau pour la vue
        $vehicle = $vehicleEntity ? $vehicleEntity->toArray() : null;

        // Rend la page avec les données utilisateur + véhicule
        $this->render("pages/creation-profil", [
            'user' => $user,
            'vehicle' => $vehicle
        ]);
    }

    // Page listant les covoiturages de l'utilisateur courant.

    public function mesCovoiturages(): void
    {
        $this->render("pages/mes-covoiturages");
    }

    // Page profil (protégée), charge les véhicules de l'utilisateur
    public function profil(): void
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
        }

        $user = $_SESSION['user'];
        $vehicles = $this->vehicleRepository->findAllByUserId($user['id']);

        $this->render("pages/my-profil", [
            'user' => $user,
            'vehicles' => $vehicles
        ]);
    }

    //Page de connexion.

    public function login(): void
    {
        $this->render("pages/login");
    }

    //Page "À propos".

    public function about(): void
    {
        $this->render("pages/about");
    }

    // Page des conditions d'utilisation.

    public function terms(): void
    {
        $this->render("pages/terms");
    }

    //Page de politique de confidentialité.

    public function privacy(): void
    {
        $this->render("pages/privacy");
    }
}
