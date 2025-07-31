<?php


return [
    // Route pour la page d'accueil   
    '/' => [
        'GET' => [
            'controller' => 'App\\Controller\\PageController',
            'action' => 'home'
        ]
    ],
    // Routes pour l'authentification    
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
    // NOUVELLES ROUTES API pour votre modal   
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
    // Routes pour le profil utilisateur   
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
    '/vehicle/delete' => [
        'POST' => [
            'controller' => 'App\\Controller\\VehicleController',
            'action' => 'delete'
        ]
    ],
    '/vehicle/edit' => [
        'POST' => [
            'controller' => 'App\\Controller\\VehicleController',
            'action' => 'update'
        ],
        'GET' => [
            'controller' => 'App\\Controller\\VehicleController',
            'action' => 'edit'
        ]
    ],
    // Routes pour l\'administration
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
        'POST' => [
            'controller' => 'App\\Controller\\AdminController',
            'action' => 'toggleEmployeeStatus'
        ]
    ],
    '/admin/users/delete/{id}' => [
        'GET' => [
            'controller' => 'App\\Controller\\AdminController',
            'action' => 'deleteEmployee'
        ]
    ],


    // Routes employés

    '/employe' => [
        'GET' => [
            'controller' => 'App\\Controller\\EmployeeController',
            'action' => 'dashboard'
        ]
    ],



    // Routes statiques  
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
    ]
];
