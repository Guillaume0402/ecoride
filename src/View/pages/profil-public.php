<?php
// Variables attendues: $profileUser (array), $vehicles (VehicleEntity[]), $reviews (array)
?>
<div class="container py-5">
    <h1 class="mb-4">Profil de <?= htmlspecialchars($profileUser['pseudo'] ?? ('#' . (int)($profileUser['id'] ?? 0))) ?></h1>

    <div class="card mb-4 p-3 bg-dark bg-opacity-25 border-0 rounded-3">
        <div class="d-flex align-items-center gap-3">
            <?php $avatar = !empty($profileUser['photo']) ? $profileUser['photo'] : (defined('DEFAULT_AVATAR_URL') ? DEFAULT_AVATAR_URL : '/assets/images/logo.svg'); ?>
            <img src="<?= htmlspecialchars($avatar) ?>" alt="Avatar" class="rounded-circle" style="width:80px;height:80px;object-fit:cover;" onerror="this.onerror=null;this.src='<?= defined('DEFAULT_AVATAR_URL') ? DEFAULT_AVATAR_URL : '/assets/images/logo.svg' ?>';">
            <div>
                <div class="fs-4 fw-semibold mb-1"><?= htmlspecialchars($profileUser['pseudo'] ?? '') ?></div>
                <div class="text-muted small">Membre depuis <?= !empty($profileUser['created_at']) ? date('d/m/Y', strtotime($profileUser['created_at'])) : 'N/A' ?></div>
                <?php if (isset($profileUser['note'])): ?>
                    <div class="small mt-1">Note: <?= number_format((float)$profileUser['note'], 1, ',', ' ') ?>/5</div>
                <?php endif; ?>
                <?php if (!empty($profileUser['travel_role'])): ?>
                    <div class="small">Rôles: <?= htmlspecialchars($profileUser['travel_role']) ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

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
                                $allowed = ['fumeur','non-fumeur','animaux','pas-animaux'];
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

    <h3 class="mt-5 mb-3">Avis des voyageurs</h3>
    <?php if (!empty($reviews)): ?>
        <div class="row g-3">
            <?php foreach ($reviews as $r): ?>
                <div class="col-md-6">
                    <div class="card p-3 bg-dark bg-opacity-25 border-0 h-100">
                        <div class="mb-1">Note: <strong><?= (int)($r['rating'] ?? 0) ?>/5</strong></div>
                        <?php if (!empty($r['comment'])): ?>
                            <div class="small">"<?= htmlspecialchars($r['comment']) ?>"</div>
                        <?php endif; ?>
                        <?php if (isset($r['created_at_ms'])): ?>
                            <div class="text-muted small mt-2">Le <?= date('d/m/Y H:i', intdiv((int)$r['created_at_ms'], 1000)) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-secondary">Pas encore d'avis publiés.</div>
    <?php endif; ?>
</div>
