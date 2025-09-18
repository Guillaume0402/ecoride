<?php
// Variables attendues: $profileUser (array), $vehicles (VehicleEntity[]), $reviews (array)

// Calcul des infos d'étoiles (moyenne et nombre d'avis)
$reviewsCount = is_array($reviews ?? null) ? count($reviews) : 0;
$avgNote = isset($profileUser['note']) ? (float)$profileUser['note'] : null;
if ($avgNote === null && $reviewsCount > 0) {
    $sum = 0; $n = 0;
    foreach ($reviews as $r) { if (isset($r['rating'])) { $sum += (int)$r['rating']; $n++; } }
    $avgNote = $n ? $sum / $n : null;
}
$rounded = $avgNote !== null ? (int) round(min(5, max(0, $avgNote))) : 0;

// Préférences globales (fusion des véhicules)
$prefsChips = [];
if (!empty($vehicles)) {
    $allowed = ['fumeur','non-fumeur','animaux','pas-animaux'];
    foreach ($vehicles as $v) {
        $prefs = explode(',', $v->getPreferences() ?? '');
        foreach ($prefs as $p) {
            $pc = strtolower(trim($p));
            if (in_array($pc, $allowed)) { $prefsChips[$pc] = true; }
        }
        if ($v->getCustomPreferences()) { $prefsChips[trim($v->getCustomPreferences())] = true; }
    }
}
?>

