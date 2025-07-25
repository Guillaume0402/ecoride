<?php

namespace App\Controller;

class PageController extends Controller
{
    public function home(): void
    {
        // Rendu de la page d'accueil avec les données
        $this->render("home", []);
    }

    public function contact(): void
    {
        // Rendu de la page de contact
        $this->render("pages/contact", []);
    }

    public function listeCovoiturages(): void
    {
        // Rendu de la liste des covoiturages
        $this->render("pages/liste-covoiturages", []);
    }

    public function creationCovoiturage(): void
    {
        // Rendu de la création de covoiturage
        $this->render("pages/creation-covoiturage", []);
    }

    public function creationProfil(): void
    {
        // Rendu de la création de profil
        $this->render("pages/creation-profil", []);
    }

    public function mesCovoiturages(): void
    {
        // Rendu de mes covoiturages
        $this->render("pages/mes-covoiturages", []);
    }

    public function Profil(): void
    {
        // Rendu du profil utilisateur
        $this->render("pages/my-profil", []);
    }

    public function login(): void
    {
        // Rendu de la page de connexion
        $this->render("pages/login", []);
    }

    // Pages manquantes à créer
    public function about(): void
    {
        // TODO: Créer src/View/pages/about.php
        $this->render("pages/about", []);
    }

    public function terms(): void
    {
        // TODO: Créer src/View/pages/terms.php
        $this->render("pages/terms", []);
    }

    public function privacy(): void
    {
        // TODO: Créer src/View/pages/privacy.php
        $this->render("pages/privacy", []);
    }
}