<?php


return [
    // NOTE paramètres de route:
    // - Les segments entre accolades (ex: {id}) sont interprétés par le Router comme des nombres (regex \d+).
    // - Si vous souhaitez des slugs alphanumériques, adaptez le Router pour utiliser [^/]+ à la place.
    //   Exemple: '/article/{slug}' avec slug alphanumérique → requires Router update.

    // Page d'accueil
    '/' => [
        'GET' => [
            'controller' => 'App\\Controller\\PageController',
            'action' => 'home'
        ]
    ],
    // Authentification (pages et API JSON)
    '/login' => [
        'GET' => [
            'controller' => 'App\\Controller\\AuthController',
            'action' => 'showLogin'
        ]
    ],
    '/logout' => [
        'GET' => [
            'controller' => 'App\\Controller\\AuthController',
            'action' => 'logout'
        ]
    ],
    // API Auth (utilisées par la modale)
    '/api/auth/register' => [
        'POST' => [
            'controller' => 'App\\Controller\\AuthController',
            'action' => 'apiRegister'
        ]
    ],
    '/api/auth/login' => [
        'POST' => [
            'controller' => 'App\\Controller\\AuthController',
            'action' => 'apiLogin'
        ]
    ],
    '/api/auth/logout' => [
        'POST' => [
            'controller' => 'App\\Controller\\AuthController',
            'action' => 'apiLogout'
        ]
    ],
    // Profil utilisateur
    '/my-profil' => [
        'GET' => [
            'controller' => 'App\\Controller\\PageController',
            'action' => 'profil'
        ]
    ],
    '/profil/edit' => [
        'GET' => [
            'controller' => 'App\\Controller\\UserController',
            'action' => 'edit'
        ]
    ],
    '/creation-profil' => [
        'GET' => [
            'controller' => 'App\\Controller\\PageController',
            'action' => 'creationProfil'
        ],
        'POST' => [
            'controller' => 'App\\Controller\\ProfilController',
            'action' => 'update'
        ]
    ],

    '/vehicle/create' => [
        // Afficher le formulaire vide
        'GET' => [
            'controller' => 'App\\Controller\\VehicleController',
            'action' => 'create'
        ],
        // Traiter le formulaire de création
        'POST' => [
            'controller' => 'App\\Controller\\VehicleController',
            'action' => 'store'
        ]
    ],
    '/vehicle/update' => [
        // Traiter les modifications du véhicule
        'POST' => [
            'controller' => 'App\\Controller\\VehicleController',
            'action' => 'update'
        ]
    ],
    // Traiter la suppression du véhicule
    '/vehicle/delete' => [
        'POST' => [
            'controller' => 'App\\Controller\\VehicleController',
            'action' => 'delete'
        ]
    ],
    '/vehicle/edit' => [
        // Afficher le formulaire pré-rempli
        'GET' => [
            'controller' => 'App\\Controller\\VehicleController',
            'action' => 'edit'
        ]
    ],

    // Administration
    '/admin' => [
        'GET' => [
            'controller' => 'App\\Controller\\AdminController',
            'action' => 'dashboard'
        ]
    ],
    '/admin/dashboard' => [
        'GET' => [
            'controller' => 'App\\Controller\\AdminController',
            'action' => 'dashboard'
        ]
    ],
    '/admin/users' => [
        'GET' => [
            'controller' => 'App\\Controller\\AdminController',
            'action' => 'users'
        ]
    ],
    '/admin/stats' => [
        'GET' => [
            'controller' => 'App\\Controller\\AdminController',
            'action' => 'stats'
        ]
    ],
    '/admin/users/create' => [
        'POST' => [
            'controller' => 'App\\Controller\\AdminController',
            'action' => 'createEmployee'
        ]
    ],
    '/admin/users/toggle/{id}' => [
        // {id} = identifiant numérique de l'utilisateur
        'POST' => [
            'controller' => 'App\\Controller\\AdminController',
            'action' => 'toggleEmployeeStatus'
        ]
    ],
    '/admin/users/delete/{id}' => [
        // {id} = identifiant numérique de l'utilisateur
        'GET' => [
            'controller' => 'App\\Controller\\AdminController',
            'action' => 'deleteEmployee'
        ]
    ],


    // Employés

    '/employe' => [
        'GET' => [
            'controller' => 'App\\Controller\\EmployeeController',
            'action' => 'dashboard'
        ]
    ],



    // Pages statiques
    '/about' => [
        'GET' => [
            'controller' => 'App\\Controller\\PageController',
            'action' => 'about'
        ]
    ],
    '/liste-covoiturages' => [
        'GET' => [
            'controller' => 'App\\Controller\\PageController',
            'action' => 'listeCovoiturages'
        ]
    ],
    '/contact' => [
        'GET' => [
            'controller' => 'App\\Controller\\PageController',
            'action' => 'contact'
        ]
    ],
    '/mes-covoiturages' => [
        'GET' => [
            'controller' => 'App\\Controller\\PageController',
            'action' => 'mesCovoiturages'
        ]
    ],
    '/terms' => [
        'GET' => [
            'controller' => 'App\\Controller\\PageController',
            'action' => 'terms'
        ]
    ],
    '/privacy' => [
        'GET' => [
            'controller' => 'App\\Controller\\PageController',
            'action' => 'privacy'
        ]
    ],

    // Formulaire classique (non-API) de création de covoiturage
    '/covoiturages/create' => [
        'POST' => [
            'controller' => 'App\\Controller\\CovoiturageController',
            'action' => 'create'
        ]
    ],

    // API covoiturages (création minimale)
    '/api/covoiturages/create' => [
        'POST' => [
            'controller' => 'App\\Controller\\CovoiturageController',
            'action' => 'apiCreate'
        ]
    ],

    // Participations
    '/participations/create' => [
        'POST' => [
            'controller' => 'App\\Controller\\ParticipationController',
            'action' => 'create'
        ]
    ],
    '/mes-demandes' => [
        'GET' => [
            'controller' => 'App\\Controller\\ParticipationController',
            'action' => 'driverRequests'
        ]
    ],
    '/participations/accept/{id}' => [
        'POST' => [
            'controller' => 'App\\Controller\\ParticipationController',
            'action' => 'accept'
        ]
    ],
    '/participations/reject/{id}' => [
        'POST' => [
            'controller' => 'App\\Controller\\ParticipationController',
            'action' => 'reject'
        ]
    ]
];
