<?php

namespace App\Controller;

/**
 * Contrôleur des employés (role_id = 2).
 * - Protège l'accès aux routes en exigeant une session active et un rôle employé.
 * - Expose un tableau de bord avec des données mockées pour l'instant.
 */
class EmployeeController extends Controller
{
    // Initialise les dépendances et applique les gardes d'accès (authentification + rôle employé).     
    public function __construct()
    {
        parent::__construct();

        // Vérifie qu'un utilisateur est authentifié, sinon redirige vers la connexion
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = "Veuillez vous connecter.";
            redirect('/login');
        }

        // Vérifie que l'utilisateur a le rôle employé (role_id = 2)
        if ($_SESSION['user']['role_id'] !== 2) { // ✅ Correction de l'indice
            abort(403, "Accès interdit");
        }
    }

    /**
     * Affiche le tableau de bord employé.
     * Pour le moment, les données sont mockées et devront être remplacées par des requêtes aux repositories.     
     */
    public function dashboard(): void
    {
        // Données mockées: avis en attente de modération
        $pendingReviews = [
            [
                'id' => 1,
                'driver_name' => 'Jean Dupont',
                'comment' => 'Très bon trajet, conducteur sympa',
                'rating' => 5
            ],
            [
                'id' => 2,
                'driver_name' => 'Marie Curie',
                'comment' => 'Un peu en retard mais trajet agréable',
                'rating' => 4
            ]
        ];

        // Données mockées: trajets problématiques signalés
        $problematicTrips = [
            [
                'covoiturage_id' => 101,
                'driver_pseudo' => 'Paul',
                'driver_email' => 'paul@example.com',
                'passenger_pseudo' => 'Alice',
                'passenger_email' => 'alice@example.com',
                'start_location' => 'Paris',
                'end_location' => 'Lyon',
                'start_date' => '2025-08-02 08:30:00',
                'end_date' => '2025-08-02 12:30:00'
            ],
            [
                'covoiturage_id' => 102,
                'driver_pseudo' => 'Luc',
                'driver_email' => 'luc@example.com',
                'passenger_pseudo' => 'Sophie',
                'passenger_email' => 'sophie@example.com',
                'start_location' => 'Marseille',
                'end_location' => 'Nice',
                'start_date' => '2025-08-03 09:00:00',
                'end_date' => '2025-08-03 11:00:00'
            ]
        ];

        // Rendu de la vue du dashboard avec les données
        $this->render('pages/employee/employee-dashboard', [
            'pendingReviews' => $pendingReviews,
            'problematicTrips' => $problematicTrips
        ]);
    }
}
