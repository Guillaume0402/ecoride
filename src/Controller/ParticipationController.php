<?php

namespace App\Controller;

use App\Repository\CovoiturageRepository;
use App\Repository\ParticipationRepository;
use App\Repository\TransactionRepository;
use App\Security\Csrf;
use App\Service\Flash;

class ParticipationController extends Controller
{
    private ParticipationRepository $participationRepository;
    private CovoiturageRepository $covoiturageRepository;
    private TransactionRepository $transactionRepository;

    public function __construct()
    {
        parent::__construct();
        $this->participationRepository = new ParticipationRepository();
        $this->covoiturageRepository = new CovoiturageRepository();
        $this->transactionRepository = new TransactionRepository();
    }

    // POST /participations/create
    public function create(): void
    {
        if (!isset($_SESSION['user'])) {
            Flash::add('Veuillez vous connecter pour participer.', 'warning');
            redirect('/login');
            return;
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            abort(405);
        }

        if (!Csrf::check($_POST['csrf'] ?? null)) {
            Flash::add('Requête invalide (CSRF).', 'danger');
            redirect('/liste-covoiturages');
            return;
        }

        $userId = (int) $_SESSION['user']['id'];
        // Rôles autorisés: Utilisateur (1), Employé (2), Admin (3)
        $roleId = (int) ($_SESSION['user']['role_id'] ?? 0);
        if (!in_array($roleId, [1, 2, 3], true)) {
            Flash::add('Action non autorisée pour votre rôle.', 'warning');
            redirect('/liste-covoiturages');
            return;
        }
        // Exiger un profil apte à voyager en tant que passager
        try {
            $currentUser = $this->userRepository->findById($userId);
            $travelRole = $currentUser?->getTravelRole() ?? 'passager';
            if (!in_array($travelRole, ['passager', 'les-deux'], true)) {
                Flash::add("Votre profil n'est pas configuré comme passager. Mettez à jour votre rôle de voyage dans votre profil.", 'warning');
                redirect('/creation-profil');
                return;
            }
        } catch (\Throwable $e) {
            error_log('[participations.create] Travel role check failed: ' . $e->getMessage());
        }
        $covoiturageId = (int) ($_POST['covoiturage_id'] ?? 0);
        if ($covoiturageId <= 0) {
            Flash::add('Covoiturage invalide.', 'danger');
            redirect('/liste-covoiturages');
            return;
        }

        // Récup covoiturage + véhicule pour capacité
        $ride = $this->covoiturageRepository->findOneWithVehicleById($covoiturageId);
        if (!$ride) {
            Flash::add('Covoiturage introuvable.', 'danger');
            redirect('/liste-covoiturages');
            return;
        }

        // Interdire au conducteur de participer à son propre trajet
        if ((int)$ride['driver_id'] === $userId) {
            Flash::add('Vous êtes le conducteur de ce trajet.', 'warning');
            redirect('/liste-covoiturages');
            return;
        }

        // Interdire si départ passé
        try {
            $depart = new \DateTime($ride['depart']);
            if ($depart < new \DateTime()) {
                Flash::add('Ce trajet est déjà passé.', 'warning');
                redirect('/liste-covoiturages');
                return;
            }
        } catch (\Throwable $e) {
            // si problème de parsing, sécurité
            Flash::add('Date de départ invalide.', 'danger');
            redirect('/liste-covoiturages');
            return;
        }

        // Sécurité: empêcher de participer à deux trajets qui se chevauchent
        // Si l'utilisateur a déjà une participation CONFIRMÉE dans une fenêtre autour de l'horaire
        // (par défaut ±120 minutes), on bloque la création.
        try {
            if ($this->participationRepository->hasConfirmedConflictAround($userId, $depart, 120)) {
                Flash::add('Vous avez déjà une participation confirmée à proximité de cet horaire. Choisissez un autre trajet.', 'warning');
                redirect('/liste-covoiturages');
                return;
            }
        } catch (\Throwable $e) {
            error_log('[participations.create] conflict check failed: ' . $e->getMessage());
        }

        // Déjà participant ?
        if ($this->participationRepository->findByCovoiturageAndPassager($covoiturageId, $userId)) {
            Flash::add('Vous avez déjà une demande pour ce trajet.', 'info');
            redirect('/liste-covoiturages');
            return;
        }

        // Capacité restante: places du véhicule - participations confirmées
        $placesVehicule = (int)($ride['vehicle_places'] ?? 0);
        $confirmes = $this->participationRepository->countConfirmedByCovoiturageId($covoiturageId);
        $restantes = max(0, $placesVehicule - $confirmes);
        if ($restantes <= 0) {
            Flash::add('Plus aucune place disponible.', 'warning');
            redirect('/liste-covoiturages');
            return;
        }
        // Tente de créer la participation en attente de validation par le conducteur
        $created = $this->participationRepository->create($covoiturageId, $userId, 'en_attente_validation');
        if ($created) {
            // Notifier le conducteur par e-mail
            try {
                $driver = $this->userRepository->findById((int)$ride['driver_id']);
                if ($driver) {
                    $passengerName = htmlspecialchars((string)($_SESSION['user']['pseudo'] ?? 'Un passager'));
                    $when = htmlspecialchars(date('d/m/Y H:i', strtotime((string)$ride['depart'])));
                    $trajet = htmlspecialchars((string)$ride['adresse_depart'] . ' → ' . (string)$ride['adresse_arrivee']);
                    $link = SITE_URL . 'mes-demandes';
                    $subject = 'Nouvelle demande de participation pour votre trajet #' . $covoiturageId;
                    $body = '<p>Bonjour ' . htmlspecialchars($driver->getPseudo()) . ',</p>'
                        . '<p><strong>' . $passengerName . '</strong> souhaite participer à votre trajet :</p>'
                        . '<ul>'
                        . '<li>Trajet : ' . $trajet . '</li>'
                        . '<li>Départ : ' . $when . '</li>'
                        . '</ul>'
                        . '<p>Vous pouvez accepter ou refuser la demande depuis votre tableau de bord : <a href="' . htmlspecialchars($link) . '">Mes demandes</a>.</p>'
                        . '<p>— L’équipe EcoRide</p>';
                    (new \App\Service\Mailer())->send($driver->getEmail(), $subject, $body);
                }
            } catch (\Throwable $e) {
                error_log('[participations.create] Mail to driver failed: ' . $e->getMessage());
            }
            Flash::add('Votre demande a été envoyée au conducteur. Vous serez notifié(e) après sa réponse.', 'success');
            redirect('/mes-covoiturages');
            return;
        }

        Flash::add('Une erreur est survenue lors de la création de la demande.', 'danger');
        redirect('/liste-covoiturages');
        return;
    }

