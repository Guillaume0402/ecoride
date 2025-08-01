<?php

namespace App\Controller;

use App\Repository\VehicleRepository;

class PageController extends Controller
{
    private VehicleRepository $vehicleRepository;

    public function __construct()
    {
        parent::__construct();
        $this->vehicleRepository = new VehicleRepository();
    }

    public function home(): void
    {
        $this->render("home");
    }

    public function contact(): void
    {
        $this->render("pages/contact");
    }

    public function listeCovoiturages(): void
    {
        $this->render("pages/liste-covoiturages");
    }

    public function creationCovoiturage(): void
    {
        $this->render("pages/creation-covoiturage");
    }

    public function creationProfil(): void
    {
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = "Vous devez être connecté pour accéder à cette page.";
            redirect('/login');
        }

        $user = $_SESSION['user'];

        $vehicleEntity = !empty($_GET['id'])
            ? $this->vehicleRepository->findById((int) $_GET['id'])
            : $this->vehicleRepository->findByUserId($user['id']);

        $vehicle = $vehicleEntity ? $vehicleEntity->toArray() : null;

        $this->render("pages/creation-profil", [
            'user' => $user,
            'vehicle' => $vehicle
        ]);
    }

    public function mesCovoiturages(): void
    {
        $this->render("pages/mes-covoiturages");
    }

    public function profil(): void
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
        }

        $user = $_SESSION['user'];
        $vehicleEntities = $this->vehicleRepository->findAllByUserId($user['id']);

        // Convertir la liste d'entités en tableaux pour la vue
        $vehicles = array_map(fn($v) => $v->toArray(), $vehicleEntities);

        $this->render("pages/my-profil", [
            'user' => $user,
            'vehicles' => $vehicles
        ]);
    }

    public function login(): void
    {
        $this->render("pages/login");
    }

    public function about(): void
    {
        $this->render("pages/about");
    }

    public function terms(): void
    {
        $this->render("pages/terms");
    }

    public function privacy(): void
    {
        $this->render("pages/privacy");
    }
}
