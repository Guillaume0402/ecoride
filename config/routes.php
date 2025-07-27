<?php

$routes = [

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
        'GET' => [
            "controller" => "App\\Controller\\ProfilController",
            "action" => "showForm"
        ],
        'POST' => [
            "controller" => "App\\Controller\\ProfilController",
            "action" => "update"
        ]
    ],

    "/my-profil" => [
        "controller" => "App\Controller\PageController",
        "action" => "Profil"
    ],

    "/profile/edit" => [
        "controller" => "App\Controller\UserController",
        "action" => "edit"
    ],

    "/vehicle/create" => [
        'GET' => [
            "controller" => "App\Controller\VehicleController",
            "action" => "create"
        ],
        'POST' => [
            "controller" => "App\Controller\VehicleController",
            "action" => "store"
        ]
    ],
    "/vehicle/edit" => [
        'GET' => [
            "controller" => "App\\Controller\\VehicleController",
            "action" => "edit"
        ],
        'POST' => [
            "controller" => "App\\Controller\\VehicleController",
            "action" => "update"
        ]
    ],
    "/vehicle/delete" => [
        'POST' => [
            "controller" => "App\Controller\VehicleController",
            "action" => "delete"
        ]
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

// ✅ Retour avec normalisation des clés
return array_combine(
    array_map(fn($key) => rtrim($key, '/') ?: '/', array_keys($routes)),
    array_values($routes)
);
