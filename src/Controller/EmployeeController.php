<?php

namespace App\Controller;




class EmployeeController extends Controller
{

    public function __construct()
    {
        parent::__construct();


        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = "Veuillez vous connecter.";
            redirect('/login');
        }

        if ($_SESSION['user']['role-id'] !== 2) {
            abort(403, "Accès interdit");
        }
    }

    public function dashboard(): void
    {
        
        
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

        $this->render('pages/employee/employee-dashboard', [
            'pendingReviews' => $pendingReviews,
            'problematicTrips' => $problematicTrips
        ]);
    }
}
