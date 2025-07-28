<?php

namespace App\Controller;

use App\Model\VehicleModel;

class PageController extends Controller
{
    private VehicleModel $vehicleModel;

    public function __construct()
    {
        parent::__construct();
        $this->vehicleModel = new VehicleModel();
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

        // Si un id de véhicule est passé en GET, on le récupère
        $vehicle = !empty($_GET['id'])
            ? $this->vehicleModel->findById((int) $_GET['id'])
            : $this->vehicleModel->findByUserId($user['id']);

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
        $vehicles = $this->vehicleModel->findAllByUserId($user['id']);

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
