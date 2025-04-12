<?php
namespace App\controller;

class CovoituragesController {
    public function index() {
        // Simplement inclure la vue "home.php"
        // qui se trouve dans: EcoRide/src/view/home.php
        require_once __DIR__ . '/../view/covoiturages.php';
    }
}