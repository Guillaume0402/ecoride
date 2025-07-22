<?php
namespace App\Controller;

class CreationProfilController
{
    public function index(): void
    {
        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $message = "Formulaire reçu avec les données : " . json_encode($_POST);
        }

        view('creation-profil', compact('message'));
    }
}