    // GET /mes-demandes : liste des demandes en attente pour les trajets du conducteur
    public function driverRequests(): void
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
            return;
        }
        $userId = (int) $_SESSION['user']['id'];
        $pending = $this->participationRepository->findPendingByDriverId($userId);
        $this->render('pages/mes-demandes', [
            'pending' => $pending
        ]);
    }

    // POST /participations/accept/{id}
    public function accept(int $id): void
    {
        $this->handleStatusChange($id, 'confirmee');
    }

    // POST /participations/reject/{id}
    public function reject(int $id): void
    {
        $this->handleStatusChange($id, 'annulee');
    }

    private function handleStatusChange(int $participationId, string $newStatus): void
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
            return;
        }
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            abort(405);
        }
        if (!Csrf::check($_POST['csrf'] ?? null)) {
            Flash::add('Requête invalide (CSRF).', 'danger');
            redirect('/mes-demandes');
            return;
        }

        $userId = (int) $_SESSION['user']['id'];
        $p = $this->participationRepository->findWithCovoiturageById($participationId);
        if (!$p) {
            Flash::add('Participation introuvable.', 'danger');
            redirect('/mes-demandes');
            return;
        }
        // Autorisation: uniquement le conducteur du covoiturage
        if ((int)($p['driver_user_id'] ?? 0) !== $userId) {
            Flash::add('Action non autorisée.', 'danger');
            redirect('/mes-demandes');
            return;
        }
        // Si on confirme, vérifier la capacité et débiter maintenant le passager
        if ($newStatus === 'confirmee') {
            $placesVehicule = (int)($p['vehicle_places'] ?? 0);
            $confirmes = $this->participationRepository->countConfirmedByCovoiturageId((int)$p['covoiturage_id']);
            $restantes = max(0, $placesVehicule - $confirmes);
            if ($restantes <= 0) {
                Flash::add('Plus de place disponible pour confirmer.', 'warning');
                redirect('/mes-demandes');
                return;
            }

            // Débiter le prix (arrondi) au passager avant de confirmer
            $passagerId = (int)($p['passager_id'] ?? 0);
            $prix = (float)($p['prix'] ?? 0);
            $cost = max(1, (int) ceil($prix));
            if (!$this->userRepository->debitIfEnough($passagerId, $cost)) {
                Flash::add('Crédits insuffisants pour confirmer. Demandez au passager de recharger son solde.', 'warning');
                redirect('/mes-demandes');
                return;
            }
            // Journaliser la transaction
            $this->transactionRepository->create($passagerId, $cost, 'debit', 'Participation trajet #' . (int)$p['covoiturage_id']);
            // Rafraîchir le solde éventuel en session si c'est l'utilisateur courant
            if (!empty($_SESSION['user']) && (int)$_SESSION['user']['id'] === $passagerId) {
                $u = $this->userRepository->findById($passagerId);
                if ($u) {
                    $_SESSION['user']['credits'] = $u->getCredits();
                }
            }
        }

        if ($this->participationRepository->updateStatus($participationId, $newStatus)) {
            $msg = $newStatus === 'confirmee' ? 'Participation confirmée.' : 'Demande refusée.';
            Flash::add($msg, 'success');
            // Notification e-mail côté passager selon la décision
            try {
                $passenger = $this->userRepository->findById((int)$p['passager_id']);
                if ($passenger) {
                    $driverName = htmlspecialchars((string)($_SESSION['user']['pseudo'] ?? 'Le conducteur'));
                    $trajet = htmlspecialchars((string)$p['adresse_depart'] . ' → ' . (string)$p['adresse_arrivee']);
                    $when = htmlspecialchars(date('d/m/Y H:i', strtotime((string)$p['depart'])));
                    $link = SITE_URL . 'mes-covoiturages';
                    if ($newStatus === 'confirmee') {
                        $subject = 'Votre participation a été acceptée';
                        $body = '<p>Bonjour ' . htmlspecialchars($passenger->getPseudo()) . ',</p>'
                            . '<p>Votre demande de participation a été <strong>acceptée</strong> par ' . $driverName . '.</p>'
                            . '<ul><li>Trajet : ' . $trajet . '</li><li>Départ : ' . $when . '</li></ul>'
                            . '<p>Consultez vos trajets : <a href="' . htmlspecialchars($link) . '">Mes covoiturages</a>.</p>'
                            . '<p>— L’équipe EcoRide</p>';
                    } else {
                        $subject = 'Votre participation a été refusée';
                        $body = '<p>Bonjour ' . htmlspecialchars($passenger->getPseudo()) . ',</p>'
                            . '<p>Votre demande de participation a été <strong>refusée</strong> par ' . $driverName . '.</p>'
                            . '<ul><li>Trajet : ' . $trajet . '</li><li>Départ : ' . $when . '</li></ul>'
                            . '<p>Vous pouvez rechercher un autre trajet sur EcoRide.</p>'
                            . '<p>— L’équipe EcoRide</p>';
                    }
                    (new \App\Service\Mailer())->send($passenger->getEmail(), $subject, $body);
                }
            } catch (\Throwable $e) {
                error_log('[participations.statusChange] Mail to passenger failed: ' . $e->getMessage());
            }
        } else {
            Flash::add('Mise à jour impossible.', 'danger');
        }
        redirect('/mes-demandes');
        return;
    }

    // POST /participations/validate/{id}
    public function validateTrip(int $id): void
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
            return;
        }
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            abort(405);
        }
        if (!Csrf::check($_POST['csrf'] ?? null)) {
            Flash::add('Requête invalide (CSRF).', 'danger');
            redirect('/mes-covoiturages');
            return;
        }

        $userId = (int) $_SESSION['user']['id'];
        $p = $this->participationRepository->findWithCovoiturageById($id);
        if (!$p) {
            Flash::add('Participation introuvable.', 'danger');
            redirect('/mes-covoiturages');
            return;
        }
        if ((int)$p['passager_id'] !== $userId) {
            Flash::add('Action non autorisée.', 'danger');
            redirect('/mes-covoiturages');
            return;
        }
        // Exiger que le covoiturage soit terminé
        $covoitStatus = (string)($p['covoit_status'] ?? '');
        if ($covoitStatus !== 'termine') {
            Flash::add('Ce trajet n\'est pas encore terminé.', 'warning');
            redirect('/mes-covoiturages');
            return;
        }

        $driverId = (int)($p['driver_user_id'] ?? 0);
        $covoiturageId = (int)($p['covoiturage_id'] ?? 0);
        $prix = (float)($p['prix'] ?? 0);
        $amount = max(1, (int) ceil($prix));

        // Idempotence via motif unique (conducteur + trajet + passager)
        $motif = 'Crédit conducteur trajet #' . $covoiturageId . ' - passager #' . $userId;
        $txRepo = $this->transactionRepository;
        if (!$txRepo->existsForMotif($driverId, $motif)) {
            // Créditer le conducteur
            if ($this->userRepository->credit($driverId, $amount)) {
                $txRepo->create($driverId, $amount, 'credit', $motif);
            } else {
                error_log('[validateTrip] Echec crédit conducteur ' . $driverId);
            }
        }

        // Stocker un éventuel avis dans Mongo
        $rating = isset($_POST['rating']) ? (int) $_POST['rating'] : null;
        $comment = trim((string)($_POST['comment'] ?? ''));
        if (($rating !== null && $rating >= 1 && $rating <= 5) || $comment !== '') {
            try {
                $reviewSvc = new \App\Service\ReviewService();
                $reviewSvc->addReview([
                    'covoiturage_id' => $covoiturageId,
                    'driver_id' => $driverId,
                    'passager_id' => $userId,
                    'rating' => $rating,
                    'comment' => $comment,
                ]);
            } catch (\Throwable $e) {
                error_log('[validateTrip] Mongo review failed: ' . $e->getMessage());
            }
        }

        Flash::add('Merci pour votre validation. Le conducteur a été crédité.', 'success');
        redirect('/mes-covoiturages');
        return;
    }

    // GET /participations/validate/{id}
    public function showValidationForm(int $id): void
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
            return;
        }
        $userId = (int) $_SESSION['user']['id'];
        $p = $this->participationRepository->findWithCovoiturageById($id);
        if (!$p) {
            Flash::add('Participation introuvable.', 'danger');
            redirect('/mes-covoiturages');
            return;
        }
        if ((int)$p['passager_id'] !== $userId) {
            Flash::add('Action non autorisée.', 'danger');
            redirect('/mes-covoiturages');
            return;
        }
        if (($p['covoit_status'] ?? '') !== 'termine') {
            Flash::add('Ce trajet n\'est pas encore terminé.', 'warning');
            redirect('/mes-covoiturages');
            return;
        }

        $this->render('pages/participations/validate', [
            'p' => $p,
        ]);
    }

    // POST /participations/report/{id}
    public function reportIssue(int $id): void
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
            return;
        }
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            abort(405);
        }
        if (!Csrf::check($_POST['csrf'] ?? null)) {
            Flash::add('Requête invalide (CSRF).', 'danger');
            redirect('/mes-covoiturages');
            return;
        }

        $userId = (int) $_SESSION['user']['id'];
        $p = $this->participationRepository->findWithCovoiturageById($id);
        if (!$p) {
            Flash::add('Participation introuvable.', 'danger');
            redirect('/mes-covoiturages');
            return;
        }
        if ((int)$p['passager_id'] !== $userId) {
            Flash::add('Action non autorisée.', 'danger');
            redirect('/mes-covoiturages');
            return;
        }
        $covoitStatus = (string)($p['covoit_status'] ?? '');
        if ($covoitStatus !== 'termine') {
            Flash::add('Vous pourrez signaler un problème quand le trajet sera terminé.', 'warning');
            redirect('/mes-covoiturages');
            return;
        }

        $driverId = (int)($p['driver_user_id'] ?? 0);
        $covoiturageId = (int)($p['covoiturage_id'] ?? 0);
        $reason = trim((string)($_POST['reason'] ?? ''));
        $comment = trim((string)($_POST['comment'] ?? ''));
        try {
            $reviewSvc = new \App\Service\ReviewService();
            $reviewSvc->addReport([
                'covoiturage_id' => $covoiturageId,
                'driver_id' => $driverId,
                'passager_id' => $userId,
                'reason' => $reason,
                'comment' => $comment,
            ]);
        } catch (\Throwable $e) {
            error_log('[reportIssue] Mongo report failed: ' . $e->getMessage());
        }

        Flash::add('Merci, votre signalement a été transmis à nos équipes.', 'success');
        redirect('/mes-covoiturages');
        return;
    }
}
