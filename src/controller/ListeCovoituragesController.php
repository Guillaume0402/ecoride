<?php
namespace App\controller;

class ListeCovoituragesController
{
    public function index(): void
    {
        $trajets = []; // Ex : récupérés depuis une BDD plus tard
        view('liste-covoiturages', compact('trajets'));
    }
}