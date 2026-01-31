<?php
// Variables: $ride (array from CovoiturageRepository::findOneWithVehicleById)
?>
<div class="ride-show container py-4">
    <?php
    $d = null;
    try {
        $d = new DateTime((string)($ride['depart'] ?? ''));
    } catch (Throwable $e) {
    }
    $avatar = !empty($ride['driver_photo']) ? $ride['driver_photo'] : (defined('DEFAULT_AVATAR_URL') ? DEFAULT_AVATAR_URL : '/assets/images/logo.svg');
    $driverName = (string)($ride['driver_pseudo'] ?? ('#' . (int)($ride['driver_id'] ?? 0)));
    $veh = trim(((string)($ride['vehicle_marque'] ?? '')) . ' ' . ((string)($ride['vehicle_modele'] ?? '')));
    $veh = $veh !== '' ? $veh : 'Véhicule';
    $couleur = (string)($ride['vehicle_couleur'] ?? '');
    $immat = (string)($ride['vehicle_immatriculation'] ?? '');
    ?>

    <div class="row g-4">
        <!-- Colonne gauche: résumé visuel -->
        <div class="ride-summary col-12 col-lg-5">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <img src="<?= htmlspecialchars($avatar) ?>" alt="Avatar conducteur" class="rounded-circle" style="width:72px;height:72px;object-fit:cover;">
                        <div>
                            <div class="h5 mb-1"><?= htmlspecialchars($driverName) ?></div>
                            <div class="text-muted small">Conducteur</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="fw-semibold">Trajet</div>
                        <div class="text-break"><?= htmlspecialchars((string)($ride['adresse_depart'] ?? '')) ?> → <?= htmlspecialchars((string)($ride['adresse_arrivee'] ?? '')) ?></div>
                    </div>
                    <div class="mb-3">
                        <div class="fw-semibold">Date & heure</div>
                        <div><?= $d ? $d->format('d/m/Y H\hi') : '' ?></div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success-subtle text-success border border-success">Prix&nbsp;: <?= htmlspecialchars(number_format((float)($ride['prix'] ?? 0), 2, ',', ' ')) ?> crédits</span>
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary">Places&nbsp;: <?= (int)($ride['vehicle_places'] ?? 0) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Colonne droite: détails -->
        <div class="ride-detail col-12 col-lg-7">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h4 mb-3">Détail du covoiturage</h2>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Véhicule</dt>
                        <dd class="col-sm-8">
                            <?= htmlspecialchars($veh) ?><?php if ($couleur): ?>, <span style="text-transform:capitalize;"><?= htmlspecialchars($couleur) ?></span><?php endif; ?>
                        <?php if ($immat): ?><small class="text-muted ms-2">[<?= htmlspecialchars($immat) ?>]</small><?php endif; ?>
                        </dd>
                        <dt class="col-sm-4">Conducteur</dt>
                        <dd class="col-sm-8 d-flex align-items-center gap-2">
                            <img src="<?= htmlspecialchars($avatar) ?>" alt="Avatar" class="rounded-circle" style="width:32px;height:32px;object-fit:cover;">
                            <span><?= htmlspecialchars($driverName) ?></span>
                        </dd>
                        <dt class="col-sm-4">Départ</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars((string)($ride['adresse_depart'] ?? '')) ?></dd>
                        <dt class="col-sm-4">Destination</dt>                                      
                        <dd class="col-sm-8"><?= htmlspecialchars((string)($ride['adresse_arrivee'] ?? '')) ?></dd>
                        <?php
                        // --- Préférences véhicule : mêmes règles que la liste ---
                        $prefsRaw = (string)($ride['vehicle_preferences'] ?? '');
                        $prefs = explode(',', $prefsRaw);

                        $allowedPrefs = ['fumeur', 'non-fumeur', 'animaux', 'pas-animaux'];
                        $badges = [];

                        foreach ($prefs as $pref) {
                            $prefClean = strtolower(trim($pref));
                            if ($prefClean !== '' && in_array($prefClean, $allowedPrefs, true)) {
                                $badges[] = $prefClean;
                            }
                        }

                        $customPref = trim((string)($ride['vehicle_prefs_custom'] ?? ''));
                        ?>

                        <?php if (!empty($badges) || $customPref !== ''): ?>
                            <dt class="col-sm-4">Préférences</dt>
                            <dd class="col-sm-8">
                                <div class="ride-prefs">
                                    <?php foreach ($badges as $b): ?>
                                        <span class="badge badge-pref <?= htmlspecialchars($b) ?> me-2">
                                            <?= htmlspecialchars($b) ?>
                                        </span>
                                    <?php endforeach; ?>

                                    <?php if ($customPref !== ''): ?>
                                        <span class="badge badge-pref custom">
                                            <?= htmlspecialchars($customPref) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </dd>
                        <?php endif; ?>

                    </dl>
                    <div class="mt-4 d-flex gap-2">
                        <a class="btn btn-outline-success" href="/liste-covoiturages">Retour à la liste</a>
                        <?php // CTA futur (réserver, contaster le conducteur, etc.) 
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>