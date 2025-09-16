<div class="container py-4">
    <h1>Mes demandes de participation</h1>

    <?php if (!empty($pending)): ?>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Trajet</th>
                        <th>Départ</th>
                        <th>Passager</th>
                        <th>Crédits/Coût</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending as $row): ?>
                        <tr>
                            <td>#<?= (int)$row['covoiturage_id'] ?> — <?= htmlspecialchars($row['adresse_depart']) ?> → <?= htmlspecialchars($row['adresse_arrivee']) ?></td>
                            <td><?= (new DateTime($row['depart']))->format('d/m/Y H\hi') ?></td>
                            <td><?= htmlspecialchars($row['passager_pseudo']) ?></td>
                            <td>
                                <?php
                                $prix = (float)($row['prix'] ?? 0);
                                $cost = max(1, (int) ceil($prix));
                                $credits = (int)($row['passager_credits'] ?? 0);
                                $enough = $credits >= $cost;
                                ?>
                                <span class="badge bg-<?= $enough ? 'success' : 'warning text-dark' ?>">
                                    <?= $credits ?> / <?= $cost ?>
                                </span>
                                <?php if (!$enough): ?>
                                    <small class="text-muted d-block">Solde insuffisant</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form action="/participations/accept/<?= (int)$row['participation_id'] ?>" method="POST" class="d-inline">
                                    <input type="hidden" name="csrf" value="<?= \App\Security\Csrf::token() ?>">
                                    <button class="btn btn-success btn-sm" type="submit" <?= !$enough ? 'disabled' : '' ?>>Accepter</button>
                                </form>
                                <form action="/participations/reject/<?= (int)$row['participation_id'] ?>" method="POST" class="d-inline ms-2">
                                    <input type="hidden" name="csrf" value="<?= \App\Security\Csrf::token() ?>">
                                    <button class="btn btn-outline-danger btn-sm" type="submit">Refuser</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">Aucune demande en attente pour vos trajets.</div>
    <?php endif; ?>
</div>