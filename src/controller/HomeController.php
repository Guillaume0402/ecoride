<?php
namespace App\controller;

class HomeController
{
    public function index(): void
    {
        view('home'); // Appelle view/home.php automatiquement
    }
}
