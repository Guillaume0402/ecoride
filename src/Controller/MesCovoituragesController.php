<?php
namespace App\Controller;

class MesCovoituragesController
{
    public function index(): void
    {
        $trajets = []; // Ex : récupérés depuis une BDD plus tard
        view('mes-covoiturages', compact('covoiturages'));
    }
}