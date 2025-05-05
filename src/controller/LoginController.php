<?php
namespace App\controller;

class LoginController
{
    public function index(): void
    {
        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $message = "Formulaire reçu avec les données : " . json_encode($_POST);
        }

        view('login', compact('message'));
    }
}
