<?php

// Déclaration de la table des routes HTTP de l'application
return [

    // Page d'accueil (liste/landing principale)
    '/' => [
        'GET' => [
            'controller' => 'App\\Controller\\PageController',
            'action' => 'home'
        ]
    ],
    // Authentification (pages HTML)
    '/login' => [
        'GET' => [
            'controller' => 'App\\Controller\\AuthController',
            'action' => 'showLogin'
        ]
    ],
    // Déconnexion de l'utilisateur connecté
    '/logout' => [
        'GET' => [
            'controller' => 'App\\Controller\\AuthController',
            'action' => 'logout'
        ]
    ],
    // API Auth (utilisées par la modale de connexion/inscription)
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
    // Vérification du lien de confirmation d'email
    '/verify-email' => [
        'GET' => [
            'controller' => 'App\\Controller\\AuthController',
            'action' => 'verifyEmail'
        ]
    ],
    // Profil de l'utilisateur actuellement connecté (espace privé)
    '/my-profil' => [
        'GET' => [
            'controller' => 'App\\Controller\\PageController',
            'action' => 'profil'
        ]
    ],
    // Profil public d'un utilisateur (lecture seule, accessible par id)
    '/profil/{id}' => [
        'GET' => [
            'controller' => 'App\\Controller\\PageController',
            'action' => 'showUserProfil'
        ]
    ],
    // Création ou mise à jour d'un profil utilisateur
    '/creation-profil' => [
        // Affiche le formulaire de création/modification de profil
        'GET' => [
            'controller' => 'App\\Controller\\PageController',
            'action' => 'creationProfil'
        ],
        // Traite la soumission du formulaire de profil
        'POST' => [
            'controller' => 'App\\Controller\\ProfilController',
            'action' => 'update'
        ]
    ],

    // Gestion du véhicule principal de l'utilisateur
    '/vehicle/create' => [
        // Afficher le formulaire vide de création de véhicule
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
    // Traiter la suppression du véhicule de l'utilisateur
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

    // Espace d'administration (réservé aux admins)
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
    // Liste et gestion des utilisateurs/employés
    '/admin/users' => [
        'GET' => [
            'controller' => 'App\\Controller\\AdminController',
            'action' => 'users'
        ]
    ],
    // Statistiques d'utilisation de la plateforme
    '/admin/stats' => [
        'GET' => [
            'controller' => 'App\\Controller\\AdminController',
            'action' => 'stats'
        ]
    ],
    // Liste des covoiturages pour supervision admin
    '/admin/covoiturages' => [
        'GET' => [
            'controller' => 'App\\Controller\\AdminController',
            'action' => 'covoiturages'
        ]
    ],
    // Création d'un nouvel employé depuis le back-office admin
    '/admin/users/create' => [
        'POST' => [
            'controller' => 'App\\Controller\\AdminController',
            'action' => 'createEmployee'
        ]
    ],
    // Activation/désactivation d'un employé par son id
    '/admin/users/toggle/{id}' => [
        // {id} = identifiant numérique de l'utilisateur ciblé
        'POST' => [
            'controller' => 'App\\Controller\\AdminController',
            'action' => 'toggleEmployeeStatus'
        ]
    ],
    // Suppression d'un employé par son id
    '/admin/users/delete/{id}' => [
        // {id} = identifiant numérique de l'utilisateur ciblé
        'GET' => [
            'controller' => 'App\\Controller\\AdminController',
            'action' => 'deleteEmployee'
        ]
    ],

    // Espace employé (interface interne de modération/gestion)
    '/employe' => [
        'GET' => [
            'controller' => 'App\\Controller\\EmployeeController',
            'action' => 'dashboard'
        ]
    ],
    // Validation ou rejet d'un avis par un employé
    '/employee/review/validate' => [
        'POST' => [
            'controller' => 'App\\Controller\\EmployeeController',
            'action' => 'validateReview'
        ]
    ],

    // (Point de debug Mongo retiré en prod; utiliser les scripts CLI sous scripts/)



    // Pages statiques (présentation, contenu légal, etc.)
    '/about' => [
        'GET' => [
            'controller' => 'App\\Controller\\PageController',
            'action' => 'about'
        ]
    ],
    '/qui-sommes-nous' => [
        'GET' => [
            'controller' => 'App\\Controller\\PageController',
            'action' => 'about'
        ]
    ],
    // Liste publique des covoiturages disponibles
    '/liste-covoiturages' => [
        'GET' => [
            'controller' => 'App\\Controller\\PageController',
            'action' => 'listeCovoiturages'
        ]
    ],
    // Page de contact (formulaire ou informations)
    '/contact' => [
        'GET' => [
            'controller' => 'App\\Controller\\PageController',
            'action' => 'contact'
        ],
        'POST' => [
            'controller' => 'App\\Controller\\PageController',
            'action' => 'contactSend'
        ],
    ],
    // Liste des covoiturages créés ou réservés par l'utilisateur
    '/mes-covoiturages' => [
        'GET' => [
            'controller' => 'App\\Controller\\PageController',
            'action' => 'mesCovoiturages'
        ]
    ],
    // Page de consultation des crédits de l'utilisateur
    '/mes-credits' => [
        'GET' => [
            'controller' => 'App\\Controller\\PageController',
            'action' => 'mesCredits'
        ]
    ],
    // Conditions générales d'utilisation
    '/terms' => [
        'GET' => [
            'controller' => 'App\\Controller\\PageController',
            'action' => 'terms'
        ]
    ],
    // Politique de confidentialité
    '/privacy' => [
        'GET' => [
            'controller' => 'App\\Controller\\PageController',
            'action' => 'privacy'
        ]
    ],
    // Mentions légales obligatoires
    '/mentions-legales' => [
        'GET' => [
            'controller' => 'App\\Controller\\PageController',
            'action' => 'mentionsLegales'
        ]
    ],

    // Test d'envoi d'e-mail (dev uniquement)
    '/mail/test' => [
        'GET' => [
            'controller' => 'App\\Controller\\MailController',
            'action' => 'testForm'
        ],
        'POST' => [
            'controller' => 'App\\Controller\\MailController',
            'action' => 'sendTest'
        ]
    ],

    // Formulaire classique (non-API) de création de covoiturage
    '/covoiturages/create' => [
        'POST' => [
            'controller' => 'App\\Controller\\CovoiturageController',
            'action' => 'create'
        ]
    ],

    // API covoiturages (création minimale via JSON ou AJAX)
    '/api/covoiturages/create' => [
        'POST' => [
            'controller' => 'App\\Controller\\CovoiturageController',
            'action' => 'apiCreate'
        ]
    ],

    // Page de détail d'un covoiturage (accessible par id)
    '/covoiturages/{id}' => [
        'GET' => [
            'controller' => 'App\\Controller\\PageController',
            'action' => 'showCovoiturage'
        ]
    ],

    // Annulation d'un covoiturage par son conducteur
    '/covoiturages/cancel/{id}' => [
        'POST' => [
            'controller' => 'App\\Controller\\CovoiturageController',
            'action' => 'cancel'
        ]
    ],

    // Démarrer un covoiturage (conducteur)
    '/covoiturages/start/{id}' => [
        'POST' => [
            'controller' => 'App\\Controller\\CovoiturageController',
            'action' => 'start'
        ]
    ],
    // Terminer un covoiturage (conducteur)
    '/covoiturages/finish/{id}' => [
        'POST' => [
            'controller' => 'App\\Controller\\CovoiturageController',
            'action' => 'finish'
        ]
    ],

    // Participations passagers à un covoiturage
    '/participations/create' => [
        'POST' => [
            'controller' => 'App\\Controller\\ParticipationController',
            'action' => 'create'
        ]
    ],
    // Vue des demandes de participation reçues par le conducteur
    '/mes-demandes' => [
        'GET' => [
            'controller' => 'App\\Controller\\ParticipationController',
            'action' => 'driverRequests'
        ]
    ],
    // Acceptation d'une demande de participation
    '/participations/accept/{id}' => [
        'POST' => [
            'controller' => 'App\\Controller\\ParticipationController',
            'action' => 'accept'
        ]
    ],
    // Rejet d'une demande de participation
    '/participations/reject/{id}' => [
        'POST' => [
            'controller' => 'App\\Controller\\ParticipationController',
            'action' => 'reject'
        ]
    ],
    // Validation/Signalement par le passager après fin de trajet
    '/participations/validate/{id}' => [
        'GET' => [
            'controller' => 'App\\Controller\\ParticipationController',
            'action' => 'showValidationForm'
        ],
        'POST' => [
            'controller' => 'App\\Controller\\ParticipationController',
            'action' => 'validateTrip'
        ]
    ],
    // Signalement d'un problème sur un trajet par le passager
    '/participations/report/{id}' => [
        'POST' => [
            'controller' => 'App\\Controller\\ParticipationController',
            'action' => 'reportIssue'
        ]
    ]
];
