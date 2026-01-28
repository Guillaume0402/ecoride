<?php

namespace App\Service;

use App\Db\Mysql;
use App\Repository\TransactionRepository;
use App\Repository\UserRepository;

// Service chargé des tâches automatiques de maintenance
// (annulation de trajets expirés, remboursements oubliés, etc.)
class MaintenanceService
{
    // Connexion PDO réutilisée pour toutes les requêtes SQL
    private \PDO $pdo;
    // Repository pour interroger les transactions de crédits
    private TransactionRepository $txRepo;
    // Repository pour récupérer les utilisateurs
    private UserRepository $userRepo;


    // Le constructeur prépare la connexion et les repositories
    public function __construct()
    {
        // Récupère l'unique instance de connexion MySQL (pattern singleton)
        $this->pdo = Mysql::getInstance()->getPDO();
        $this->txRepo = new TransactionRepository();
        $this->userRepo = new UserRepository();
    }

    // Balayage principal: annulation auto des trajets non démarrés 1h après départ + rattrapage remboursements manqués
    // Méthode appelée par un cron/commande CLI pour lancer toutes les vérifications
    public function sweep(): void
    {
        try {
            // 1) Annule les trajets non démarrés trop longtemps après l'heure prévue
            $this->autoCancelExpiredRides();
            // 2) Annule les trajets "démarrés" qui ne sont jamais passés en "terminé"
            $this->autoExpireStuckStartedRides();
            // 3) Rattrape d'éventuels remboursements qui auraient été oubliés
            $this->backfillMissingRefundsForCancelled();
        } catch (\Throwable $e) {
            error_log('[MaintenanceService::sweep] ' . $e->getMessage());
        }
    }

