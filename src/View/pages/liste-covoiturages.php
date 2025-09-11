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
                    <button type="submit" class="btn btn-inscription fw-semibold d-block m-auto">Rechercher</button>
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
                        <li><a class="dropdown-item" href="#">Animaux acceptés</a></li>
                        <li><a class="dropdown-item" href="#">Pas d'animaux</a></li>
                        <li><a class="dropdown-item" href="#">Fumeur</a></li>
                        <li><a class="dropdown-item" href="#">Non-fumeur</a></li>
                    </ul>
                </div>
                <div class="dropdown">
                    <button class="btn btn-sort dropdown-toggle" data-bs-toggle="dropdown">
                        Trier par
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#">Prix</a></li>
                        <li><a class="dropdown-item" href="#">Date</a></li>
                        <li><a class="dropdown-item" href="#">Éco-énergie</a></li>
                    </ul>
                </div>
            </div>
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
                                        <span class="price"><?= $price ?> €</span>
                                    </div>
                                    <div class="card-time">
                                        <i class="bi bi-clock-fill"></i>
                                        <span><?= $d->format('H\hi') ?></span>
                                    </div>
                                </div>
                                <div class="card-body d-flex align-items-start justify-content-between flex-wrap mb-3">
                                    <?php $avatar = !empty($ride['driver_photo']) ? $ride['driver_photo'] : '/assets/images/logo.svg'; ?>
                                    <img src="<?= htmlspecialchars($avatar) ?>" alt="Avatar conducteur" class="avatar rounded-circle" style="width:48px;height:48px;object-fit:cover;">
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
                                            <?php if (isset($ride['vehicle_places'])): ?>
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
                                <div class="card-footer d-flex justify-content-between">
                                    <small class="text-muted">Annonce #<?= (int)$ride['id'] ?></small>
                                    <div>
                                        <button class="btn btn-inscription" disabled>Participer</button>
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