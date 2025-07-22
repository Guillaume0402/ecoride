<?php
namespace App\Controller;

class HomeController
{
    public function index(): void
    {
        view('home'); // Appelle view/home.php automatiquement
    }
}
