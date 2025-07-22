<?php
namespace App\Controller;

class CreationCovoiturageController
{
    public function index(): void
    {
        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $message = "Formulaire reçu avec les données : " . json_encode($_POST);
        }

        view('creation-covoiturage', compact('message'));
    }
}
