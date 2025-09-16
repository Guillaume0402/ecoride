<?php

namespace App\Controller;

use App\Service\Flash;

// Contrôleur employé (role_id = 2): gardes d'accès + dashboard
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

    // Dashboard employé (modération Mongo)
    public function dashboard(): void
    {
        $pendingReviews = [];
        $problematicTrips = [];
        try {
            $mod = new \App\Service\ReviewModerationService();
            $pending = $mod->listPending();
            // Sépare reviews et reports si on veut afficher deux sections distinctes
            foreach ($pending as $doc) {
                if (($doc['kind'] ?? '') === 'report') {
                    $problematicTrips[] = [
                        'id' => $doc['id'] ?? '',
                        'covoiturage_id' => $doc['covoiturage_id'] ?? null,
                        'driver_id' => $doc['driver_id'] ?? null,
                        'passager_id' => $doc['passager_id'] ?? null,
                        'reason' => $doc['reason'] ?? '',
                        'comment' => $doc['comment'] ?? '',
                        'created_at_ms' => $doc['created_at_ms'] ?? null,
                    ];
                } else {
                    $pendingReviews[] = [
                        'id' => $doc['id'] ?? '',
                        'covoiturage_id' => $doc['covoiturage_id'] ?? null,
                        'driver_id' => $doc['driver_id'] ?? null,
                        'passager_id' => $doc['passager_id'] ?? null,
                        'comment' => $doc['comment'] ?? '',
                        'rating' => $doc['rating'] ?? null,
                        'created_at_ms' => $doc['created_at_ms'] ?? null,
                    ];
                }
            }
        } catch (\Throwable $e) {
            error_log('[EmployeeController::dashboard] ' . $e->getMessage());
        }

        // Rendu de la vue du dashboard avec les données
        $this->render('pages/employee/employee-dashboard', [
            'pendingReviews' => $pendingReviews,
            'problematicTrips' => $problematicTrips
        ]);
    }

    // POST /employee/review/validate
    public function validateReview(): void
    {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role_id'] !== 2) {
            abort(403);
        }
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            abort(405);
        }
        if (!\App\Security\Csrf::check($_POST['csrf'] ?? null)) {
            Flash::add('Requête invalide (CSRF).', 'danger');
            redirect('/employe');
        }
        $id = (string)($_POST['review_id'] ?? '');
        $action = (string)($_POST['action'] ?? 'reject');
        try {
            $mod = new \App\Service\ReviewModerationService();
            $ok = $mod->updateStatus($id, $action === 'approve' ? 'approved' : 'rejected');
            Flash::add($ok ? 'Décision enregistrée.' : 'Aucune mise à jour.', $ok ? 'success' : 'warning');
        } catch (\Throwable $e) {
            error_log('[EmployeeController::validateReview] ' . $e->getMessage());
            Flash::add('Erreur lors de la mise à jour.', 'danger');
        }
        redirect('/employe');
    }
}