    // Annule les trajets encore "en_attente" 1h après l\'heure de départ et rembourse les passagers confirmés
    private function autoCancelExpiredRides(): void
    {
        // Durée à partir de laquelle un trajet en attente est considéré comme expiré
        $minutes = defined('AUTO_CANCEL_MINUTES') ? (int) AUTO_CANCEL_MINUTES : 60;

        // Date/heure limite de départ pour être considéré comme expiré
        $threshold = (new \DateTime())->modify("-{$minutes} minutes")->format('Y-m-d H:i:s');

        // Sélectionne les trajets encore en attente mais dont l'heure de départ est déjà passée
        $stmtList = $this->pdo->prepare("SELECT id, prix, adresse_depart, adresse_arrivee, depart FROM covoiturages 
            WHERE status = 'en_attente' AND depart < :threshold
            ORDER BY depart ASC LIMIT 100");
        $stmtList->execute([':threshold' => $threshold]);
        $rows = $stmtList->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        foreach ($rows as $ride) {
            // Identifiant du trajet
            $rideId = (int)$ride['id'];
            // Montant à rembourser : au moins 1 crédit, arrondi au supérieur
            $refund = $this->computeRefund($ride);
            try {
                // On commence une transaction SQL pour garantir la cohérence
                $this->pdo->beginTransaction();
                // Clôture le trajet si toujours en attente
                $stmt = $this->pdo->prepare("UPDATE covoiturages SET status='annule' WHERE id=:id AND status='en_attente'");
                $stmt->execute([':id' => $rideId]);

                // Récupère les participations confirmées qui devront être remboursées
                $stmtSel = $this->pdo->prepare("SELECT id AS participation_id, passager_id FROM participations WHERE covoiturage_id = :id AND status = 'confirmee'");
                $stmtSel->execute([':id' => $rideId]);
                $confirmed = $stmtSel->fetchAll(\PDO::FETCH_ASSOC) ?: [];

                // Marque toutes les participations comme annulées (y compris en_attente_validation)
                $stmt2 = $this->pdo->prepare("UPDATE participations SET status='annulee' WHERE covoiturage_id=:id AND status <> 'annulee'");
                $stmt2->execute([':id' => $rideId]);

                // Rembourse les participations confirmées si aucune transaction de remboursement n'existe déjà
                foreach ($confirmed as $row) {
                    $passagerId = (int)($row['passager_id'] ?? 0);
                    if ($passagerId <= 0) continue;
                    $motif = $this->refundMotif($rideId);
                    if (!$this->txRepo->existsForMotif($passagerId, $motif)) {
                        // Crédit SQL direct (même transaction) sur le compte de l'utilisateur
                        $stmtCred = $this->pdo->prepare("UPDATE users SET credits = credits + :amt WHERE id = :uid");
                        $stmtCred->execute([':amt' => $refund, ':uid' => $passagerId]);
                        // Ajoute une ligne dans le journal des transactions
                        $stmtTx = $this->pdo->prepare("INSERT INTO transactions (user_id, montant, type, motif) VALUES (:uid, :m, 'credit', :motif)");
                        $stmtTx->execute([':uid' => $passagerId, ':m' => $refund, ':motif' => $motif]);
                    }
                }

                $this->pdo->commit();

                // Envoie un e-mail d'information aux passagers après le commit (best effort)
                $this->notifyPassengersRefunded($ride, $confirmed, $refund, 'Trajet expiré - remboursement effectué');
            } catch (\Throwable $e) {
                // En cas d'erreur, on annule la transaction pour éviter un état partiel
                if ($this->pdo->inTransaction()) {
                    $this->pdo->rollBack();
                }
                error_log('[MaintenanceService::autoCancelExpiredRides] ride #' . $rideId . ' ' . $e->getMessage());
            }
        }
    }

    // Calcule le montant de remboursement (au moins 1, arrondi supérieur)
    private function computeRefund(array $ride): int
    {
        return max(1, (int) ceil((float) ($ride['prix'] ?? 0)));
    }

    // Motif standardisé pour éviter les variations de texte
    private function refundMotif(int $rideId): string
    {
        return 'Remboursement annulation trajet #' . $rideId;
    }

    // Notifie les passagers remboursés (best effort, après commit)
    private function notifyPassengersRefunded(array $ride, array $confirmed, int $refund, string $subject): void
    {
        if (empty($confirmed)) {
            return;
        }

        try {
            $mailer = new Mailer();

            $trajet = htmlspecialchars((string)($ride['adresse_depart'] ?? '') . ' → ' . (string)($ride['adresse_arrivee'] ?? ''));
            $when = htmlspecialchars(date('d/m/Y H:i', strtotime((string)($ride['depart'] ?? ''))));

            foreach ($confirmed as $row) {
                $passagerId = (int)($row['passager_id'] ?? 0);
                if ($passagerId <= 0) continue;

                $u = $this->userRepo->findById($passagerId);
                if (!$u) continue;

                $to = $u->getEmail();

                $body = '<p>Bonjour ' . htmlspecialchars($u->getPseudo()) . ',</p>'
                    . '<p>Le trajet ' . $trajet . ' (départ ' . $when . ') a été annulé automatiquement.</p>'
                    . '<p>Votre compte a été crédité de <strong>' . number_format($refund, 0, ',', ' ') . ' crédit(s)</strong>.</p>'
                    . '<p><a href="' . (defined('SITE_URL') ? SITE_URL : '/') . 'mes-credits">Voir mes crédits</a></p>'
                    . '<p>— L\'équipe EcoRide</p>';

                $mailer->send($to, $subject, $body);
            }
        } catch (\Throwable $e) {
            error_log('[MaintenanceService notifyPassengersRefunded] ' . $e->getMessage());
        }
    }


    // Annule les trajets "demarre" depuis trop longtemps et jamais terminés; rembourse les passagers confirmés
    private function autoExpireStuckStartedRides(): void
    {
        // Durée maximale pendant laquelle un trajet peut rester en statut "demarre"
        $minutes = defined('AUTO_EXPIRE_STARTED_MINUTES') ? (int) AUTO_EXPIRE_STARTED_MINUTES : 120;
        $threshold = (new \DateTime())->modify("-{$minutes} minutes")->format('Y-m-d H:i:s');
        // Sélectionne les trajets démarrés avant la date/heure seuil
        $stmtList = $this->pdo->prepare("SELECT id, prix, adresse_depart, adresse_arrivee, depart FROM covoiturages 
                WHERE status = 'demarre' AND depart < :threshold
                ORDER BY depart ASC LIMIT 100");
        $stmtList->execute([':threshold' => $threshold]);
        $rows = $stmtList->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        foreach ($rows as $ride) {
            $rideId = (int)$ride['id'];
            $refund = $this->computeRefund($ride);
            try {
                // On commence une transaction pour annuler proprement le trajet et rembourser
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

                // Même logique de remboursement que pour les trajets en attente expirés
                foreach ($confirmed as $row) {
                    $passagerId = (int)($row['passager_id'] ?? 0);
                    if ($passagerId <= 0) continue;
                    $motif = $this->refundMotif($rideId);
                    if (!$this->txRepo->existsForMotif($passagerId, $motif)) {
                        $stmtCred = $this->pdo->prepare("UPDATE users SET credits = credits + :amt WHERE id = :uid");
                        $stmtCred->execute([':amt' => $refund, ':uid' => $passagerId]);
                        $stmtTx = $this->pdo->prepare("INSERT INTO transactions (user_id, montant, type, motif) VALUES (:uid, :m, 'credit', :motif)");
                        $stmtTx->execute([':uid' => $passagerId, ':m' => $refund, ':motif' => $motif]);
                    }
                }
                $this->pdo->commit();

                // Notifie les passagers que le trajet a été clôturé automatiquement
                $this->notifyPassengersRefunded(
                    $ride,
                    $confirmed,
                    $refund,
                    'Trajet clos automatiquement'
                );
            } catch (\Throwable $e) {
                // Rollback en cas de problème pendant la mise à jour
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
        // Recherche les trajets annulés pour lesquels il y a eu des participations
        // mais où un remboursement pourrait ne pas avoir été enregistré
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
                // Si aucune transaction n'existe pour ce motif, on crédite les crédits manquants
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
