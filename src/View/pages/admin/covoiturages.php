<?php
// Vue admin: liste globale des covoiturages
// Variables attendues: $scope, $limit, $rows
?>
<div class="container py-4">
    <h1 class="mb-3">Tous les covoiturages</h1>

    <form method="GET" class="row g-2 align-items-end mb-3">
        <div class="col-auto">
            <label for="scope" class="form-label">Filtre</label>
            <select class="form-select" id="scope" name="scope">
                <?php
                $scopes = ['all' => 'Tous', 'past' => 'Passés', 'ongoing' => 'En cours', 'future' => 'À venir'];
                foreach ($scopes as $k => $label):
                    $sel = ($scope ?? 'all') === $k ? 'selected' : '';
                    echo '<option value="' . htmlspecialchars($k) . '" ' . $sel . '>' . htmlspecialchars($label) . '</option>';
                endforeach;
                ?>
            </select>
        </div>
        <div class="col-auto">
            <label for="limit" class="form-label">Limite</label>
            <input type="number" class="form-control" id="limit" name="limit" min="1" max="1000" value="<?= (int)($limit ?? 200) ?>">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Appliquer</button>
            <a href="/admin/covoiturages" class="btn btn-outline-secondary">Réinitialiser</a>
        </div>
    </form>

    <?php if (empty($rows)): ?>
        <div class="alert alert-info">Aucun covoiturage pour ce filtre.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Trajet</th>
                        <th>Départ</th>
                        <th>Crédits</th>
                        <th>Conducteur</th>
                        <th>Véhicule</th>
                        <th>Statut</th>
                        <th>Conf.</th>
                        <th>En att.</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><?= (int)$r['id'] ?></td>
                            <td><?= htmlspecialchars((string)($r['adresse_depart'] ?? '')) ?> → <?= htmlspecialchars((string)($r['adresse_arrivee'] ?? '')) ?></td>
                            <td><?= htmlspecialchars(date('d/m/Y H\hi', strtotime((string)$r['depart']))) ?></td>
                            <td><?= number_format((float)($r['prix'] ?? 0), 2, ',', ' ') ?></td>
                            <td><?= htmlspecialchars((string)($r['driver_pseudo'] ?? ('#' . (int)($r['driver_id'] ?? 0)))) ?></td>
                            <td><?= htmlspecialchars(trim(((string)($r['vehicle_marque'] ?? '')) . ' ' . ((string)($r['vehicle_modele'] ?? '')))) ?></td>
                            <td>
                                <?php $st = (string)($r['status'] ?? 'en_attente');
                                $labels = ['en_attente' => 'En attente', 'demarre' => 'Démarré', 'termine' => 'Terminé', 'annule' => 'Annulé'];
                                $cls = ['en_attente' => 'warning text-dark', 'demarre' => 'info', 'termine' => 'success', 'annule' => 'danger'];
                                ?>
                                <span class="badge bg-<?= $cls[$st] ?? 'secondary' ?>"><?= $labels[$st] ?? $st ?></span>
                            </td>
                            <td><?= (int)($r['confirmed_count'] ?? 0) ?></td>
                            <td><?= (int)($r['pending_count'] ?? 0) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
