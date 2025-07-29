<?php
// Sécurité : vérifier que l'utilisateur est connecté et a le rôle "employé"
if (!isset($_SESSION['user']) || $_SESSION['user']['role_id'] !== 2) { // 2 = Employé
    header('Location: /login');
    exit;
}
?>

<div class="container py-5">
    <h2 class="mb-4 text-center">Espace Employé</h2>

    <!-- Section 1 : Valider / Refuser un avis -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">Avis en attente de validation</div>
        <div class="card-body">
            <?php if (!empty($pendingReviews)): ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Chauffeur</th>
                            <th>Commentaire</th>
                            <th>Note</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingReviews as $review): ?>
                            <tr>
                                <td><?= htmlspecialchars($review['driver_name']) ?></td>
                                <td><?= htmlspecialchars($review['comment']) ?></td>
                                <td><?= htmlspecialchars($review['rating']) ?>/5</td>
                                <td>
                                    <form method="post" action="/employee/review/validate" class="d-inline">
                                        <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                        <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Valider</button>
                                    </form>
                                    <form method="post" action="/employee/review/validate" class="d-inline">
                                        <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                        <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Refuser</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-center text-muted">Aucun avis en attente.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Section 2 : Trajets problématiques -->
    <div class="card shadow-sm">
        <div class="card-header bg-warning">Trajets signalés comme problématiques</div>
        <div class="card-body">
            <?php if (!empty($problematicTrips)): ?>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID Covoiturage</th>
                            <th>Chauffeur</th>
                            <th>Passager</th>
                            <th>Départ</th>
                            <th>Arrivée</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($problematicTrips as $trip): ?>
                            <tr>
                                <td>#<?= $trip['covoiturage_id'] ?></td>
                                <td><?= htmlspecialchars($trip['driver_pseudo']) ?> <br><small><?= htmlspecialchars($trip['driver_email']) ?></small></td>
                                <td><?= htmlspecialchars($trip['passenger_pseudo']) ?> <br><small><?= htmlspecialchars($trip['passenger_email']) ?></small></td>
                                <td><?= htmlspecialchars($trip['start_location']) ?></td>
                                <td><?= htmlspecialchars($trip['end_location']) ?></td>
                                <td><?= htmlspecialchars($trip['start_date']) ?> → <?= htmlspecialchars($trip['end_date']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-center text-muted">Aucun trajet signalé.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
