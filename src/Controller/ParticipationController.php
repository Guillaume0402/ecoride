<?php

namespace App\Controller;

use App\Repository\CovoiturageRepository;
use App\Repository\ParticipationRepository;
use App\Repository\TransactionRepository;
use App\Repository\UserRepository;
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
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            abort(405);
        }

        if (!Csrf::check($_POST['csrf'] ?? null)) {
            Flash::add('Requête invalide (CSRF).', 'danger');
            redirect('/liste-covoiturages');
        }

        $userId = (int) $_SESSION['user']['id'];
        // Rôle autorisé: uniquement "Utilisateur" (role_id = 1)
        $roleId = (int) ($_SESSION['user']['role_id'] ?? 0);
        if ($roleId !== 1) {
            Flash::add('Cette action est réservée aux utilisateurs.', 'warning');
            redirect('/liste-covoiturages');
        }
        $covoiturageId = (int) ($_POST['covoiturage_id'] ?? 0);
        if ($covoiturageId <= 0) {
            Flash::add('Covoiturage invalide.', 'danger');
            redirect('/liste-covoiturages');
        }

        // Récup covoiturage + véhicule pour capacité
        $ride = $this->covoiturageRepository->findOneWithVehicleById($covoiturageId);
        if (!$ride) {
            Flash::add('Covoiturage introuvable.', 'danger');
            redirect('/liste-covoiturages');
        }

        // Interdire au conducteur de participer à son propre trajet
        if ((int)$ride['driver_id'] === $userId) {
            Flash::add('Vous êtes le conducteur de ce trajet.', 'warning');
            redirect('/liste-covoiturages');
        }

        // Interdire si départ passé
        try {
            $depart = new \DateTime($ride['depart']);
            if ($depart < new \DateTime()) {
                Flash::add('Ce trajet est déjà passé.', 'warning');
                redirect('/liste-covoiturages');
            }
        } catch (\Throwable $e) {
            // si problème de parsing, sécurité
            Flash::add('Date de départ invalide.', 'danger');
            redirect('/liste-covoiturages');
        }

        // Déjà participant ?
        if ($this->participationRepository->findByCovoiturageAndPassager($covoiturageId, $userId)) {
            Flash::add('Vous avez déjà une demande pour ce trajet.', 'info');
            redirect('/liste-covoiturages');
        }

        // Capacité restante: places du véhicule - participations confirmées
        $placesVehicule = (int)($ride['vehicle_places'] ?? 0);
        $confirmes = $this->participationRepository->countConfirmedByCovoiturageId($covoiturageId);
        $restantes = max(0, $placesVehicule - $confirmes);
        if ($restantes <= 0) {
            Flash::add('Plus aucune place disponible.', 'warning');
            redirect('/liste-covoiturages');
        }
        // Coût en crédits: arrondi du prix (au moins 1 si prix>0)
        $prix = (float) ($ride['prix'] ?? 0);
        $cost = max(1, (int) ceil($prix));

        // Débit serveur (atomicité approximative)
        if (!$this->userRepository->debitIfEnough($userId, $cost)) {
            Flash::add('Crédits insuffisants pour participer.', 'warning');
            redirect('/mes-credits');
        }
        // Journalise la transaction de débit
        $this->transactionRepository->create($userId, $cost, 'debit', 'Participation trajet #' . $covoiturageId);

        // Tente de créer la participation confirmée
        $created = $this->participationRepository->create($covoiturageId, $userId, 'confirmee');
        if ($created) {
            // Rafraîchit crédits en session
            $u = $this->userRepository->findById($userId);
            if ($u) {
                $_SESSION['user']['credits'] = $u->getCredits();
            }
            Flash::add('Participation confirmée. Bonne route !', 'success');
            redirect('/mes-covoiturages');
        }

        // Échec après débit → remboursement simple
        $this->userRepository->credit($userId, $cost);
        $this->transactionRepository->create($userId, $cost, 'credit', 'Remboursement: échec participation');
        Flash::add('Une erreur est survenue. Aucun crédit n’a été débité.', 'danger');
        redirect('/liste-covoiturages');
    }

    // GET /mes-demandes : liste des demandes en attente pour les trajets du conducteur
    public function driverRequests(): void
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
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
        }
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            abort(405);
        }
        if (!Csrf::check($_POST['csrf'] ?? null)) {
            Flash::add('Requête invalide (CSRF).', 'danger');
            redirect('/mes-demandes');
        }

        $userId = (int) $_SESSION['user']['id'];
        $p = $this->participationRepository->findWithCovoiturageById($participationId);
        if (!$p) {
            Flash::add('Participation introuvable.', 'danger');
            redirect('/mes-demandes');
        }
        // Autorisation: uniquement le conducteur du covoiturage
        if ((int)($p['driver_user_id'] ?? 0) !== $userId) {
            Flash::add('Action non autorisée.', 'danger');
            redirect('/mes-demandes');
        }
        // Si on confirme, vérifier la capacité
        if ($newStatus === 'confirmee') {
            $placesVehicule = (int)($p['vehicle_places'] ?? 0);
            $confirmes = $this->participationRepository->countConfirmedByCovoiturageId((int)$p['covoiturage_id']);
            $restantes = max(0, $placesVehicule - $confirmes);
            if ($restantes <= 0) {
                Flash::add('Plus de place disponible pour confirmer.', 'warning');
                redirect('/mes-demandes');
            }

            // Débiter le prix (arrondi) au passager avant de confirmer
            $passagerId = (int)($p['passager_id'] ?? 0);
            $prix = (float)($p['prix'] ?? 0);
            $cost = max(1, (int) ceil($prix));
            if (!$this->userRepository->debitIfEnough($passagerId, $cost)) {
                Flash::add('Crédits insuffisants pour confirmer.', 'warning');
                redirect('/mes-demandes');
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
        } else {
            Flash::add('Mise à jour impossible.', 'danger');
        }
        redirect('/mes-demandes');
    }

    // POST /participations/validate/{id}
    public function validateTrip(int $id): void
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
        }
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            abort(405);
        }
        if (!\App\Security\Csrf::check($_POST['csrf'] ?? null)) {
            Flash::add('Requête invalide (CSRF).', 'danger');
            redirect('/mes-covoiturages');
        }

        $userId = (int) $_SESSION['user']['id'];
        $p = $this->participationRepository->findWithCovoiturageById($id);
        if (!$p) {
            Flash::add('Participation introuvable.', 'danger');
            redirect('/mes-covoiturages');
        }
        if ((int)$p['passager_id'] !== $userId) {
            Flash::add('Action non autorisée.', 'danger');
            redirect('/mes-covoiturages');
        }
        // Exiger que le covoiturage soit terminé
        $covoitStatus = (string)($p['covoit_status'] ?? '');
        if ($covoitStatus !== 'termine') {
            Flash::add('Ce trajet n\'est pas encore terminé.', 'warning');
            redirect('/mes-covoiturages');
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
    }

    // POST /participations/report/{id}
    public function reportIssue(int $id): void
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
        }
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            abort(405);
        }
        if (!\App\Security\Csrf::check($_POST['csrf'] ?? null)) {
            Flash::add('Requête invalide (CSRF).', 'danger');
            redirect('/mes-covoiturages');
        }

        $userId = (int) $_SESSION['user']['id'];
        $p = $this->participationRepository->findWithCovoiturageById($id);
        if (!$p) {
            Flash::add('Participation introuvable.', 'danger');
            redirect('/mes-covoiturages');
        }
        if ((int)$p['passager_id'] !== $userId) {
            Flash::add('Action non autorisée.', 'danger');
            redirect('/mes-covoiturages');
        }
        $covoitStatus = (string)($p['covoit_status'] ?? '');
        if ($covoitStatus !== 'termine') {
            Flash::add('Vous pourrez signaler un problème quand le trajet sera terminé.', 'warning');
            redirect('/mes-covoiturages');
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
    }
}
