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
                            <th>Passager</th>
                            <th>Départ</th>
                            <th>Destination</th>
                            <th>Heure</th>
                            <th>Commentaire</th>
                            <th>Note</th>
                            <th>Créé</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingReviews as $review): ?>
                            <tr>
                                <td><?= htmlspecialchars((string) ($review['driver_name'] ?? ('#' . (int)($review['driver_id'] ?? 0)))) ?></td>
                                <td><?= htmlspecialchars((string) ($review['passager_name'] ?? ('#' . (int)($review['passager_id'] ?? 0)))) ?></td>
                                <td><?= htmlspecialchars((string) ($review['adresse_depart'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string) ($review['adresse_arrivee'] ?? '')) ?></td>
                                <td>
                                    <?php if (!empty($review['depart_at'])) {
                                        echo date('d/m/Y H:i', strtotime((string)$review['depart_at']));
                                    } ?>
                                </td>
                                <td><?= htmlspecialchars((string) ($review['comment'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string) ($review['rating'] ?? '')) ?>/5</td>
                                <td>
                                    <?php if (!empty($review['created_at_ms'])) {
                                        // created_at_ms peut être un float (ms). On arrondit et on convertit proprement en secondes sans perte
                                        $ms = (float) $review['created_at_ms'];
                                        $sec = intdiv((int) round($ms), 1000);
                                        echo date('d/m/Y H:i', $sec);
                                    } ?>
                                </td>
                                <td>
                                    <?php
                                    $vehLabel = trim(((string)($review['vehicle_marque'] ?? '')) . ' ' . ((string)($review['vehicle_modele'] ?? '')));
                                    $vehImmat = (string)($review['vehicle_immatriculation'] ?? '');
                                    $tooltip = htmlspecialchars(trim($vehLabel . ($vehImmat ? ' • ' . $vehImmat : '')));
                                    ?>
                                    <?php if (!empty($review['covoiturage_id'])): ?>
                                        <a class="btn btn-outline-primary btn-sm me-2" href="/covoiturages/<?= (int)$review['covoiturage_id'] ?>" title="Voir le trajet" data-bs-toggle="tooltip" data-bs-title="<?= $tooltip ?>">
                                            Voir le trajet
                                        </a>
                                    <?php endif; ?>
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
                            <th>Chauffeur</th>
                            <th>Passager</th>
                            <th>Départ</th>
                            <th>Destination</th>
                            <th>Heure</th>
                            <th>Raison</th>
                            <th>Commentaire</th>
                            <th>Créé</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($problematicTrips as $rep): ?>
                            <tr>
                                <td><?= htmlspecialchars((string) ($rep['driver_name'] ?? ('#' . (int)($rep['driver_id'] ?? 0)))) ?></td>
                                <td><?= htmlspecialchars((string) ($rep['passager_name'] ?? ('#' . (int)($rep['passager_id'] ?? 0)))) ?></td>
                                <td><?= htmlspecialchars((string) ($rep['adresse_depart'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string) ($rep['adresse_arrivee'] ?? '')) ?></td>
                                <td>
                                    <?php if (!empty($rep['depart_at'])) {
                                        echo date('d/m/Y H:i', strtotime((string)$rep['depart_at']));
                                    } ?>
                                </td>
                                <td><?= htmlspecialchars($rep['reason'] ?? '') ?></td>
                                <td><?= htmlspecialchars($rep['comment'] ?? '') ?></td>
                                <td><?php if (!empty($rep['created_at_ms'])) {
                                        $ms = (float) $rep['created_at_ms'];
                                        $sec = intdiv((int) round($ms), 1000);
                                        echo date('d/m/Y H:i', $sec);
                                    } ?></td>
                                <td>
                                    <?php
                                    $vehLabel = trim(((string)($rep['vehicle_marque'] ?? '')) . ' ' . ((string)($rep['vehicle_modele'] ?? '')));
                                    $vehImmat = (string)($rep['vehicle_immatriculation'] ?? '');
                                    $tooltip = htmlspecialchars(trim($vehLabel . ($vehImmat ? ' • ' . $vehImmat : '')));
                                    ?>
                                    <?php if (!empty($rep['covoiturage_id'])): ?>
                                        <a class="btn btn-outline-primary btn-sm me-2" href="/covoiturages/<?= (int)$rep['covoiturage_id'] ?>" title="Voir le trajet" data-bs-toggle="tooltip" data-bs-title="<?= $tooltip ?>">
                                            Voir le trajet
                                        </a>
                                    <?php endif; ?>
                                    <form method="post" action="/employee/review/validate" class="d-inline">
                                        <input type="hidden" name="csrf" value="<?= \App\Security\Csrf::token() ?>">
                                        <input type="hidden" name="review_id" value="<?= htmlspecialchars($rep['id']) ?>">
                                        <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Valider</button>
                                    </form>
                                    <form method="post" action="/employee/review/validate" class="d-inline">
                                        <input type="hidden" name="csrf" value="<?= \App\Security\Csrf::token() ?>">
                                        <input type="hidden" name="review_id" value="<?= htmlspecialchars($rep['id']) ?>">
                                        <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Refuser</button>
                                    </form>
                                </td>
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

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.bootstrap) {
            const triggers = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            triggers.forEach(el => new bootstrap.Tooltip(el));
        }
    });
</script>