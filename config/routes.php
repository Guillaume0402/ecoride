<?php

return [

    // =========================
    // 🌍 Pages publiques
    // =========================
    "/" => [
        "controller" => "App\Controller\PageController",
        "action" => "home"
    ],
    "/about" => [
        "controller" => "App\Controller\PageController",
        "action" => "about"
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
    ],
    "/liste-covoiturages" => [
        "controller" => "App\Controller\PageController",
        "action" => "listeCovoiturages"
    ],

    // =========================
    // 🔐 Authentification
    // =========================
    "/login" => [
        "controller" => "App\Controller\AuthController",
        "action" => "showLogin"
    ],
    "/logout" => [
        "controller" => "App\Controller\AuthController",
        "action" => "logout"
    ],
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

    // =========================
    // 👤 Profil utilisateur
    // =========================
    "/profile" => [
        'GET' => [
            "controller" => "App\Controller\ProfilController",
            "action" => "showForm"
        ],
        'POST' => [
            "controller" => "App\Controller\ProfilController",
            "action" => "update"
        ]
    ],

    "/creation-profil" => [
        "controller" => "App\Controller\PageController",
        "action" => "creationProfil"
    ],

    "/my-profil" => [
        "controller" => "App\Controller\PageController",
        "action" => "Profil"
    ],

    // (optionnel si utilisé)
    "/profile/edit" => [
        "controller" => "App\Controller\UserController",
        "action" => "edit"
    ],

    // =========================
    // ⚙️ Administration
    // =========================
    "/admin/dashboard" => [
        "controller" => "App\Controller\AdminController",
        "action" => "dashboard"
    ],
    "/admin/users" => [
        "controller" => "App\Controller\AdminController",
        "action" => "users"
    ],
    "/admin/stats" => [
        "controller" => "App\Controller\AdminController",
        "action" => "stats"
    ]
];
