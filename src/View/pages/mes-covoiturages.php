<?php
    $driverCount = isset($asDriver) && is_array($asDriver) ? count($asDriver) : 0;
    $passengerCount = isset($asPassenger) && is_array($asPassenger) ? count($asPassenger) : 0;
    $historyDriverCount = isset($historyDriver) && is_array($historyDriver) ? count($historyDriver) : 0;
    $historyPassengerCount = isset($historyPassenger) && is_array($historyPassenger) ? count($historyPassenger) : 0;
    $historyTotal = $historyDriverCount + $historyPassengerCount;
    $activeTab = $driverCount > 0 ? 'driver' : ($passengerCount > 0 ? 'passenger' : ($historyTotal > 0 ? 'history' : 'driver'));
?>

<div class="container py-4 page-mes-trajets">
    <h1>Mes trajets</h1>

    <div class="card mt-2">
        <div class="card-header pb-0">
            <ul class="nav nav-tabs card-header-tabs" id="mesTrajetsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $activeTab === 'driver' ? 'active' : '' ?>" id="driver-tab" data-bs-toggle="tab" data-bs-target="#driver-pane" type="button" role="tab" aria-controls="driver-pane" aria-selected="<?= $activeTab === 'driver' ? 'true' : 'false' ?>">
                        Conducteur <span class="badge bg-secondary ms-1"><?= (int)$driverCount ?></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $activeTab === 'passenger' ? 'active' : '' ?>" id="passenger-tab" data-bs-toggle="tab" data-bs-target="#passenger-pane" type="button" role="tab" aria-controls="passenger-pane" aria-selected="<?= $activeTab === 'passenger' ? 'true' : 'false' ?>">
                        Passager <span class="badge bg-secondary ms-1"><?= (int)$passengerCount ?></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $activeTab === 'history' ? 'active' : '' ?>" id="history-tab" data-bs-toggle="tab" data-bs-target="#history-pane" type="button" role="tab" aria-controls="history-pane" aria-selected="<?= $activeTab === 'history' ? 'true' : 'false' ?>">
                        Historique <span class="badge bg-secondary ms-1"><?= (int)$historyTotal ?></span>
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="mesTrajetsContent">
                <div class="tab-pane fade <?= $activeTab === 'driver' ? 'show active' : '' ?>" id="driver-pane" role="tabpanel" aria-labelledby="driver-tab" tabindex="0">
                    <?php if (!empty($asDriver)): ?>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Trajet</th>
                                        <th>Prix</th>
                                        <th>Statut</th>
                                        <th>Départ</th>
                                        <th>Places restantes</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($asDriver as $c): ?>
                                        <tr>
                                            <td><?= (int)$c['id'] ?></td>
                                            <td><?= htmlspecialchars($c['adresse_depart']) ?> → <?= htmlspecialchars($c['adresse_arrivee']) ?></td>
                                            <td><?= number_format((float)($c['prix'] ?? 0), 2, ',', ' ') ?> €</td>
                                            <td>
                                                <?php $st = (string)($c['status'] ?? 'en_attente');
                                                $labels = ['en_attente' => 'En attente', 'demarre' => 'Démarré', 'termine' => 'Terminé', 'annule' => 'Annulé'];
                                                // Couleurs plus lisibles sur fond sombre
                                                $cls = ['en_attente' => 'warning text-dark', 'demarre' => 'info', 'termine' => 'success', 'annule' => 'danger'];
                                                ?>
                                                <span class="badge bg-<?= $cls[$st] ?? 'secondary' ?>"><?= $labels[$st] ?? $st ?></span>
                                                <small class="ms-2 text-status-meta">(Conf: <?= (int)($c['confirmed_count'] ?? 0) ?>, Att: <?= (int)($c['pending_count'] ?? 0) ?>)</small>
                                                <?php if (!empty($c['confirmed_passengers'])): ?>
                                                    <div class="small text-status-meta mt-1">Passagers: <?= htmlspecialchars($c['confirmed_passengers']) ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= (new DateTime($c['depart']))->format('d/m/Y H\hi') ?></td>
                                            <td><?= isset($c['places_restantes']) ? (int)$c['places_restantes'] : (int)($c['vehicle_places'] ?? 0) ?></td>
                                            <td>
                                                <?php
                                                $isPast = false;
                                                try {
                                                    $isPast = (new DateTime($c['depart'])) < new DateTime();
                                                } catch (Throwable $e) {
                                                }
                                                $isClosable = !$isPast && !in_array(($c['status'] ?? 'en_attente'), ['annule', 'termine'], true);
                                                ?>
                                                <?php if ($isClosable): ?>
                                                    <form action="/covoiturages/cancel/<?= (int)$c['id'] ?>" method="POST" class="d-inline js-confirm" data-confirm-text="Annuler ce trajet ? Les passagers seront informés." data-confirm-variant="danger">
                                                        <input type="hidden" name="csrf" value="<?= \App\Security\Csrf::token() ?>">
                                                        <button type="submit" class="btn btn-outline-danger btn-sm">Annuler</button>
                                                    </form>
                                                <?php else: ?>
                                                    <button class="btn btn-secondary btn-sm" disabled>Non modifiable</button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">Vous n'avez pas encore créé de trajets.</div>
                    <?php endif; ?>
                </div>

                <div class="tab-pane fade <?= $activeTab === 'passenger' ? 'show active' : '' ?>" id="passenger-pane" role="tabpanel" aria-labelledby="passenger-tab" tabindex="0">
                    <?php if (!empty($asPassenger)): ?>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Trajet</th>
                                        <th>Départ</th>
                                        <th>Véhicule</th>
                                        <th>Prix</th>
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
                                            <td><?= htmlspecialchars(trim(($p['vehicle_marque'] ?? '') . ' ' . ($p['vehicle_modele'] ?? ''))) ?></td>
                                            <td><?= number_format((float)($p['prix'] ?? 0), 2, ',', ' ') ?> €</td>
                                            <td><?= htmlspecialchars($p['driver_pseudo']) ?></td>
                                            <td>
                                                <?php if ($p['status'] === 'confirmee'): ?>
                                                    <span class="badge bg-success">Confirmée</span>
                                                <?php elseif ($p['status'] === 'en_attente_validation'): ?>
                                                    <span class="badge bg-warning text-dark">En attente</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Annulée</span>
                                                <?php endif; ?>
                                                <?php if (!empty($p['covoit_status'])): ?>
                                                    <?php $cv = (string)$p['covoit_status'];
                                                    $cvLabel = ['en_attente' => 'En attente', 'demarre' => 'Démarré', 'termine' => 'Terminé', 'annule' => 'Annulé'][$cv] ?? $cv;
                                                    ?>
                                                    <small class="text-status-meta ms-2">Trajet: <?= htmlspecialchars($cvLabel) ?></small>
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
                <div class="tab-pane fade <?= $activeTab === 'history' ? 'show active' : '' ?>" id="history-pane" role="tabpanel" aria-labelledby="history-tab" tabindex="0">
                    <?php if (($historyDriverCount + $historyPassengerCount) > 0): ?>
                        <?php if (!empty($historyDriver)): ?>
                            <h5 class="mt-2 mb-2">En tant que conducteur</h5>
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Trajet</th>
                                            <th>Prix</th>
                                            <th>Statut</th>
                                            <th>Départ</th>
                                            <th>Places restantes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($historyDriver as $c): ?>
                                            <tr>
                                                <td><?= (int)$c['id'] ?></td>
                                                <td><?= htmlspecialchars($c['adresse_depart']) ?> → <?= htmlspecialchars($c['adresse_arrivee']) ?></td>
                                                <td><?= number_format((float)($c['prix'] ?? 0), 2, ',', ' ') ?> €</td>
                                                <td>
                                                    <?php $st = (string)($c['status'] ?? 'en_attente');
                                                    $labels = ['en_attente' => 'En attente', 'demarre' => 'Démarré', 'termine' => 'Terminé', 'annule' => 'Annulé'];
                                                    $cls = ['en_attente' => 'warning text-dark', 'demarre' => 'info', 'termine' => 'success', 'annule' => 'danger'];
                                                    ?>
                                                    <span class="badge bg-<?= $cls[$st] ?? 'secondary' ?>"><?= $labels[$st] ?? $st ?></span>
                                                    <small class="ms-2 text-status-meta">(Conf: <?= (int)($c['confirmed_count'] ?? 0) ?>, Att: <?= (int)($c['pending_count'] ?? 0) ?>)</small>
                                                    <?php if (!empty($c['confirmed_passengers'])): ?>
                                                        <div class="small text-status-meta mt-1">Passagers: <?= htmlspecialchars($c['confirmed_passengers']) ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= (new DateTime($c['depart']))->format('d/m/Y H\hi') ?></td>
                                                <td><?= isset($c['places_restantes']) ? (int)$c['places_restantes'] : (int)($c['vehicle_places'] ?? 0) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($historyPassenger)): ?>
                            <h5 class="mt-4 mb-2">En tant que passager</h5>
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Trajet</th>
                                            <th>Départ</th>
                                            <th>Véhicule</th>
                                            <th>Prix</th>
                                            <th>Conducteur</th>
                                            <th>Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($historyPassenger as $p): ?>
                                            <tr>
                                                <td><?= (int)$p['covoiturage_id'] ?></td>
                                                <td><?= htmlspecialchars($p['adresse_depart']) ?> → <?= htmlspecialchars($p['adresse_arrivee']) ?></td>
                                                <td><?= (new DateTime($p['depart']))->format('d/m/Y H\hi') ?></td>
                                                <td><?= htmlspecialchars(trim(($p['vehicle_marque'] ?? '') . ' ' . ($p['vehicle_modele'] ?? ''))) ?></td>
                                                <td><?= number_format((float)($p['prix'] ?? 0), 2, ',', ' ') ?> €</td>
                                                <td><?= htmlspecialchars($p['driver_pseudo']) ?></td>
                                                <td>
                                                    <?php if ($p['status'] === 'confirmee'): ?>
                                                        <span class="badge bg-success">Confirmée</span>
                                                    <?php elseif ($p['status'] === 'en_attente_validation'): ?>
                                                        <span class="badge bg-warning text-dark">En attente</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Annulée</span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($p['covoit_status'])): ?>
                                                        <?php $cv = (string)$p['covoit_status'];
                                                        $cvLabel = ['en_attente' => 'En attente', 'demarre' => 'Démarré', 'termine' => 'Terminé', 'annule' => 'Annulé'][$cv] ?? $cv;
                                                        ?>
                                                        <small class="text-status-meta ms-2">Trajet: <?= htmlspecialchars($cvLabel) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">Aucun élément d'historique pour le moment.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <a href="/liste-covoiturages" class="btn btn-inscription">Rechercher un trajet</a>
    </div>
</div>