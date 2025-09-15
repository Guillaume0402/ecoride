<div class="container py-4">
    <h1>Mes trajets</h1>

    <div class="row g-4 mt-2">
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header fw-semibold">En tant que conducteur</div>
                <div class="card-body">
                    <?php if (!empty($asDriver)): ?>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Trajet</th>
                                        <th>Départ</th>
                                        <th>Places restantes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($asDriver as $c): ?>
                                        <tr>
                                            <td><?= (int)$c['id'] ?></td>
                                            <td><?= htmlspecialchars($c['adresse_depart']) ?> → <?= htmlspecialchars($c['adresse_arrivee']) ?></td>
                                            <td><?= (new DateTime($c['depart']))->format('d/m/Y H\hi') ?></td>
                                            <td><?= isset($c['places_restantes']) ? (int)$c['places_restantes'] : (int)($c['vehicle_places'] ?? 0) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">Vous n'avez pas encore créé de trajets.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header fw-semibold">En tant que passager</div>
                <div class="card-body">
                    <?php if (!empty($asPassenger)): ?>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Trajet</th>
                                        <th>Départ</th>
                                        <th>Conducteur</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($asPassenger as $p): ?>
                                        <tr>
                                            <td><?= (int)$p['covoiturage_id'] ?></td>
                                            <td><?= htmlspecialchars($p['adresse_depart']) ?> → <?= htmlspecialchars($p['adresse_arrivee']) ?></td>
                                            <td><?= (new DateTime($p['depart']))->format('d/m/Y H\hi') ?></td>
                                            <td><?= htmlspecialchars($p['driver_pseudo']) ?></td>
                                            <td>
                                                <?php if ($p['status'] === 'confirmee'): ?>
                                                    <span class="badge bg-success">Confirmée</span>
                                                <?php elseif ($p['status'] === 'en_attente_validation'): ?>
                                                    <span class="badge bg-warning text-dark">En attente</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Annulée</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">Aucune participation pour le moment.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="mt-3">
        <a href="/liste-covoiturages" class="btn btn-outline-primary">Rechercher un trajet</a>
    </div>
</div>