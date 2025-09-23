<?php

namespace App\Controller;

use App\Repository\VehicleRepository;
use App\Repository\CovoiturageRepository;
use App\Repository\ParticipationRepository;
use App\Repository\TransactionRepository;
use App\Repository\UserRepository;
use App\Entity\CovoiturageEntity;
use App\Security\Csrf;
use App\Service\Flash;

class CovoiturageController extends Controller
{
    private VehicleRepository $vehicleRepository;
    private CovoiturageRepository $covoiturageRepository;
    private ParticipationRepository $participationRepository;
    private TransactionRepository $transactionRepository;
    private UserRepository $localUserRepository;

    public function __construct()
    {
        parent::__construct();
        $this->vehicleRepository = new VehicleRepository();
        $this->covoiturageRepository = new CovoiturageRepository();
        $this->participationRepository = new ParticipationRepository();
        $this->transactionRepository = new TransactionRepository();
        // Attention: CovoiturageController hérite déjà d'un userRepository via Controller.
        // On garde une référence locale si besoin pour clarté.
        $this->localUserRepository = $this->userRepository;
    }

    // POST /covoiturages/create (soumission classique)
    public function create(): void
    {
        if (!isset($_SESSION['user'])) {
            Flash::add('Veuillez vous connecter.', 'danger');
            redirect('/login');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            abort(405);
        }

        if (!Csrf::check($_POST['csrf'] ?? null)) {
            Flash::add('Requête invalide (CSRF).', 'danger');
            redirect('/');
        }

        $userId = (int) $_SESSION['user']['id'];
        $vehicleId = (int) ($_POST['vehicle_id'] ?? 0);
        $villeDepart = trim($_POST['ville_depart'] ?? '');
        $villeArrivee = trim($_POST['ville_arrivee'] ?? '');
        $date = trim($_POST['date'] ?? '');
        $time = trim($_POST['time'] ?? '');
        $timeArrivee = trim($_POST['time_arrivee'] ?? '');
        $prixRaw = $_POST['prix'] ?? '';
        $places = filter_input(INPUT_POST, 'places', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 9]]);
        $prix = is_numeric($prixRaw) ? (float) $prixRaw : -1;

        if ($vehicleId <= 0 || $villeDepart === '' || $villeArrivee === '' || $date === '' || $time === '' || $timeArrivee === '' || $prix < 0 || $places === false) {
            Flash::add('Champs requis manquants ou invalides.', 'danger');
            redirect('/');
        }

        $vehicle = $this->vehicleRepository->findById($vehicleId);
        if (!$vehicle || $vehicle->getUserId() !== $userId) {
            Flash::add('Véhicule introuvable ou non autorisé.', 'danger');
            redirect('/');
        }
        if ($places > $vehicle->getPlacesDispo()) {
            Flash::add("Le nombre de places demandées dépasse la capacité du véhicule.", 'danger');
            redirect('/');
        }

        $departDt = \DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $time);
        if (!$departDt) {
            Flash::add('Date/heure invalides.', 'danger');
            redirect('/');
        }
        // Interdit une date de départ passée (tolère la minute courante)
        $now = new \DateTime('now');
        if ($departDt < $now) {
            Flash::add('La date/heure de départ ne peut pas être dans le passé.', 'danger');
            redirect('/');
        }

        // Arrivée: si l'heure d'arrivée est inférieure ou égale à l'heure de départ,
        // on considère que c'est le lendemain (trajet qui passe minuit).
        $arriveeDt = \DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $timeArrivee);
        if (!$arriveeDt) {
            Flash::add('Date/heure invalides.', 'danger');
            redirect('/');
        }
        if ($arriveeDt <= $departDt) {
            $arriveeDt->modify('+1 day');
        }

        $c = new CovoiturageEntity([
            'driver_id' => $userId,
            'vehicle_id' => $vehicleId,
            'adresse_depart' => $villeDepart,
            'adresse_arrivee' => $villeArrivee,
            'depart' => $departDt->format('Y-m-d H:i:s'),
            'arrivee' => $arriveeDt->format('Y-m-d H:i:s'),
            'prix' => $prix,
            'status' => 'en_attente',
        ]);
        // Frais de création: débiter le conducteur avant l'insertion
        $fee = getRideCreateFee();
        if ($fee > 0) {
            if (!$this->userRepository->debitIfEnough($userId, $fee)) {
                Flash::add("Crédits insuffisants: il faut au moins {$fee} crédit(s) pour créer un trajet.", 'warning');
                redirect('/');
            }
            // Rafraîchir le solde en session si besoin
            if (!empty($_SESSION['user']) && (int)$_SESSION['user']['id'] === $userId) {
                $u = $this->userRepository->findById($userId);
                if ($u) {
                    $_SESSION['user']['credits'] = $u->getCredits();
                }
            }
        }

        if ($this->covoiturageRepository->create($c)) {
            // Ajoute une transaction informative avec l'ID
            if ($fee > 0) {
                $this->transactionRepository->create($userId, $fee, 'debit', 'Frais création trajet #' . (int)$c->getId());
            }
            Flash::add('Covoiturage créé avec succès.', 'success');
            redirect('/liste-covoiturages');
        } else {
            // Rembourser le frais si l'insertion a échoué
            if ($fee > 0) {
                $this->userRepository->credit($userId, $fee);
                $this->transactionRepository->create($userId, $fee, 'credit', 'Remboursement frais création (échec)');
                if (!empty($_SESSION['user']) && (int)$_SESSION['user']['id'] === $userId) {
                    $u = $this->userRepository->findById($userId);
                    if ($u) {
                        $_SESSION['user']['credits'] = $u->getCredits();
                    }
                }
            }
            Flash::add("Erreur lors de la création du covoiturage.", 'danger');
            redirect('/');
        }
    }
    // POST /api/covoiturages/create
    public function apiCreate(): void
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Authentification requise']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }

        if (!Csrf::check($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Requête invalide (CSRF).']);
            return;
        }

        $userId = (int) $_SESSION['user']['id'];
        $vehicleId = (int) ($_POST['vehicle_id'] ?? 0);
        $villeDepart = trim($_POST['ville_depart'] ?? '');
        $villeArrivee = trim($_POST['ville_arrivee'] ?? '');
        $date = trim($_POST['date'] ?? '');
        $time = trim($_POST['time'] ?? '');
        $prix = (float) ($_POST['prix'] ?? 0);
        $timeArrivee = trim($_POST['time_arrivee'] ?? '');
        $places = filter_input(INPUT_POST, 'places', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 9]]);

        // Validations simples
        if ($vehicleId <= 0 || $villeDepart === '' || $villeArrivee === '' || $date === '' || $time === '' || $timeArrivee === '' || $prix < 0 || $places === false) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Champs requis manquants ou invalides.']);
            return;
        }

        $vehicle = $this->vehicleRepository->findById($vehicleId);
        if (!$vehicle || $vehicle->getUserId() !== $userId) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Véhicule introuvable ou non autorisé.']);
            return;
        }
        if ($places > $vehicle->getPlacesDispo()) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Le nombre de places demandées dépasse la capacité du véhicule."]);
            return;
        }

        // Assemble date/heure
        $departDt = \DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $time);
        if (!$departDt) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Date/heure invalides.']);
            return;
        }
        $now = new \DateTime('now');
        if ($departDt < $now) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "La date/heure de départ ne peut pas être dans le passé."]);
            return;
        }

        $arriveeDt = \DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $timeArrivee);
        if (!$arriveeDt) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Date/heure invalides.']);
            return;
        }
        if ($arriveeDt <= $departDt) {
            // Interpréter comme le lendemain
            $arriveeDt->modify('+1 day');
        }

        $c = new CovoiturageEntity([
            'driver_id' => $userId,
            'vehicle_id' => $vehicleId,
            'adresse_depart' => $villeDepart,
            'adresse_arrivee' => $villeArrivee,
            'depart' => $departDt->format('Y-m-d H:i:s'),
            'arrivee' => $arriveeDt->format('Y-m-d H:i:s'),
            'prix' => $prix,
            'status' => 'en_attente',
        ]);
        // Frais de création
        $fee = getRideCreateFee();
        if ($fee > 0) {
            if (!$this->userRepository->debitIfEnough($userId, $fee)) {
                http_response_code(402);
                echo json_encode(['success' => false, 'message' => "Crédits insuffisants: il faut au moins {$fee} crédit(s) pour créer un trajet."]);
                return;
            }
        }

        if ($this->covoiturageRepository->create($c)) {
            if ($fee > 0) {
                $this->transactionRepository->create($userId, $fee, 'debit', 'Frais création trajet #' . (int)$c->getId());
            }
            echo json_encode(['success' => true, 'message' => 'Covoiturage créé.', 'id' => $c->getId()]);
            return;
        } else {
            if ($fee > 0) {
                $this->userRepository->credit($userId, $fee);
                $this->transactionRepository->create($userId, $fee, 'credit', 'Remboursement frais création (échec)');
            }
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la création.']);
        }
    }

    // POST /covoiturages/cancel/{id}
    public function cancel(int $id): void
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
        }
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            abort(405);
        }
        if (!Csrf::check($_POST['csrf'] ?? null)) {
            Flash::add('Requête invalide (CSRF).', 'danger');
            redirect('/mes-covoiturages');
        }

        $userId = (int) $_SESSION['user']['id'];
        $ride = $this->covoiturageRepository->findOneWithVehicleById($id);
        if (!$ride) {
            Flash::add('Trajet introuvable.', 'danger');
            redirect('/mes-covoiturages');
        }
        // Autorisation: seul le conducteur peut annuler
        if ((int)$ride['driver_id'] !== $userId) {
            Flash::add('Action non autorisée.', 'danger');
            redirect('/mes-covoiturages');
        }
        // Interdire si déjà terminé/annulé
        if (in_array(($ride['status'] ?? ''), ['annule', 'termine'], true)) {
            Flash::add('Trajet déjà clôturé.', 'warning');
            redirect('/mes-covoiturages');
        }

        // Annule le covoiturage et les participations associées + remboursements
        try {
            $pdo = \App\Db\Mysql::getInstance()->getPDO();
            $pdo->beginTransaction();
            // annuler le covoiturage
            $stmt = $pdo->prepare("UPDATE covoiturages SET status='annule' WHERE id=:id");
            $stmt->execute([':id' => $id]);

            // Récupère les participations confirmées à rembourser avant de changer leur statut
            $stmtSel = $pdo->prepare("SELECT id AS participation_id, passager_id FROM participations WHERE covoiturage_id = :id AND status = 'confirmee'");
            $stmtSel->execute([':id' => $id]);
            $confirmed = $stmtSel->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            // marquer toutes les participations comme annulées
            $stmt2 = $pdo->prepare("UPDATE participations SET status='annulee' WHERE covoiturage_id=:id AND status <> 'annulee'");
            $stmt2->execute([':id' => $id]);

            // Remboursement: créditer le montant effectivement débité (ceil(prix), min 1)
            $ridePrice = (float)($ride['prix'] ?? 0);
            $refund = max(1, (int) ceil($ridePrice));
            foreach ($confirmed as $row) {
                $passagerId = (int) ($row['passager_id'] ?? 0);
                if ($passagerId > 0) {
                    // Créditer directement via SQL pour rester dans la même transaction PDO
                    $stmtCred = $pdo->prepare("UPDATE users SET credits = credits + :amt WHERE id = :uid");
                    $stmtCred->execute([':amt' => $refund, ':uid' => $passagerId]);

                    // Journaliser la transaction de crédit
                    $stmtTx = $pdo->prepare("INSERT INTO transactions (user_id, montant, type, motif) VALUES (:uid, :m, 'credit', :motif)");
                    $stmtTx->execute([
                        ':uid' => $passagerId,
                        ':m' => $refund,
                        ':motif' => 'Remboursement annulation trajet #' . $id,
                    ]);

                    // Notifier le passager par e-mail (hors transaction DB)
                    try {
                        $passenger = $this->localUserRepository->findById($passagerId);
                        if ($passenger) {
                            $mailer = new \App\Service\Mailer();
                            $to = $passenger->getEmail();
                            $subject = 'Votre participation a été remboursée';
                            $trajet = htmlspecialchars((string)$ride['adresse_depart'] . ' → ' . (string)$ride['adresse_arrivee']);
                            $when = htmlspecialchars(date('d/m/Y H:i', strtotime((string)$ride['depart'])));
                            $body = '<p>Bonjour ' . htmlspecialchars($passenger->getPseudo()) . ',</p>'
                                . '<p>Le conducteur a annulé le trajet ' . $trajet . ' (départ ' . $when . ').</p>'
                                . '<p>Nous avons crédité votre compte de <strong>' . number_format($refund, 0, ',', ' ') . ' crédit(s)</strong>.</p>'
                                . '<p>Vous pouvez consulter vos crédits ici : <a href="' . (defined('SITE_URL') ? SITE_URL : '/') . 'mes-credits">Mes crédits</a>.</p>'
                                . '<p>— L’équipe EcoRide</p>';
                            $mailer->send($to, $subject, $body);
                        }
                    } catch (\Throwable $e) {
                        error_log('[cancel notify refund] ' . $e->getMessage());
                    }
                }
            }

            $pdo->commit();
            Flash::add('Trajet annulé. Les passagers ont été prévenus.', 'success');
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('[cancel covoit] ' . $e->getMessage());
            Flash::add('Erreur lors de l\'annulation.', 'danger');
        }

        redirect('/mes-covoiturages');
    }

    // POST /covoiturages/start/{id}
    public function start(int $id): void
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
        }
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            abort(405);
        }
        if (!Csrf::check($_POST['csrf'] ?? null)) {
            Flash::add('Requête invalide (CSRF).', 'danger');
            redirect('/mes-covoiturages');
        }
        $userId = (int) $_SESSION['user']['id'];
        $ride = $this->covoiturageRepository->findOneWithVehicleById($id);
        if (!$ride) {
            Flash::add('Trajet introuvable.', 'danger');
            redirect('/mes-covoiturages');
        }
        if ((int)$ride['driver_id'] !== $userId) {
            Flash::add('Action non autorisée.', 'danger');
            redirect('/mes-covoiturages');
        }
        if (in_array(($ride['status'] ?? ''), ['annule', 'termine'], true)) {
            Flash::add('Trajet déjà clôturé.', 'warning');
            redirect('/mes-covoiturages');
        }
        if (($ride['status'] ?? 'en_attente') === 'demarre') {
            Flash::add('Trajet déjà démarré.', 'info');
            redirect('/mes-covoiturages');
        }
        if ($this->covoiturageRepository->updateStatus($id, 'demarre')) {
            Flash::add('Trajet démarré. Bonne route !', 'success');
        } else {
            Flash::add('Impossible de démarrer le trajet.', 'danger');
        }
        redirect('/mes-covoiturages');
    }

    // POST /covoiturages/finish/{id}
    public function finish(int $id): void
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
        }
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            abort(405);
        }
        if (!Csrf::check($_POST['csrf'] ?? null)) {
            Flash::add('Requête invalide (CSRF).', 'danger');
            redirect('/mes-covoiturages');
        }
        $userId = (int) $_SESSION['user']['id'];
        $ride = $this->covoiturageRepository->findOneWithVehicleById($id);
        if (!$ride) {
            Flash::add('Trajet introuvable.', 'danger');
            redirect('/mes-covoiturages');
        }
        if ((int)$ride['driver_id'] !== $userId) {
            Flash::add('Action non autorisée.', 'danger');
            redirect('/mes-covoiturages');
        }
        if (($ride['status'] ?? '') === 'termine') {
            Flash::add('Trajet déjà terminé.', 'info');
            redirect('/mes-covoiturages');
        }
        if (($ride['status'] ?? '') === 'annule') {
            Flash::add('Trajet annulé.', 'warning');
            redirect('/mes-covoiturages');
        }

        // Passage à terminé
        if ($this->covoiturageRepository->updateStatus($id, 'termine')) {
            Flash::add('Arrivée à destination. Les passagers vont recevoir un e-mail de validation.', 'success');
            // TODO: envoyer un email à chaque passager confirmé avec un lien vers /mes-covoiturages
            // On peut trouver les passagers via ParticipationRepository::findConfirmedByCovoiturageId
            try {
                $confirmed = $this->participationRepository->findConfirmedByCovoiturageId($id);
                $mailer = new \App\Service\Mailer();
                foreach ($confirmed as $row) {
                    $passagerId = (int)$row['passager_id'];
                    $u = $this->localUserRepository->findById($passagerId);
                    if ($u) {
                        $to = $u->getEmail();
                        $subject = 'Validez votre trajet EcoRide';
                        $link = (defined('SITE_URL') ? SITE_URL : '/') . 'participations/validate/' . (int)$row['participation_id'];
                        $body = '<p>Bonjour ' . htmlspecialchars($u->getPseudo()) . ',</p>'
                            . '<p>Votre trajet vient de se terminer. Merci de confirmer que tout s\'est bien passé ou de signaler un souci.</p>'
                            . '<p><a href="' . htmlspecialchars($link) . '">Valider mon voyage</a></p>'
                            . '<p>— L\'équipe EcoRide</p>';
                        $mailer->send($to, $subject, $body);
                    }
                }
            } catch (\Throwable $e) {
                error_log('[finish covoit mail] ' . $e->getMessage());
            }
        } else {
            Flash::add('Impossible de terminer le trajet.', 'danger');
        }
        redirect('/mes-covoiturages');
    }
}
