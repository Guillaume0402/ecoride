<?php


return [
    // Route pour la page d'accueil
    "/" => [
        "controller" => "App\Controller\PageController",
        "action" => "home"
    ],

    // Routes pour l'authentification
    "/login" => [
        "controller" => "App\Controller\AuthController",
        "action" => "showLogin"
    ],
    "/register" => [
        "controller" => "App\Controller\AuthController",
        "action" => "showRegister"
    ],
    "/logout" => [
        "controller" => "App\Controller\AuthController",
        "action" => "logout"
    ],

    // Routes pour les trajets
    "/rides" => [
        "controller" => "App\Controller\RideController",
        "action" => "index"
    ],
    "/rides/create" => [
        "controller" => "App\Controller\RideController",
        "action" => "create"
    ],
    "/rides/search" => [
        "controller" => "App\Controller\RideController",
        "action" => "search"
    ],

    // Routes pour le profil utilisateur
    "/profile" => [
        "controller" => "App\Controller\UserController",
        "action" => "profile"
    ],
    "/profile/edit" => [
        "controller" => "App\Controller\UserController",
        "action" => "edit"
    ],

    // Routes pour les réservations
    "/bookings" => [
        "controller" => "App\Controller\BookingController",
        "action" => "index"
    ],
    "/bookings/create" => [
        "controller" => "App\Controller\BookingController",
        "action" => "create"
    ],

    // Routes pour l'administration
    "/admin" => [
        "controller" => "App\Controller\AdminController",
        "action" => "dashboard"
    ],
    "/admin/users" => [
        "controller" => "App\Controller\AdminController",
        "action" => "users"
    ],
    "/admin/rides" => [
        "controller" => "App\Controller\AdminController",
        "action" => "rides"
    ],

    // Routes statiques
    "/about" => [
        "controller" => "App\Controller\PageController",
        "action" => "about"
    ],
    "/contact" => [
        "controller" => "App\Controller\ContactController",
        "action" => "index"
    ],
    "/terms" => [
        "controller" => "App\Controller\PageController",
        "action" => "terms"
    ],
    "/privacy" => [
        "controller" => "App\Controller\PageController",
        "action" => "privacy"
    ]
];
