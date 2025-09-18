<?php
// Variables attendues: $profileUser (array), $vehicles (VehicleEntity[]), $reviews (array)

// Prépare les données d'avis
$reviewsCount = is_array($reviews ?? null) ? count($reviews) : 0;
?>

<div class="container py-5 my-profil-page">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-lg border-0 rounded-4 p-4 mb-5" style="background:rgba(0,0,0,0.10);backdrop-filter:blur(2px);">
                <div class="row g-0 align-items-center">
                    <div class="col-md-4 text-center text-md-start mb-4 mb-md-0">
                        <div class="d-flex flex-column align-items-center align-items-md-start gap-2">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="fs-5">
                                    <?php
                                    $avg = isset($profileUser['note']) ? (float)$profileUser['note'] : null;
                                    if ($avg === null && $reviewsCount > 0) {
                                        $s=0;$n=0; foreach($reviews as $r){ if(isset($r['rating'])){$s+=(int)$r['rating'];$n++;} }
                                        $avg = $n? ($s/$n) : 0;
                                    }
                                    $fill = (int) round(min(5,max(0,$avg ?? 0))); 
                                    for ($i = 0; $i < 5; $i++): ?>
                                        <i class="bi bi-star-fill <?= $i < $fill ? 'text-warning' : 'text-secondary' ?>"></i>
                                    <?php endfor; ?>
                                </span>
                                <span class="ms-2">(<?= (int)$reviewsCount ?>)</span>
                            </div>
                            <h2 class="fw-bold mb-2"><?= htmlspecialchars($profileUser['pseudo'] ?? '') ?></h2>
                            <?php $__avatar = !empty($profileUser['photo']) ? $profileUser['photo'] : (defined('DEFAULT_AVATAR_URL') ? DEFAULT_AVATAR_URL : '/assets/images/logo.svg'); ?>
                            <img src="<?= htmlspecialchars($__avatar) ?>" alt="Avatar" class="rounded-circle bg-white mb-2" style="width:70px;height:70px;object-fit:cover;" onerror="this.onerror=null;this.src='<?= defined('DEFAULT_AVATAR_URL') ? DEFAULT_AVATAR_URL : '/assets/images/logo.svg' ?>';">
                            <ul class="list-unstyled small mb-3">
                                <?php
                                // Affiche quelques préférences génériques si présentes dans les véhicules
                                $prefMap = ['animaux'=>'Animaux acceptés','pas-animaux'=>'Pas d\'animaux','fumeur'=>'Fumeur','non-fumeur'=>'Sans tabac'];
                                $prefsOut = [];
                                foreach (($vehicles ?? []) as $v) {
                                    foreach (explode(',', $v->getPreferences() ?? '') as $p) {
                                        $pc = strtolower(trim($p));
                                        if (isset($prefMap[$pc])) $prefsOut[$pc] = $prefMap[$pc];
                                    }
                                }
                                if (empty($prefsOut)) {
                                    $prefsOut = ['animaux' => 'Animaux acceptés', 'non-fumeur' => 'Sans tabac'];
                                }
                                foreach ($prefsOut as $label) { echo '<li>'.htmlspecialchars($label).'</li>'; }
                                ?>
                            </ul>
                            <div class="d-flex flex-md-row gap-2">
                                <a href="#" class="btn btn-custom-outline px-4">Historique</a>
                                <!-- Boutons d'édition retirés pour le profil public -->
                            </div>
                            <!-- Bouton Ajouter un véhicule retiré pour le profil public -->
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="row g-3">
                            <div class="col-6 col-lg-4">
                                <div class="fw-semibold">Date d'inscription</div>
                                <div class="small"><?= !empty($profileUser['created_at']) ? date('d-m-Y', strtotime($profileUser['created_at'])) : 'Non renseignée' ?></div>
                            </div>
                            <div class="col-6 col-lg-4">
                                <div class="fw-semibold">Chauffeur</div>
                                <div class="small"><?= (!empty($profileUser['travel_role']) && in_array($profileUser['travel_role'], ['chauffeur', 'les-deux'])) ? 'Oui' : 'Non' ?></div>
                            </div>
                            <div class="col-6 col-lg-4">
                                <div class="fw-semibold">Passager</div>
                                <div class="small"><?= (!empty($profileUser['travel_role']) && in_array($profileUser['travel_role'], ['passager', 'les-deux'])) ? 'Oui' : 'Non' ?></div>
                            </div>
                            <?php if (!empty($vehicles)): ?>
                                <!-- Onglets -->
                                <ul class="nav nav-tabs mb-4" id="vehicleTabs" role="tablist">
                                    <?php foreach ($vehicles as $index => $vehicle): ?>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link <?= $index === 0 ? 'active' : '' ?>" id="vehicle-tab-<?= $index ?>"
                                                data-bs-toggle="tab" data-bs-target="#vehicle-<?= $index ?>" type="button" role="tab">
                                                Véhicule <?= $index + 1 ?>
                                            </button>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <!-- Contenu des onglets -->
                                <div class="tab-content" id="vehicleTabsContent">
                                    <?php foreach ($vehicles as $index => $vehicle): ?>
                                        <div class="tab-pane fade <?= $index === 0 ? 'show active' : '' ?>" id="vehicle-<?= $index ?>" role="tabpanel">
                                            <div class="card bg-transparent border rounded-3 p-3">
                                                <div class="fw-bold mb-3">
                                                    Modèle : <?= htmlspecialchars($vehicle->getMarque()) . ' ' . htmlspecialchars($vehicle->getModele()) ?>
                                                </div>
                                                <div class="row small">
                                                    <div class="col-md-4">Couleur : <?= htmlspecialchars($vehicle->getCouleur()) ?></div>
                                                    <div class="col-md-4">Immatriculation : <?= htmlspecialchars($vehicle->getImmatriculation()) ?></div>
                                                    <div class="col-md-4">Date : <?= date('d/m/Y', strtotime($vehicle->getDatePremiereImmatriculation())) ?></div>
                                                    <div class="col-md-4">Énergie : <?= htmlspecialchars($vehicle->getFuelTypeName() ?? 'Non renseigné') ?></div>
                                                    <div class="col-md-4">Places : <?= htmlspecialchars($vehicle->getPlacesDispo()) ?></div>
                                                    <div class="col-12 mt-3">
                                                        <strong>Préférences :</strong><br>
                                                        <?php
                                                        $prefs = explode(',', $vehicle->getPreferences() ?? '');
                                                        $allowedPrefs = ['fumeur', 'non-fumeur', 'animaux', 'pas-animaux'];
                                                        foreach ($prefs as $pref) {
                                                            $prefClean = strtolower(trim($pref));
                                                            if (in_array($prefClean, $allowedPrefs)) {
                                                                echo '<span class="badge badge-pref ' . $prefClean . ' me-2">' . htmlspecialchars($prefClean) . '</span>';
                                                            }
                                                        }
                                                        if (!empty($vehicle->getCustomPreferences())) {
                                                            echo '<span class="badge badge-pref custom">' . htmlspecialchars(trim($vehicle->getCustomPreferences())) . '</span>';
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                                <!-- Boutons Modifier/Supprimer retirés pour le profil public -->
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="p-3 border rounded-3 bg-dark bg-opacity-25 w-100">Aucun véhicule renseigné</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h2 class="text-center mb-5">Les avis des voyageurs</h2>
    <?php if ($reviewsCount > 0): ?>
        <div class="row justify-content-center g-4">
            <?php foreach ($reviews as $r): ?>
                <div class="col-md-4">
                    <div class="card shadow border-0 rounded-4 p-4 h-100" style="background:rgba(0,0,0,0.10);backdrop-filter:blur(2px);">
                        <div class="mb-2">
                            <?php $rr = (int)($r['rating'] ?? 0); for ($j = 0; $j < 5; $j++): ?>
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
                                <div class="fw-semibold">Voyageur</div>
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
</div>