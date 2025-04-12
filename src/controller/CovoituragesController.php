<?php
namespace App\controller;

class CovoituragesController
{
    public function index(): void
    {
        $trajets = []; // Ex : récupérés depuis une BDD plus tard
        view('covoiturages', compact('trajets'));
    }
}