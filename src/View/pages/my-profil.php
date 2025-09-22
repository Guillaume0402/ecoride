<?php
$user = $_SESSION['user'] ?? null;

if (!$user) {
    header('Location: /login');
    exit;
}
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
                                    <?php for ($i = 0; $i < 5; $i++): ?><i class="bi bi-star-fill text-warning"></i><?php endfor; ?>
                                </span>
                                <span class="ms-2">(24)</span>
                            </div>
                            <?php if (isset($_SESSION['user'])): ?>
                                <h2 class="fw-bold mb-2"><?= $_SESSION['user']['pseudo'] ?? '' ?></h2>
                                <?php $__avatar = !empty($user['photo']) ? $user['photo'] : (defined('DEFAULT_AVATAR_URL') ? DEFAULT_AVATAR_URL : '/assets/images/logo.svg'); ?>
                                <img src="<?= htmlspecialchars($__avatar) ?>" alt="Avatar" class="rounded-circle bg-white mb-2" style="width:70px;height:70px;object-fit:cover;" onerror="this.onerror=null;this.src='<?= defined('DEFAULT_AVATAR_URL') ? DEFAULT_AVATAR_URL : '/assets/images/logo.svg' ?>';">
                                <ul class="list-unstyled small mb-3">
                                    <li>Animaux accepté</li>
                                    <li>Sans tabac</li>
                                    <li>Sans nourriture</li>
                                    <li>Sans les mains</li>
                                </ul>
                                <div class="d-flex flex-md-row gap-2">
                                    <a href="#" class="btn btn-custom-outline px-4">Historique</a>
                                    <a href="/creation-profil" class="btn btn-custom-outline px-4">Modifier Profil</a>
                                </div>
                                <div class="text-center">
                                    <a href="/vehicle/create" class="btn btn-inscription px-4 mt-3">
                                        <i class="bi bi-plus-circle"></i> Ajouter un véhicule
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="row g-3">
                            <div class="col-6 col-lg-4">
                                <div class="fw-semibold">Date d'inscription</div>
                                <div class="small"><?= !empty($user['created_at']) ? date('d-m-Y', strtotime($user['created_at'])) : 'Non renseignée' ?></div>
                            </div>
                            <div class="col-6 col-lg-4">
                                <div class="fw-semibold">Chauffeur</div>
                                <div class="small"><?= (!empty($user['travel_role']) && in_array($user['travel_role'], ['chauffeur', 'les-deux'])) ? 'Oui' : 'Non' ?></div>
                            </div>
                            <div class="col-6 col-lg-4">
                                <div class="fw-semibold">Passager</div>
                                <div class="small"><?= (!empty($user['travel_role']) && in_array($user['travel_role'], ['passager', 'les-deux'])) ? 'Oui' : 'Non' ?></div>
                            </div>
                            <div class="col-6 col-lg-4">
                                <div class="fw-semibold">Crédits</div>
                                <div class="small"><?= isset($user['credits']) ? (int)$user['credits'] : 0 ?></div>
                            </div>
                            <!-- Historique des transactions déplacé vers /mes-credits -->
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
                                                <div class="d-flex justify-content-end mt-4">
                                                    <a href="/vehicle/edit?id=<?= $vehicle->getId() ?>" class="btn btn-custom-outline px-4">
                                                        <i class="bi bi-pencil-square me-1"></i> Modifier véhicule
                                                    </a>
                                                    <form method="POST" action="/vehicle/delete">
                                                        <input type="hidden" name="csrf" value="<?= \App\Security\Csrf::token() ?>">
                                                        <input type="hidden" name="vehicle_id" value="<?= $vehicle->getId() ?>">
                                                        <button type="submit" class="btn btn-inscription px-4 ms-2">
                                                            <i class="bi bi-trash me-1"></i> Supprimer
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="p-3 border rounded-3 bg-dark bg-opacity-25">
                                    Aucun véhicule renseigné
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <h2 class="text-center mb-5">Les avis des voyageurs</h2>
    <div class="row justify-content-center g-4">
        <?php for ($i = 0; $i < 3; $i++): ?>
            <div class="col-md-4">
                <div class="card shadow border-0 rounded-4 p-4 h-100" style="background:rgba(0,0,0,0.10);backdrop-filter:blur(2px);">
                    <div class="mb-2">
                        <?php for ($j = 0; $j < 5; $j++): ?><i class="bi bi-star-fill text-warning"></i><?php endfor; ?>
                    </div>
                    <blockquote class="blockquote mb-3">
                        <p class="mb-0">"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse varius enim in eros elementum tristique."</p>
                    </blockquote>
                    <div class="d-flex align-items-center gap-2 mt-3">
                        <img src="/assets/images/404.png" alt="Avatar" class="rounded-circle bg-white" style="width:40px;height:40px;object-fit:cover;">
                        <div>
                            <div class="fw-semibold">Name Surname</div>
                            <div class="small">Position, Company name</div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endfor; ?>
    </div>