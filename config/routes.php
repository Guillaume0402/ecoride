<?php


return [
    // Route pour la page d'accueil
    "/" => [
        "controller" => "App\Controller\PageController",
        "action" => "home"
    ],

    // Routes pour l'authentification
    "/login" => [
        'GET' => [
            "controller" => "App\Controller\AuthController",
            "action" => "showLogin"
        ]
    ],    
    "/logout" => [
        "controller" => "App\Controller\AuthController",
        "action" => "logout"
    ],

    // NOUVELLES ROUTES API pour votre modal
    "/api/auth/register" => [
        "controller" => "App\Controller\AuthController",
        "action" => "apiRegister"
    ],
    "/api/auth/login" => [
        "controller" => "App\Controller\AuthController",
        "action" => "apiLogin"
    ],
    "/api/auth/logout" => [
        "controller" => "App\Controller\AuthController",
        "action" => "apiLogout"
    ],

 
    // Routes pour le profil utilisateur
    "/creation-profil" => [
        "controller" => "App\Controller\PageController",
        "action" => "creationProfil"
    ],
    "/profile" => [
        "controller" => "App\Controller\UserController",
        "action" => "profile"
    ],
    "/profile/edit" => [
        "controller" => "App\Controller\UserController",
        "action" => "edit"
    ],

  
    // Routes statiques
    "/about" => [
        "controller" => "App\Controller\PageController",
        "action" => "about"
    ],
    "/liste-covoiturages" => [
        "controller" => "App\Controller\PageController",
        "action" => "listeCovoiturages"
    ],
    "/contact" => [
        "controller" => "App\Controller\PageController",
        "action" => "contact"
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
