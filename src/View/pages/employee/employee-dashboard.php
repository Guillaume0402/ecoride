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
                                        <input type="hidden" name="csrf" value="<?= \App\Security\Csrf::token() ?>">
                                        <input type="hidden" name="review_id" value="<?= htmlspecialchars($review['id']) ?>">
                                        <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Valider</button>
                                    </form>
                                    <form method="post" action="/employee/review/validate" class="d-inline">
                                        <input type="hidden" name="csrf" value="<?= \App\Security\Csrf::token() ?>">
                                        <input type="hidden" name="review_id" value="<?= htmlspecialchars($review['id']) ?>">
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
                            <th>ID</th>
                            <th>Trajet</th>
                            <th>Raison</th>
                            <th>Commentaire</th>
                            <th>Créé</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($problematicTrips as $rep): ?>
                            <tr>
                                <td><?= htmlspecialchars($rep['id']) ?></td>
                                <td>#<?= (int)($rep['covoiturage_id'] ?? 0) ?></td>
                                <td><?= htmlspecialchars($rep['reason'] ?? '') ?></td>
                                <td><?= htmlspecialchars($rep['comment'] ?? '') ?></td>
                                <td><?php if (!empty($rep['created_at_ms'])) { $d = (int)$rep['created_at_ms']/1000; echo date('d/m/Y H:i', $d); } ?></td>
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
