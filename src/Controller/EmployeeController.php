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
        if ($_SESSION['user']['role_id'] !== 2) { // Correction de l'indice
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
            // Cache simple pour éviter des requêtes répétées
            $userNameCache = [];
            $getName = function (?int $uid) use (&$userNameCache) {
                if (!$uid) return null;
                if (isset($userNameCache[$uid])) return $userNameCache[$uid];
                $u = $this->userRepository->findById($uid);
                $name = $u ? $u->getPseudo() : null;
                $userNameCache[$uid] = $name;
                return $name;
            };
            // Sépare reviews et reports, et enrichit avec infos trajet (si dispo)
            $covoitRepo = new \App\Repository\CovoiturageRepository();
            foreach ($pending as $doc) {
                if (($doc['kind'] ?? '') === 'report') {
                    $ride = null;
                    if (!empty($doc['covoiturage_id'])) {
                        $ride = $covoitRepo->findOneWithVehicleById((int)$doc['covoiturage_id']);
                    }
                    $problematicTrips[] = [
                        'id' => $doc['id'] ?? '',
                        'covoiturage_id' => $doc['covoiturage_id'] ?? null,
                        'driver_id' => $doc['driver_id'] ?? null,
                        'passager_id' => $doc['passager_id'] ?? null,
                        'driver_name' => $getName(isset($doc['driver_id']) ? (int)$doc['driver_id'] : null),
                        'passager_name' => $getName(isset($doc['passager_id']) ? (int)$doc['passager_id'] : null),
                        'adresse_depart' => $ride['adresse_depart'] ?? null,
                        'adresse_arrivee' => $ride['adresse_arrivee'] ?? null,
                        'depart_at' => $ride['depart'] ?? null,
                        'vehicle_marque' => $ride['vehicle_marque'] ?? null,
                        'vehicle_modele' => $ride['vehicle_modele'] ?? null,
                        'vehicle_immatriculation' => $ride['vehicle_immatriculation'] ?? null,
                        'reason' => $doc['reason'] ?? '',
                        'comment' => $doc['comment'] ?? '',
                        'created_at_ms' => $doc['created_at_ms'] ?? null,
                    ];
                } else {
                    $ride = null;
                    if (!empty($doc['covoiturage_id'])) {
                        $ride = $covoitRepo->findOneWithVehicleById((int)$doc['covoiturage_id']);
                    }
                    $pendingReviews[] = [
                        'id' => $doc['id'] ?? '',
                        'covoiturage_id' => $doc['covoiturage_id'] ?? null,
                        'driver_id' => $doc['driver_id'] ?? null,
                        'passager_id' => $doc['passager_id'] ?? null,
                        'driver_name' => $getName(isset($doc['driver_id']) ? (int)$doc['driver_id'] : null),
                        'passager_name' => $getName(isset($doc['passager_id']) ? (int)$doc['passager_id'] : null),
                        'adresse_depart' => $ride['adresse_depart'] ?? null,
                        'adresse_arrivee' => $ride['adresse_arrivee'] ?? null,
                        'depart_at' => $ride['depart'] ?? null,
                        'vehicle_marque' => $ride['vehicle_marque'] ?? null,
                        'vehicle_modele' => $ride['vehicle_modele'] ?? null,
                        'vehicle_immatriculation' => $ride['vehicle_immatriculation'] ?? null,
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
        if ($id === '') {
            Flash::add('Avis introuvable (ID manquant).', 'danger');
            redirect('/employe');
        }
        $action = (string)($_POST['action'] ?? 'reject');
        try {
            $mod = new \App\Service\ReviewModerationService();
            // Charger le document pour connaître son type et les acteurs
            $doc = $mod->getById($id);
            if (!$doc) {
                Flash::add('Avis introuvable ou déjà traité.', 'warning');
                redirect('/employe');
            }
            $decision = $action === 'approve' ? 'approved' : 'rejected';
            $ok = $mod->updateStatus($id, $decision);

            // Si un report est approuvé → notifier conducteur et passager par email
            if ($ok && $decision === 'approved' && is_array($doc) && (($doc['kind'] ?? '') === 'report')) {
                try {
                    $driverId = (int)($doc['driver_id'] ?? 0);
                    $passagerId = (int)($doc['passager_id'] ?? 0);
                    $covoiturageId = (int)($doc['covoiturage_id'] ?? 0);
                    $mailer = new \App\Service\Mailer();

                    if ($driverId > 0) {
                        $driver = $this->userRepository->findById($driverId);
                        if ($driver) {
                            $subject = 'Signalement approuvé — trajet #' . $covoiturageId;
                            $body = '<p>Bonjour ' . htmlspecialchars($driver->getPseudo()) . ',</p>'
                                . '<p>Un signalement concernant votre trajet #' . $covoiturageId . ' a été approuvé par nos équipes.</p>'
                                . '<p>Motif: ' . htmlspecialchars((string)($doc['reason'] ?? '')) . '</p>'
                                . '<p>Commentaire: ' . htmlspecialchars((string)($doc['comment'] ?? '')) . '</p>'
                                . '<p>Notre support peut revenir vers vous si nécessaire.</p>'
                                . '<p>— L\'équipe EcoRide</p>';
                            $mailer->send($driver->getEmail(), $subject, $body);
                        }
                    }
                    if ($passagerId > 0) {
                        $passager = $this->userRepository->findById($passagerId);
                        if ($passager) {
                            $subject = 'Votre signalement a été approuvé — trajet #' . $covoiturageId;
                            $body = '<p>Bonjour ' . htmlspecialchars($passager->getPseudo()) . ',</p>'
                                . '<p>Votre signalement concernant le trajet #' . $covoiturageId . ' a été approuvé.</p>'
                                . '<p>Motif: ' . htmlspecialchars((string)($doc['reason'] ?? '')) . '</p>'
                                . '<p>Commentaire: ' . htmlspecialchars((string)($doc['comment'] ?? '')) . '</p>'
                                . '<p>Notre support peut revenir vers vous si nécessaire.</p>'
                                . '<p>— L\'équipe EcoRide</p>';
                            $mailer->send($passager->getEmail(), $subject, $body);
                        }
                    }
                } catch (\Throwable $e) {
                    error_log('[EmployeeController::validateReview notify] ' . $e->getMessage());
                }
            }

            Flash::add($ok ? 'Décision enregistrée.' : 'Aucune mise à jour (élément introuvable ou déjà traité).', $ok ? 'success' : 'warning');
        } catch (\Throwable $e) {
            error_log('[EmployeeController::validateReview] ' . $e->getMessage());
            Flash::add('Erreur lors de la mise à jour.', 'danger');
        }
        redirect('/employe');
    }
}
