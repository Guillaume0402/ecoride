<?php

// Gestion de la date formatée
$dateFormatted = '';
if (!empty($vehicle['date_premiere_immatriculation'])) {
    $timestamp = strtotime($vehicle['date_premiere_immatriculation']);
    $dateFormatted = date('Y-m-d', $timestamp);
}
// Date du jour pour contrainte max du champ date
$today = date('Y-m-d');

// Préférences
$prefs = isset($vehicle['preferences']) ? explode(',', $vehicle['preferences']) : [];

?>

<div class="container py-5">
    <h2 class="mb-4 text-center"><?= !empty($vehicle['id']) ? 'Modifier mon véhicule' : 'Ajouter un véhicule' ?></h2>

    <form method="POST" action="<?= !empty($vehicle['id']) ? '/vehicle/update' : '/vehicle/create' ?>" class="p-4">
        <input type="hidden" name="csrf" value="<?= \App\Security\Csrf::token() ?>">

        <?php if (!empty($vehicle['id'])): ?>
            <input type="hidden" name="vehicle_id" value="<?= htmlspecialchars((string)$vehicle['id'], ENT_QUOTES, 'UTF-8') ?>">
        <?php endif; ?>

        <!-- Plaque -->
        <div class="mb-3">
            <label for="immatriculation" class="form-label">Plaque d'immatriculation</label>
            <input
                type="text"
                class="form-control"
                id="immatriculation"
                name="immatriculation"
                value="<?= htmlspecialchars((string)($vehicle['immatriculation'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                required>
        </div>

        <!-- Date -->
        <div class="mb-3">
            <label for="date_premiere_immatriculation" class="form-label">Date de première immatriculation</label>
            <input
                type="date"
                class="form-control"
                id="date_premiere_immatriculation"
                name="date_premiere_immatriculation"
                value="<?= htmlspecialchars((string)$dateFormatted, ENT_QUOTES, 'UTF-8') ?>"
                max="<?= htmlspecialchars((string)$today, ENT_QUOTES, 'UTF-8') ?>"
                required>
        </div>

        <!-- Marque, modèle, couleur -->
        <div class="mb-3">
            <label for="marque" class="form-label">Marque</label>
            <input
                type="text"
                class="form-control"
                id="marque"
                name="marque"
                value="<?= htmlspecialchars((string)($vehicle['marque'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                required>
        </div>

        <div class="mb-3">
            <label for="modele" class="form-label">Modèle</label>
            <input
                type="text"
                class="form-control"
                id="modele"
                name="modele"
                value="<?= htmlspecialchars((string)($vehicle['modele'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                required>
        </div>

        <div class="mb-3">
            <label for="couleur" class="form-label">Couleur</label>
            <input
                type="text"
                class="form-control"
                id="couleur"
                name="couleur"
                value="<?= htmlspecialchars((string)($vehicle['couleur'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                required>
        </div>

        <!-- Motorisation -->
        <div class="mb-3">
            <label for="fuel_type_id" class="form-label">Type de motorisation</label>
            <select class="form-select" id="fuel_type_id" name="fuel_type_id" required>
                <option value="1" <?= ((int)($vehicle['fuel_type_id'] ?? 0) === 1) ? 'selected' : '' ?>>Essence</option>
                <option value="2" <?= ((int)($vehicle['fuel_type_id'] ?? 0) === 2) ? 'selected' : '' ?>>Diesel</option>
                <option value="3" <?= ((int)($vehicle['fuel_type_id'] ?? 0) === 3) ? 'selected' : '' ?>>Électrique</option>
                <option value="4" <?= ((int)($vehicle['fuel_type_id'] ?? 0) === 4) ? 'selected' : '' ?>>Hybride</option>
            </select>
        </div>

        <!-- Places dispo -->
        <div class="mb-3">
            <label for="places_dispo" class="form-label">Nombre de places disponibles</label>
            <select class="form-select" id="places_dispo" name="places_dispo" required>
                <?php for ($i = 1; $i <= 8; $i++): ?>
                    <option value="<?= $i ?>" <?= ((int)($vehicle['places_dispo'] ?? 0) === $i) ? 'selected' : '' ?>>
                        <?= $i ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>

        <!-- Préférences -->
        <fieldset class="mb-3">
            <legend class="form-label mb-2">Préférences</legend>

            <?php
            $prefOptions = [
                'fumeur'      => 'Fumeur',
                'non-fumeur'  => 'Non-fumeur',
                'animaux'     => 'Animaux acceptés',
                'pas-animaux' => "Pas d'animal",
            ];
            ?>

            <?php foreach ($prefOptions as $value => $label): ?>
                <?php $id = 'pref_' . $value; ?>
                <div class="form-check">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        id="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>"
                        name="preferences[]"
                        value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>"
                        <?= in_array($value, $prefs ?? [], true) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </fieldset>

        <!-- Ajout personnalisé -->
        <div class="mb-3">
            <label for="custom_preferences" class="form-label">Ajouter vos préférences personnalisées</label>
            <textarea
                class="form-control"
                id="custom_preferences"
                name="custom_preferences"
                rows="3"
                maxlength="250"><?= htmlspecialchars((string)($vehicle['custom_preferences'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <!-- Boutons -->
        <div class="text-end">
            <a href="/my-profil" class="btn btn-custom-outline">Annuler</a>
            <button type="submit" class="btn btn-inscription">Enregistrer</button>
        </div>
    </form>

</div>

<!-- Conflits de préférences: Fumeur vs Non-fumeur, Animaux vs Pas d'animal -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sel = (val) => document.querySelector('input[name="preferences[]"][value="' + val + '"]');
        const fumeur = sel('fumeur');
        const nonFumeur = sel('non-fumeur');
        const animaux = sel('animaux');
        const pasAnimaux = sel('pas-animaux');

        function bindExclusive(a, b) {
            if (!a || !b) return;
            const sync = () => {
                if (a.checked && b.checked) {
                    // Par défaut, on garde celui qui vient d'être coché
                    // et on décoche l'autre
                    // L'événement 'change' se déclenchant sur l'élément modifié,
                    // on peut simplement décocher l'autre
                }
                if (a.checked) b.checked = false;
                if (b.checked) a.checked = false;
            };
            a.addEventListener('change', sync);
            b.addEventListener('change', sync);
            // Sync initial (cas où le serveur a coché les deux par erreur)
            sync();
        }

        bindExclusive(fumeur, nonFumeur);
        bindExclusive(animaux, pasAnimaux);
    });
</script>