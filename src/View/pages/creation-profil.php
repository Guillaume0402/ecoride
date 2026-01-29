<!-- On définit l'utilisateur si non déjà défini -->
<?php $user = $user ?? null; ?>

<div class="container mt-5 mb-5">

    <form method="POST" action="/creation-profil" enctype="multipart/form-data" class="p-4">
        <input type="hidden" name="csrf" value="<?= \App\Security\Csrf::token() ?>">

        <!-- Titre uniquement (plus de boutons en haut) -->
        <div class="mb-4 form-section">
            <h4 class="mb-0">Votre Profil</h4>
        </div>

        <!-- Photo -->
        <div class="mb-4">
            <label for="photo" class="form-label">Photo</label>

            <div class="d-flex align-items-center gap-4 flex-wrap">
                <img
                    id="avatarPreview"
                    src="<?= !empty($user['photo'])
                        ? htmlspecialchars($user['photo'], ENT_QUOTES, 'UTF-8')
                        : htmlspecialchars(defined('DEFAULT_AVATAR_URL') ? DEFAULT_AVATAR_URL : '/assets/images/logo.svg', ENT_QUOTES, 'UTF-8') ?>"
                    alt="Avatar"
                    class="rounded-circle shadow-sm border"
                    style="width: 100px; height: 100px; object-fit: cover;">

                <!-- Label-bouton (clique => ouvre input file via for="photo") -->
                <label for="photo" class="upload-label d-inline-flex align-items-center gap-3 px-3 py-2 rounded-pill">
                    <div class="upload-icon rounded-circle d-flex align-items-center justify-content-center">
                        <i class="bi bi-file-earmark-arrow-up-fill"></i>
                    </div>
                    <span id="photo-name" class="text-white">Télécharger votre photo</span>
                </label>
            </div>

            <input
                type="file"
                class="d-none form-control"
                id="photo"
                name="photo"
                accept="image/*">
        </div>

        <!-- PSEUDO -->
        <div class="mb-3 form-section">
            <label for="pseudo" class="form-label">Pseudo</label>
            <input
                type="text"
                class="form-control"
                id="pseudo"
                name="pseudo"
                required
                autocomplete="nickname"
                value="<?= htmlspecialchars($user['pseudo'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <!-- Mot de passe actuel -->
        <div class="mb-3 position-relative">
            <label for="current_password" class="form-label">Mot de passe actuel</label>
            <input
                type="password"
                class="form-control pe-5"
                id="current_password"
                name="current_password"
                placeholder="Mot de passe actuel"
                autocomplete="current-password">

            <button
                type="button"
                class="btn btn-link input-password-toggle"
                onclick="togglePassword('current_password', this)"
                aria-label="Afficher / masquer le mot de passe actuel">
                <i class="bi bi-eye-slash"></i>
            </button>
        </div>

        <!-- Nouveau mot de passe -->
        <div class="mb-3 position-relative">
            <label for="new_password" class="form-label">Nouveau mot de passe</label>
            <input
                type="password"
                class="form-control pe-5"
                id="new_password"
                name="new_password"
                placeholder="Nouveau mot de passe"
                autocomplete="new-password">

            <button
                type="button"
                class="btn btn-link input-password-toggle"
                onclick="togglePassword('new_password', this)"
                aria-label="Afficher / masquer le nouveau mot de passe">
                <i class="bi bi-eye-slash"></i>
            </button>
        </div>

        <!-- RÔLE -->
        <div class="mb-3 form-section">
            <label for="travel_role" class="form-label">Rôles</label>
            <select
                class="form-select form-control"
                id="travel_role"
                name="travel_role"
                onchange="toggleChauffeurFields()">
                <option value="">Sélectionner</option>
                <option value="passager"  <?= ($user['travel_role'] ?? '') === 'passager' ? 'selected' : '' ?>>Passager</option>
                <option value="chauffeur" <?= ($user['travel_role'] ?? '') === 'chauffeur' ? 'selected' : '' ?>>Chauffeur</option>
                <option value="les-deux"  <?= ($user['travel_role'] ?? '') === 'les-deux' ? 'selected' : '' ?>>Les deux</option>
            </select>
        </div>

        <!-- SECTION CHAUFFEUR (tes champs chauffeur restent ici, inchangés) -->
        <!-- ... -->

        <!-- Boutons uniquement en bas -->
        <div class="text-end mt-4">
            <a href="/my-profil" class="btn btn-custom-outline">Annuler</a>
            <button type="submit" class="btn btn-inscription ms-2">Sauvegarder</button>
        </div>
    </form>
</div>
