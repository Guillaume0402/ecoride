<?php

// Gestion de la date formatée
$dateFormatted = '';
if (!empty($vehicle['date_premiere_immatriculation'])) {
    $timestamp = strtotime($vehicle['date_premiere_immatriculation']);
    $dateFormatted = date('Y-m-d', $timestamp);
}

// Préférences
$prefs = isset($vehicle['preferences']) ? explode(',', $vehicle['preferences']) : [];

?>

<div class="container py-5">
    <h2 class="mb-4 text-center"><?= !empty($vehicle['id']) ? 'Modifier mon véhicule' : 'Ajouter un véhicule' ?></h2>

    <form method="POST" action="<?= !empty($vehicle['id']) ? '/vehicle/update' : '/vehicle/create' ?>" class="p-4">
        <input type="hidden" name="csrf" value="<?= \App\Security\Csrf::token() ?>">
        <?php if (!empty($vehicle['id'])): ?>
            <input type="hidden" name="vehicle_id" value="<?= htmlspecialchars($vehicle['id']) ?>">
        <?php endif; ?>
        <!-- Plaque -->
        <div class="mb-3">
            <label for="immatriculation" class="form-label">Plaque d'immatriculation</label>
            <input type="text" class="form-control" id="immatriculation" name="immatriculation"
                value="<?= htmlspecialchars($vehicle['immatriculation'] ?? '') ?>" required>
        </div>

        <!-- Date -->
        <div class="mb-3">
            <label for="date_premiere_immatriculation" class="form-label">Date de première immatriculation</label>
            <input type="date" class="form-control" id="date_premiere_immatriculation" name="date_premiere_immatriculation"
                value="<?= $dateFormatted ?>" required>
        </div>

        <!-- Marque, modèle, couleur -->
        <div class="mb-3">
            <label for="marque" class="form-label">Marque</label>
            <input type="text" class="form-control" id="marque" name="marque"
                value="<?= htmlspecialchars($vehicle['marque'] ?? '') ?>" required>
        </div>

        <div class="mb-3">
            <label for="modele" class="form-label">Modèle</label>
            <input type="text" class="form-control" id="modele" name="modele"
                value="<?= htmlspecialchars($vehicle['modele'] ?? '') ?>" required>
        </div>

        <div class="mb-3">
            <label for="couleur" class="form-label">Couleur</label>
            <input type="text" class="form-control" id="couleur" name="couleur"
                value="<?= htmlspecialchars($vehicle['couleur'] ?? '') ?>" required>
        </div>

        <!-- Motorisation -->
        <div class="mb-3">
            <label for="fuel_type_id" class="form-label">Type de motorisation</label>
            <select class="form-select" id="fuel_type_id" name="fuel_type_id" required>
                <option value="1" <?= ($vehicle['fuel_type_id'] ?? '') == 1 ? 'selected' : '' ?>>Essence</option>
                <option value="2" <?= ($vehicle['fuel_type_id'] ?? '') == 2 ? 'selected' : '' ?>>Diesel</option>
                <option value="3" <?= ($vehicle['fuel_type_id'] ?? '') == 3 ? 'selected' : '' ?>>Électrique</option>
                <option value="4" <?= ($vehicle['fuel_type_id'] ?? '') == 4 ? 'selected' : '' ?>>Hybride</option>
            </select>
        </div>

        <!-- Places dispo -->
        <div class="mb-3">
            <label for="places_dispo" class="form-label">Nombre de places disponibles</label>
            <select class="form-select" id="places_dispo" name="places_dispo" required>
                <option value="1" <?= ($vehicle['places_dispo'] ?? '') == '1' ? 'selected' : '' ?>>1</option>
                <option value="2" <?= ($vehicle['places_dispo'] ?? '') == '2' ? 'selected' : '' ?>>2</option>
                <option value="3" <?= ($vehicle['places_dispo'] ?? '') == '3' ? 'selected' : '' ?>>3</option>
                <option value="4" <?= ($vehicle['places_dispo'] ?? '') == '4' ? 'selected' : '' ?>>4</option>
                <option value="5" <?= ($vehicle['places_dispo'] ?? '') == '5' ? 'selected' : '' ?>>5</option>
                <option value="6" <?= ($vehicle['places_dispo'] ?? '') == '6' ? 'selected' : '' ?>>6</option>
                <option value="7" <?= ($vehicle['places_dispo'] ?? '') == '7' ? 'selected' : '' ?>>7</option>
                <option value="8" <?= ($vehicle['places_dispo'] ?? '') == '8' ? 'selected' : '' ?>>8</option>


            </select>
        </div>

        <!-- Préférences -->
        <div class="mb-3">
            <label class="form-label">Préférences</label>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="fumeur" name="preferences[]"
                    <?= in_array('fumeur', $prefs) ? 'checked' : '' ?>>
                <label class="form-check-label">Fumeur</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="non-fumeur" name="preferences[]"
                    <?= in_array('non-fumeur', $prefs) ? 'checked' : '' ?>>
                <label class="form-check-label">Non-fumeur</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="animaux" name="preferences[]"
                    <?= in_array('animaux', $prefs) ? 'checked' : '' ?>>
                <label class="form-check-label">Animaux acceptés</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="pas-animaux" name="preferences[]"
                    <?= in_array('pas-animaux', $prefs) ? 'checked' : '' ?>>
                <label class="form-check-label">Pas d'animal</label>
            </div>
        </div>

        <!-- Ajout personnalisé -->
        <div class="mb-3">
            <label for="custom_preferences" class="form-label">Ajouter vos préférences personnalisées</label>
            <textarea class="form-control" id="custom_preferences" name="custom_preferences"
                rows="3" maxlength="250"><?= htmlspecialchars($vehicle['custom_preferences'] ?? '') ?></textarea>

        </div>

        <!-- Boutons -->
        <div class="text-end">
            <a href="/my-profil" class="btn btn-custom-outline">Annuler</a>
            <button type="submit" class="btn btn-inscription">Enregistrer</button>
        </div>
    </form>
</div>