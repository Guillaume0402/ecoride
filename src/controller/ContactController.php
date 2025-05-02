<?php
namespace App\controller;

class ContactController
{
    public function index(): void
    {
        $contact = []; // Ex : récupérés depuis une BDD plus tard
        view('contact', compact('contact'));
    }
}