<div class="container py-5">
    <!-- Bandeau d'en-tête du profil -->
    <div class="card shadow-lg border-0 rounded-4 p-4 mb-5" style="background:rgba(0,0,0,0.10);backdrop-filter:blur(2px);">
        <div class="row g-4 align-items-center">
            <div class="col-lg-4">
                <div class="d-flex flex-column align-items-start gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <div>
                            <?php for ($i=0;$i<5;$i++): ?>
                                <i class="bi bi-star-fill <?= $i < $rounded ? 'text-warning' : 'text-secondary' ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <?php if ($reviewsCount > 0): ?>
                            <span class="small text-muted">(<?= (int)$reviewsCount ?>)</span>
                        <?php endif; ?>
                    </div>
                    <?php $avatar = !empty($profileUser['photo']) ? $profileUser['photo'] : (defined('DEFAULT_AVATAR_URL') ? DEFAULT_AVATAR_URL : '/assets/images/logo.svg'); ?>
                    <div class="d-flex align-items-center gap-3 mt-1">
                        <img src="<?= htmlspecialchars($avatar) ?>" alt="Avatar" class="rounded-circle bg-white" style="width:72px;height:72px;object-fit:cover;" onerror="this.onerror=null;this.src='<?= defined('DEFAULT_AVATAR_URL') ? DEFAULT_AVATAR_URL : '/assets/images/logo.svg' ?>';">
                        <div>
                            <h2 class="fw-bold mb-1" style="letter-spacing:.2px;">
                                <?= htmlspecialchars($profileUser['pseudo'] ?? ('#' . (int)($profileUser['id'] ?? 0))) ?>
                            </h2>
                            <div class="small text-muted">Membre depuis <?= !empty($profileUser['created_at']) ? date('d/m/Y', strtotime($profileUser['created_at'])) : 'N/A' ?></div>
                        </div>
                    </div>
                    <?php if (!empty($prefsChips)): ?>
                        <ul class="list-unstyled small mt-3 mb-0">
                            <?php foreach (array_keys($prefsChips) as $chip): ?>
                                <li><?= htmlspecialchars($chip) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="row row-cols-1 row-cols-md-2 g-3 small">
                    <div class="col">
                        <div class="fw-semibold">Date d'inscription</div>
                        <div><?= !empty($profileUser['created_at']) ? date('d/m/Y', strtotime($profileUser['created_at'])) : '—' ?></div>
                    </div>
                    <div class="col">
                        <div class="fw-semibold">Chauffeur</div>
                        <div><?= (!empty($profileUser['travel_role']) && in_array($profileUser['travel_role'], ['chauffeur','les-deux'])) ? 'Oui' : 'Non' ?></div>
                    </div>
                    <div class="col">
                        <div class="fw-semibold">Passager</div>
                        <div><?= (!empty($profileUser['travel_role']) && in_array($profileUser['travel_role'], ['passager','les-deux'])) ? 'Oui' : 'Non' ?></div>
                    </div>
                    <?php
                    // Affiche jusqu'à 2 véhicules en résumé
                    $v1 = $vehicles[0] ?? null; $v2 = $vehicles[1] ?? null;
                    ?>
                    <div class="col">
                        <div class="fw-semibold">Véhicule 1</div>
                        <div><?= $v1 ? htmlspecialchars($v1->getMarque() . ' ' . $v1->getModele()) : '—' ?></div>
                    </div>
                    <div class="col">
                        <div class="fw-semibold">Type énergie</div>
                        <div><?= $v1 ? htmlspecialchars($v1->getFuelTypeName() ?? '—') : '—' ?></div>
                    </div>
                    <div class="col">
                        <div class="fw-semibold">Immatriculation</div>
                        <div><?= $v1 ? htmlspecialchars($v1->getImmatriculation()) : '—' ?></div>
                    </div>
                    <div class="col">
                        <div class="fw-semibold">Véhicule 2</div>
                        <div><?= $v2 ? htmlspecialchars($v2->getMarque() . ' ' . $v2->getModele()) : '—' ?></div>
                    </div>
                    <div class="col">
                        <div class="fw-semibold">Type énergie</div>
                        <div><?= $v2 ? htmlspecialchars($v2->getFuelTypeName() ?? '—') : '—' ?></div>
                    </div>
                    <div class="col">
                        <div class="fw-semibold">Immatriculation</div>
                        <div><?= $v2 ? htmlspecialchars($v2->getImmatriculation()) : '—' ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des véhicules détaillée -->
    <h3 class="mt-4 mb-3">Véhicule(s)</h3>
    <?php if (!empty($vehicles)): ?>
        <div class="row g-3">
            <?php foreach ($vehicles as $v): ?>
                <div class="col-md-6">
                    <div class="card p-3 bg-dark bg-opacity-25 border-0 h-100">
                        <div class="fw-semibold mb-2">
                            <?= htmlspecialchars($v->getMarque()) . ' ' . htmlspecialchars($v->getModele()) ?>
                        </div>
                        <div class="row small g-2">
                            <div class="col-6">Couleur: <?= htmlspecialchars($v->getCouleur()) ?></div>
                            <div class="col-6">Plaque: <?= htmlspecialchars($v->getImmatriculation()) ?></div>
                            <div class="col-6">1ère immat.: <?= date('d/m/Y', strtotime($v->getDatePremiereImmatriculation())) ?></div>
                            <div class="col-6">Places: <?= (int)$v->getPlacesDispo() ?></div>
                            <div class="col-12">
                                <strong>Préférences:</strong>
                                <?php
                                $prefs = explode(',', $v->getPreferences() ?? '');
                                $allowed = ['fumeur', 'non-fumeur', 'animaux', 'pas-animaux'];
                                $chips = [];
                                foreach ($prefs as $p) { $pc = strtolower(trim($p)); if (in_array($pc, $allowed)) { $chips[] = $pc; } }
                                if ($v->getCustomPreferences()) { $chips[] = trim($v->getCustomPreferences()); }
                                echo $chips ? htmlspecialchars(implode(' • ', $chips)) : '—';
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-secondary">Aucun véhicule renseigné.</div>
    <?php endif; ?>

    <!-- Avis -->
    <h3 class="mt-5 mb-3 text-center">Les avis des voyageurs</h3>
    <?php if (!empty($reviews)): ?>
        <div class="row g-4">
            <?php foreach ($reviews as $r): ?>
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 rounded-4 p-4 h-100" style="background:rgba(0,0,0,0.10);backdrop-filter:blur(2px);">
                        <div class="mb-2">
                            <?php $rr = (int)($r['rating'] ?? 0); for ($i=0;$i<5;$i++): ?>
                                <i class="bi bi-star-fill <?= $i < $rr ? 'text-warning' : 'text-secondary' ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <?php if (!empty($r['comment'])): ?>
                            <blockquote class="blockquote mb-3">
                                <p class="mb-0 small">"<?= htmlspecialchars($r['comment']) ?>"</p>
                            </blockquote>
                        <?php endif; ?>
                        <div class="d-flex align-items-center gap-2 mt-2">
                            <img src="<?= defined('DEFAULT_AVATAR_URL') ? DEFAULT_AVATAR_URL : '/assets/images/logo.svg' ?>" alt="Avatar" class="rounded-circle bg-white" style="width:32px;height:32px;object-fit:cover;">
                            <div class="small text-muted">
                                <?php if (isset($r['created_at_ms'])): ?>
                                    <div>Le <?= date('d/m/Y H:i', intdiv((int)$r['created_at_ms'], 1000)) ?></div>
                                <?php else: ?>
                                    <div>Voyageur</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-secondary text-center">Pas encore d'avis publiés.</div>
    <?php endif; ?>
</div>