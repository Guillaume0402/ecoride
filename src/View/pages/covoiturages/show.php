<?php
// Prépare les données d'avis
$reviewsCount = isset($reviewsCount) ? (int)$reviewsCount : (is_array($reviews ?? null) ? count($reviews) : 0);
if (!isset($avgRating)) {
    // Si non fourni, calcule à partir des avis
    $s = 0;
    $n = 0;
    if (is_array($reviews ?? null)) {
        foreach ($reviews as $r) {
            if (isset($r['rating']) && is_numeric($r['rating'])) {
                $s += (int)$r['rating'];
                $n++;
            }
        }
    }
    $avgRating = $n ? round($s / $n, 1) : 0.0;
}

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

 <h2 class="text-center mb-5">Les avis des voyageurs</h2>
    <?php if ($reviewsCount > 0 && is_array($reviews ?? null)): ?>
        <div class="row justify-content-center g-4">
            <?php foreach ($reviews as $r): ?>
                <div class="col-sm-6 col-md-6 col-lg-4 col-xxl-3">
                    <div class="card shadow border-0 rounded-4 p-4 h-100" style="background:rgba(0,0,0,0.10);backdrop-filter:blur(2px);">
                        <div class="mb-2">
                            <?php $rr = (int)($r['rating'] ?? 0);
                            for ($j = 0; $j < 5; $j++): ?>
                                <i class="bi bi-star-fill <?= $j < $rr ? 'text-warning' : 'text-secondary' ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <?php if (!empty($r['comment'])): ?>
                            <blockquote class="blockquote mb-3">
                                <p class="mb-0">"<?= htmlspecialchars($r['comment']) ?>"</p>
                            </blockquote>
                        <?php endif; ?>
                        <div class="d-flex align-items-center gap-2 mt-3">
                            <img src="<?= defined('DEFAULT_AVATAR_URL') ? DEFAULT_AVATAR_URL : '/assets/images/logo.svg' ?>" alt="Avatar" class="rounded-circle bg-white" style="width:40px;height:40px;object-fit:cover;">
                            <div>
                                <div class="fw-semibold"><?= htmlspecialchars($r['passager_pseudo'] ?? 'Voyageur') ?></div>
                                <div class="small text-muted">
                                    <?php if (isset($r['created_at_ms'])): ?>
                                        Le <?= date('d/m/Y H:i', intdiv((int)$r['created_at_ms'], 1000)) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-secondary text-center">Pas encore d'avis publiés.</div>
    <?php endif; ?>