<?php
namespace App\controller;

class ProfilController
{
    public function index(): void
    {
        $profil = []; // Ex : récupérés depuis une BDD plus tard
        view('my-profil', compact('profil'));
    }
}