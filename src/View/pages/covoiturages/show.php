<div class="container py-4">
    <h2 class="mb-3">Détail du covoiturage</h2>
    <div class="card shadow-sm">
        <div class="card-body">
            <?php
            $d = null;
            try {
                $d = new DateTime((string)($ride['depart'] ?? ''));
            } catch (Throwable $e) {
            }
            ?>
            <dl class="row">
                <dt class="col-sm-3">Départ</dt>
                <dd class="col-sm-9"><?= htmlspecialchars((string)($ride['adresse_depart'] ?? '')) ?></dd>
                <dt class="col-sm-3">Destination</dt>
                <dd class="col-sm-9"><?= htmlspecialchars((string)($ride['adresse_arrivee'] ?? '')) ?></dd>
                <dt class="col-sm-3">Date & heure</dt>
                <dd class="col-sm-9"><?= $d ? $d->format('d/m/Y H:i') : '' ?></dd>
                <dt class="col-sm-3">Prix</dt>
                <dd class="col-sm-9"><?= htmlspecialchars(number_format((float)($ride['prix'] ?? 0), 2, ',', ' ')) ?> crédits</dd>
                <dt class="col-sm-3">Conducteur</dt>
                <dd class="col-sm-9"><?= htmlspecialchars((string)($ride['driver_pseudo'] ?? ('#' . (int)($ride['driver_id'] ?? 0)))) ?></dd>
                <dt class="col-sm-3">Véhicule</dt>
                <dd class="col-sm-9">
                    <?php
                    $veh = trim(((string)($ride['vehicle_marque'] ?? '')) . ' ' . ((string)($ride['vehicle_modele'] ?? '')));
                    $veh = $veh !== '' ? $veh : 'N/C';
                    $couleur = (string)($ride['vehicle_couleur'] ?? '');
                    $immat = (string)($ride['vehicle_immatriculation'] ?? '');
                    ?>
                    <?= htmlspecialchars($veh) ?><?php if ($couleur): ?>, <span style="text-transform:capitalize;"><?= htmlspecialchars($couleur) ?></span><?php endif; ?>
                <?php if ($immat): ?><small class="text-muted ms-2">[<?= htmlspecialchars($immat) ?>]</small><?php endif; ?>
                </dd>
                <dt class="col-sm-3">Places dispo</dt>
                <dd class="col-sm-9"><?= (int)($ride['vehicle_places'] ?? 0) ?></dd>
            </dl>
            <a class="btn btn-secondary" href="/liste-covoiturages">Retour à la liste</a>
        </div>
    </div>
</div>