<?php
namespace App\controller;

class SigninController
{
    public function index(): void
    {
        $inscription = []; // Ex : récupérés depuis une BDD plus tard
        view('signin', compact('inscription'));
    }
}