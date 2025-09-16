<div class="container">
    <div class="text-center mt-3">
        <h1>Liste des covoiturages</h1>
    </div>
    <section class="container mt-5 ">
        <div class="col-lg-6 col-12 d-flex justify-content-center mb-4  m-auto">
            <div class="form-box rounded p-4 w-100 ">
                <form method="get" action="/liste-covoiturages">
                    <div class="mb-3">
                        <label class="form-label">Ville de départ :</label>
                        <input type="text" name="depart" class="form-control" placeholder="Ex : Fleurance" value="<?= htmlspecialchars($criteria['depart'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ville d’arrivée :</label>
                        <input type="text" name="arrivee" class="form-control" placeholder="Ex : Auch" value="<?= htmlspecialchars($criteria['arrivee'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date de départ :</label>
                        <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($criteria['date'] ?? '') ?>">
                    </div>
                    <?php if (!empty($criteria['pref']) && is_array($criteria['pref'])): ?>
                        <?php foreach ($criteria['pref'] as $p): ?>
                            <input type="hidden" name="pref[]" value="<?= htmlspecialchars($p) ?>">
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-inscription fw-semibold d-block m-auto">Rechercher</button>
                    <a href="/liste-covoiturages" class="btn btn-secondary fw-semibold d-block m-auto mt-2">Réinitialiser</a>
                </form>
            </div>
        </div>
    </section>
    <!-- Filtre & tri -->
    <section class="filter-section m-5">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between flex-wrap filter-bar px-3 py-2 rounded-2">
                <div class="dropdown">
                    <button class="btn btn-filter dropdown-toggle" data-bs-toggle="dropdown">
                        Filtrer
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <?php
                        $baseParams = [
                            'depart' => $criteria['depart'] ?? '',
                            'arrivee' => $criteria['arrivee'] ?? '',
                            'date'    => $criteria['date'] ?? '',
                            'sort'    => $criteria['sort'] ?? '',
                            'dir'     => $criteria['dir'] ?? ''
                        ];
                        $selected = (array)($criteria['pref'] ?? []);
                        ?>
                        <li>
                            <form method="get" action="/liste-covoiturages" class="px-3 py-2">
                                <?php foreach ($baseParams as $k => $v): ?>
                                    <?php if ($v !== '' && $v !== null): ?>
                                        <input type="hidden" name="<?= htmlspecialchars($k) ?>" value="<?= htmlspecialchars($v) ?>">
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="pref_animaux" name="pref[]" value="animaux" <?= in_array('animaux', $selected, true) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="pref_animaux">Animaux acceptés</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="pref_pas_animaux" name="pref[]" value="pas-animaux" <?= in_array('pas-animaux', $selected, true) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="pref_pas_animaux">Pas d'animaux</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="pref_fumeur" name="pref[]" value="fumeur" <?= in_array('fumeur', $selected, true) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="pref_fumeur">Fumeur</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="pref_non_fumeur" name="pref[]" value="non-fumeur" <?= in_array('non-fumeur', $selected, true) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="pref_non_fumeur">Non-fumeur</label>
                                </div>
                                <button type="submit" class="btn btn-sm btn-primary mt-2">Appliquer</button>
                                <a class="btn btn-sm btn-link mt-2" href="/liste-covoiturages">Réinitialiser</a>
                            </form>
                        </li>
                    </ul>
                </div>
                <div class="dropdown">
                    <button class="btn btn-sort dropdown-toggle" data-bs-toggle="dropdown">Trier par</button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <?php
                        // Conserve les critères; change sort/dir
                        $baseParams = [
                            'depart' => $criteria['depart'] ?? '',
                            'arrivee' => $criteria['arrivee'] ?? '',
                            'date' => $criteria['date'] ?? '',
                            'pref' => $criteria['pref'] ?? []
                        ];
                        ?>
                        <li><a class="dropdown-item" href="/liste-covoiturages?<?= http_build_query($baseParams + ['sort' => 'price', 'dir' => 'asc']) ?>">Crédits croissants</a></li>
                        <li><a class="dropdown-item" href="/liste-covoiturages?<?= http_build_query($baseParams + ['sort' => 'price', 'dir' => 'desc']) ?>">Crédits décroissants</a></li>
                        <li><a class="dropdown-item" href="/liste-covoiturages?<?= http_build_query($baseParams + ['sort' => 'date', 'dir' => 'asc']) ?>">Date la plus proche</a></li>
                        <li><a class="dropdown-item" href="/liste-covoiturages?<?= http_build_query($baseParams + ['sort' => 'date', 'dir' => 'desc']) ?>">Date la plus lointaine</a></li>
                    </ul>
                </div>
            </div>
            <?php if (!empty($criteria['pref'])): ?>
                <div class="px-3 mt-2 d-flex flex-wrap gap-2">
                    <?php
                    $labels = [
                        'animaux' => 'Animaux acceptés',
                        'pas-animaux' => "Pas d'animaux",
                        'fumeur' => 'Fumeur',
                        'non-fumeur' => 'Non-fumeur',
                    ];
                    $baseParams = [
                        'depart' => $criteria['depart'] ?? '',
                        'arrivee' => $criteria['arrivee'] ?? '',
                        'date' => $criteria['date'] ?? '',
                        'sort' => $criteria['sort'] ?? '',
                        'dir'  => $criteria['dir'] ?? ''
                    ];
                    $current = (array)$criteria['pref'];
                    foreach ($current as $rm) {
                        $remain = array_values(array_diff($current, [$rm]));
                        $params = $baseParams;
                        if (!empty($remain)) {
                            $params['pref'] = $remain;
                        }
                        $url = '/liste-covoiturages?' . http_build_query($params);
                        $text = $labels[$rm] ?? $rm;
                        echo '<a class="badge rounded-pill text-bg-success text-decoration-none" href="' . htmlspecialchars($url) . '">' . htmlspecialchars($text) . ' ×</a>';
                    }
                    // Lien pour tout retirer (préserve autres critères)
                    $urlClear = '/liste-covoiturages?' . http_build_query($baseParams);
                    echo '<a class="badge rounded-pill text-bg-secondary text-decoration-none" href="' . htmlspecialchars($urlClear) . '">Tout retirer</a>';
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <section class="rides-section pb-5">
        <div class="container">
            <div class="row row-cols-1 row-cols-md-2 g-4">
                <?php if (!empty($results)): ?>
                    <?php foreach ($results as $ride): ?>
                        <div class="col">
                            <div class="carpool-card d-flex flex-column justify-content-between">
                                <div class="card-header d-flex justify-content-between mb-3">
                                    <div class="card-info">
                                        <?php
                                        $d = new DateTime($ride['depart']);
                                        $price = number_format((float)$ride['prix'], 2, ',', ' ');
                                        ?>
                                        <span class="date"><?= $d->format('d/m/Y') ?></span>
                                        <span class="sep">•</span>
                                        <span class="price"><?= $price ?></span>
                                    </div>
                                    <div class="card-time">
                                        <i class="bi bi-clock-fill"></i>
                                        <span><?= $d->format('H\hi') ?></span>
                                    </div>
                                </div>
                                <div class="card-body d-flex align-items-start justify-content-between flex-wrap mb-3">
                                    <?php $avatar = !empty($ride['driver_photo']) ? $ride['driver_photo'] : (defined('DEFAULT_AVATAR_URL') ? DEFAULT_AVATAR_URL : '/assets/images/logo.svg'); ?>
                                    <img src="<?= htmlspecialchars($avatar) ?>" alt="Avatar conducteur" class="avatar rounded-circle" style="width:48px;height:48px;object-fit:cover;" onerror="this.onerror=null;this.src='<?= defined('DEFAULT_AVATAR_URL') ? DEFAULT_AVATAR_URL : '/assets/images/logo.svg' ?>';">
                                    <div class="details flex-grow-1 px-3 m-auto">
                                        <h5><?= htmlspecialchars($ride['adresse_depart']) ?> → <?= htmlspecialchars($ride['adresse_arrivee']) ?></h5>
                                        <ul class="mb-0">
                                            <li>Conducteur : <strong><?= htmlspecialchars($ride['driver_pseudo'] ?? ('#' . (int)$ride['driver_id'])) ?></strong></li>
                                            <?php if (isset($ride['driver_note'])): ?>
                                                <li>Note : <?= number_format((float)$ride['driver_note'], 1, ',', ' ') ?>/5</li>
                                            <?php endif; ?>
                                            <?php if (!empty($ride['vehicle_marque']) || !empty($ride['vehicle_modele'])): ?>
                                                <li>Véhicule : <?= htmlspecialchars(trim(($ride['vehicle_marque'] ?? '') . ' ' . ($ride['vehicle_modele'] ?? ''))) ?><?php if (!empty($ride['vehicle_couleur'])): ?>, <span style="text-transform:capitalize;"><?= htmlspecialchars($ride['vehicle_couleur']) ?></span><?php endif; ?></li>
                                            <?php endif; ?>
                                            <?php if (isset($ride['places_restantes'])): ?>
                                                <li>Places restantes : <?= max(0, (int)$ride['places_restantes']) ?></li>
                                            <?php elseif (isset($ride['vehicle_places'])): ?>
                                                <li>Places dispo : <?= (int)$ride['vehicle_places'] ?></li>
                                            <?php endif; ?>
                                            <?php
                                            $prefs = [];
                                            if (!empty($ride['vehicle_preferences'])) {
                                                $prefs = array_filter(array_map('trim', explode(',', (string)$ride['vehicle_preferences'])));
                                            }
                                            if (!empty($ride['vehicle_prefs_custom'])) {
                                                $prefs[] = trim((string)$ride['vehicle_prefs_custom']);
                                            }
                                            if (!empty($prefs)):
                                            ?>
                                                <li>Préférences : <?= htmlspecialchars(implode(' • ', $prefs)) ?></li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card-footer d-flex justify-content-end">
                                    <div>
                                        <?php
                                        $isLogged = isset($_SESSION['user']);
                                        $isDriver = $isLogged && ((int)$ride['driver_id'] === (int)$_SESSION['user']['id']);
                                        $placesRestantes = isset($ride['places_restantes'])
                                            ? max(0, (int)$ride['places_restantes'])
                                            : (isset($ride['vehicle_places']) ? (int)$ride['vehicle_places'] : 0);
                                        $isPast = false;
                                        try {
                                            $isPast = (new DateTime($ride['depart'])) < new DateTime();
                                        } catch (Throwable $e) {
                                        }
                                        ?>

                                        <?php if (!$isLogged): ?>
                                            <button type="button" class="btn btn-inscription" data-bs-toggle="modal" data-bs-target="#authModal" data-start="login">Se connecter pour participer</button>
                                        <?php elseif ($isDriver): ?>
                                            <button class="btn btn-secondary" disabled>Vous êtes le conducteur</button>
                                        <?php elseif ($isPast): ?>
                                            <button class="btn btn-secondary" disabled>Trajet passé</button>
                                        <?php elseif ($placesRestantes <= 0): ?>
                                            <button class="btn btn-secondary" disabled>Complet</button>
                                        <?php elseif (!empty($ride['has_my_participation']) && (int)$ride['has_my_participation'] === 1): ?>
                                            <?php $status = (string)($ride['my_participation_status'] ?? ''); ?>
                                            <?php if ($status === 'confirmee'): ?>
                                                <button class="btn btn-secondary" disabled>Participation confirmée</button>
                                            <?php elseif ($status === 'en_attente_validation' || $status === ''): ?>
                                                <button class="btn btn-secondary" disabled>Demande envoyée</button>
                                            <?php else: ?>
                                                <button class="btn btn-secondary" disabled>Participation en cours</button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <?php
                                            $roleId = (int)($_SESSION['user']['role_id'] ?? 0);
                                            $myCredits = (int)($_SESSION['user']['credits'] ?? 0);
                                            $prix = (float)($ride['prix'] ?? 0);
                                            $cost = max(1, (int) ceil($prix));
                                            ?>
                                            <?php if ($roleId !== 1): ?>
                                                <button class="btn btn-secondary" disabled>Réservé aux Utilisateurs</button>
                                            <?php elseif ($myCredits < $cost): ?>
                                                <a class="btn btn-secondary" href="/mes-credits" title="Solde: <?= (int)$myCredits ?>">Crédits insuffisants (<?= (int)$cost ?>)</a>
                                            <?php else: ?>
                                                <form action="/participations/create" method="POST" class="d-inline js-confirm"
                                                    data-confirm-steps="2"
                                                    data-confirm-text="Confirmer la participation ?"
                                                    data-confirm-text2="Cette action débitera <?= (int)$cost ?> crédits. Confirmez-vous ?"
                                                    data-confirm-variant="warning">
                                                    <input type="hidden" name="csrf" value="<?= \App\Security\Csrf::token() ?>">
                                                    <input type="hidden" name="covoiturage_id" value="<?= (int)$ride['id'] ?>">
                                                    <button type="submit" class="btn btn-inscription">Participer</button>
                                                </form>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col">
                        <div class="alert alert-info">Aucun covoiturage trouvé. Essayez d'élargir votre recherche.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>