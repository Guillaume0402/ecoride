<?php

namespace App\Service;

use App\Db\Mysql;
use App\Repository\TransactionRepository;
use App\Repository\UserRepository;

class MaintenanceService
{
    private \PDO $pdo;
    private TransactionRepository $txRepo;
    private UserRepository $userRepo;

    public function __construct()
    {
        $this->pdo = Mysql::getInstance()->getPDO();
        $this->txRepo = new TransactionRepository();
        $this->userRepo = new UserRepository();
    }

    // Balayage principal: annulation auto des trajets non démarrés 1h après départ + rattrapage remboursements manqués
    public function sweep(): void
    {
        try {
            $this->autoCancelExpiredRides();
            $this->autoExpireStuckStartedRides();
            $this->backfillMissingRefundsForCancelled();
        } catch (\Throwable $e) {
            error_log('[MaintenanceService::sweep] ' . $e->getMessage());
        }
    }

    // Annule les trajets encore "en_attente" 1h après l\'heure de départ et rembourse les passagers confirmés
    private function autoCancelExpiredRides(): void
    {
        $minutes = defined('AUTO_CANCEL_MINUTES') ? (int) AUTO_CANCEL_MINUTES : 60;
        $threshold = (new \DateTime())->modify("-{$minutes} minutes")->format('Y-m-d H:i:s');
        $stmtList = $this->pdo->prepare("SELECT id, prix, adresse_depart, adresse_arrivee, depart FROM covoiturages 
            WHERE status = 'en_attente' AND depart < :threshold
            ORDER BY depart ASC LIMIT 100");
        $stmtList->execute([':threshold' => $threshold]);
        $rows = $stmtList->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        foreach ($rows as $ride) {
            $rideId = (int)$ride['id'];
            $refund = max(1, (int) ceil((float)($ride['prix'] ?? 0)));
            try {
                $this->pdo->beginTransaction();
                // Clôture du trajet si toujours en attente
                $stmt = $this->pdo->prepare("UPDATE covoiturages SET status='annule' WHERE id=:id AND status='en_attente'");
                $stmt->execute([':id' => $rideId]);

                // Participations confirmées à rembourser
                $stmtSel = $this->pdo->prepare("SELECT id AS participation_id, passager_id FROM participations WHERE covoiturage_id = :id AND status = 'confirmee'");
                $stmtSel->execute([':id' => $rideId]);
                $confirmed = $stmtSel->fetchAll(\PDO::FETCH_ASSOC) ?: [];

                // Marquer toutes les participations comme annulées (y compris en_attente_validation)
                $stmt2 = $this->pdo->prepare("UPDATE participations SET status='annulee' WHERE covoiturage_id=:id AND status <> 'annulee'");
                $stmt2->execute([':id' => $rideId]);

                // Rembourser les confirmées si pas déjà remboursées
                foreach ($confirmed as $row) {
                    $passagerId = (int)($row['passager_id'] ?? 0);
                    if ($passagerId <= 0) continue;
                    $motif = 'Remboursement annulation trajet #' . $rideId;
                    if (!$this->txRepo->existsForMotif($passagerId, $motif)) {
                        // Crédit SQL direct (même transaction)
                        $stmtCred = $this->pdo->prepare("UPDATE users SET credits = credits + :amt WHERE id = :uid");
                        $stmtCred->execute([':amt' => $refund, ':uid' => $passagerId]);
                        // Journal
                        $stmtTx = $this->pdo->prepare("INSERT INTO transactions (user_id, montant, type, motif) VALUES (:uid, :m, 'credit', :motif)");
                        $stmtTx->execute([':uid' => $passagerId, ':m' => $refund, ':motif' => $motif]);
                    }
                }

                $this->pdo->commit();

                // Notifier après commit (best effort)
                try {
                    if (!empty($confirmed)) {
                        $mailer = new Mailer();
                        foreach ($confirmed as $row) {
                            $passagerId = (int)$row['passager_id'];
                            $u = $this->userRepo->findById($passagerId);
                            if ($u) {
                                $to = $u->getEmail();
                                $subject = 'Trajet expiré - remboursement effectué';
                                $trajet = htmlspecialchars((string)$ride['adresse_depart'] . ' → ' . (string)$ride['adresse_arrivee']);
                                $when = htmlspecialchars(date('d/m/Y H:i', strtotime((string)$ride['depart'])));
                                $body = '<p>Bonjour ' . htmlspecialchars($u->getPseudo()) . ',</p>'
                                    . '<p>Le trajet ' . $trajet . ' (départ ' . $when . ') n\'a pas eu lieu et a été annulé automatiquement.</p>'
                                    . '<p>Votre compte a été crédité de <strong>' . number_format($refund, 0, ',', ' ') . ' crédit(s)</strong>.</p>'
                                    . '<p><a href="' . (defined('SITE_URL') ? SITE_URL : '/') . 'mes-credits">Voir mes crédits</a></p>'
                                    . '<p>— L\'équipe EcoRide</p>';
                                $mailer->send($to, $subject, $body);
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    error_log('[MaintenanceService notify auto-cancel] ' . $e->getMessage());
                }
            } catch (\Throwable $e) {
                if ($this->pdo->inTransaction()) {
                    $this->pdo->rollBack();
                }
                error_log('[MaintenanceService::autoCancelExpiredRides] ride #' . $rideId . ' ' . $e->getMessage());
            }
        }
    }

    // Annule les trajets "demarre" depuis trop longtemps et jamais terminés; rembourse les passagers confirmés
    private function autoExpireStuckStartedRides(): void
    {
        $minutes = defined('AUTO_EXPIRE_STARTED_MINUTES') ? (int) AUTO_EXPIRE_STARTED_MINUTES : 120;
        $threshold = (new \DateTime())->modify("-{$minutes} minutes")->format('Y-m-d H:i:s');
        $stmtList = $this->pdo->prepare("SELECT id, prix, adresse_depart, adresse_arrivee, depart FROM covoiturages 
                WHERE status = 'demarre' AND depart < :threshold
                ORDER BY depart ASC LIMIT 100");
        $stmtList->execute([':threshold' => $threshold]);
        $rows = $stmtList->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        foreach ($rows as $ride) {
            $rideId = (int)$ride['id'];
            $refund = max(1, (int) ceil((float)($ride['prix'] ?? 0)));
            try {
                $this->pdo->beginTransaction();
                // Annule le trajet démarré mais jamais terminé
                $stmt = $this->pdo->prepare("UPDATE covoiturages SET status='annule' WHERE id=:id AND status='demarre'");
                $stmt->execute([':id' => $rideId]);

                // Participations confirmées à rembourser
                $stmtSel = $this->pdo->prepare("SELECT id AS participation_id, passager_id FROM participations WHERE covoiturage_id = :id AND status = 'confirmee'");
                $stmtSel->execute([':id' => $rideId]);
                $confirmed = $stmtSel->fetchAll(\PDO::FETCH_ASSOC) ?: [];

                // Marquer toutes les participations comme annulées
                $stmt2 = $this->pdo->prepare("UPDATE participations SET status='annulee' WHERE covoiturage_id=:id AND status <> 'annulee'");
                $stmt2->execute([':id' => $rideId]);

                foreach ($confirmed as $row) {
                    $passagerId = (int)($row['passager_id'] ?? 0);
                    if ($passagerId <= 0) continue;
                    $motif = 'Remboursement annulation trajet #' . $rideId;
                    if (!$this->txRepo->existsForMotif($passagerId, $motif)) {
                        $stmtCred = $this->pdo->prepare("UPDATE users SET credits = credits + :amt WHERE id = :uid");
                        $stmtCred->execute([':amt' => $refund, ':uid' => $passagerId]);
                        $stmtTx = $this->pdo->prepare("INSERT INTO transactions (user_id, montant, type, motif) VALUES (:uid, :m, 'credit', :motif)");
                        $stmtTx->execute([':uid' => $passagerId, ':m' => $refund, ':motif' => $motif]);
                    }
                }

                $this->pdo->commit();

                try {
                    if (!empty($confirmed)) {
                        $mailer = new Mailer();
                        foreach ($confirmed as $row) {
                            $passagerId = (int)$row['passager_id'];
                            $u = $this->userRepo->findById($passagerId);
                            if ($u) {
                                $to = $u->getEmail();
                                $subject = 'Trajet clos automatiquement';
                                $trajet = htmlspecialchars((string)$ride['adresse_depart'] . ' → ' . (string)$ride['adresse_arrivee']);
                                $when = htmlspecialchars(date('d/m/Y H:i', strtotime((string)$ride['depart'])));
                                $body = '<p>Bonjour ' . htmlspecialchars($u->getPseudo()) . ',</p>'
                                    . '<p>Le trajet ' . $trajet . ' (départ ' . $when . ') a été clôturé automatiquement.</p>'
                                    . '<p>Votre compte a été crédité de <strong>' . number_format($refund, 0, ',', ' ') . ' crédit(s)</strong>.</p>'
                                    . '<p>— L\'équipe EcoRide</p>';
                                $mailer->send($to, $subject, $body);
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    error_log('[MaintenanceService notify auto-expire-started] ' . $e->getMessage());
                }
            } catch (\Throwable $e) {
                if ($this->pdo->inTransaction()) {
                    $this->pdo->rollBack();
                }
                error_log('[MaintenanceService::autoExpireStuckStartedRides] ride #' . $rideId . ' ' . $e->getMessage());
            }
        }
    }

    // Rattrapage: si des trajets sont déjà en statut annulé mais sans transaction de remboursement pour certains passagers
    private function backfillMissingRefundsForCancelled(): void
    {
        $sql = "SELECT c.id AS covoiturage_id, c.prix, p.passager_id
                FROM covoiturages c
                JOIN participations p ON p.covoiturage_id = c.id
                WHERE c.status = 'annule' AND p.status IN ('confirmee','annulee')
                ORDER BY c.id DESC LIMIT 500";
        $rows = $this->pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        foreach ($rows as $row) {
            $rideId = (int)$row['covoiturage_id'];
            $passagerId = (int)$row['passager_id'];
            $refund = max(1, (int) ceil((float)($row['prix'] ?? 0)));
            $motif = 'Remboursement annulation trajet #' . $rideId;
            try {
                if (!$this->txRepo->existsForMotif($passagerId, $motif)) {
                    // Effectue le crédit manquant
                    $stmtCred = $this->pdo->prepare("UPDATE users SET credits = credits + :amt WHERE id = :uid");
                    $stmtCred->execute([':amt' => $refund, ':uid' => $passagerId]);
                    $stmtTx = $this->pdo->prepare("INSERT INTO transactions (user_id, montant, type, motif) VALUES (:uid, :m, 'credit', :motif)");
                    $stmtTx->execute([':uid' => $passagerId, ':m' => $refund, ':motif' => $motif]);
                }
            } catch (\Throwable $e) {
                error_log('[MaintenanceService::backfillMissingRefundsForCancelled] ' . $e->getMessage());
            }
        }
    }
}
