<!-- On définit l'utilisateur si non déjà défini -->
<?php $user = $user ?? null;
?>

<!-- Alertes de succès et d'erreur -->
<?php if (!empty($_SESSION['success'])): ?>
    <div class="alert alert-success text-center w-75 mx-auto">
        <?= $_SESSION['success'];
        unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>
<?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger text-center w-75 mx-auto">
        <?= $_SESSION['error'];
        unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<?php if (!$user): ?>
    <div class="alert alert-danger text-center w-75 mx-auto">Aucun utilisateur connecté.</div>
<?php endif; ?>

<div class="container mt-5 mb-5">

    <form method="POST" action="/creation-profil" enctype="multipart/form-data" class="p-4">
        <div class="container mt-2 d-flex align-items-center justify-content-between form-section">
            <h4>Votre Profil</h4>
            <!-- Boutons -->
            <div class="mb-3">
                <a href="/" class="btn btn-custom-outline">Annuler</a>
                <button type="submit" class="btn btn-inscription me-2">Sauvegarder</button>
            </div>
        </div>
        <!-- Photo -->
        <div class="mb-4">
            <label for="photo" class="form-label">Photo</label>
            <div class="d-flex align-items-center gap-4 flex-wrap">
                <!-- Image preview -->
                <img id="avatarPreview"
                    src="<?= !empty($user['photo']) ? htmlspecialchars($user['photo']) : '/assets/images/logo.svg' ?>"
                    alt="Avatar"
                    class="rounded-circle shadow-sm border"
                    style="width: 100px; height: 100px; object-fit: cover;">

                <!-- Zone bouton de téléchargement -->
                <label for="photo" class="upload-label d-inline-flex align-items-center gap-3 px-3 py-2 rounded-pill">
                    <div class="upload-icon rounded-circle d-flex align-items-center justify-content-center">
                        <i class="bi bi-file-earmark-arrow-up-fill"></i>
                    </div>
                    <span id="photo-name" class="text-white">Télécharger votre photo</span>
                </label>
            </div>

            <!-- Input masqué -->
            <input type="file" class="d-none form-control" id="photo" name="photo" accept="image/*">
        </div>

        <!-- PSEUDO -->
        <div class="mb-3 form-section">
            <label for="pseudo" class="form-label">Pseudo</label>
            <input type="text" class="form-control" id="pseudo" name="pseudo" required
                value="<?= isset($user['pseudo']) ? htmlspecialchars($user['pseudo']) : '' ?>">

        </div>

        <!-- Mot de passe actuel -->
        <div class="mb-3 position-relative">
            <label for="current_password" class="form-label">Mot de passe actuel</label>
            <input type="password" class="form-control pe-5" id="current_password" name="current_password" placeholder="Mot de passe actuel">
            <button type="button" class="btn btn-link input-password-toggle" onclick="togglePassword('current_password', this)">
                <i class="bi bi-eye-slash"></i>
            </button>
        </div>

        <!-- Nouveau mot de passe -->
        <div class="mb-3 position-relative">
            <label for="new_password" class="form-label">Nouveau mot de passe</label>
            <input type="password" class="form-control pe-5" id="new_password" name="new_password" placeholder="Nouveau mot de passe">
            <button type="button" class="btn btn-link input-password-toggle" onclick="togglePassword('new_password', this)">
                <i class="bi bi-eye-slash"></i>
            </button>
        </div>


        <!-- RÔLE -->
        <div class="mb-3 form-section">
            <label for="role" class="form-label">Rôles</label>
            <select class="form-select form-control" id="travel_role" name="travel_role" required onchange="toggleChauffeurFields()">
                <option value="">Sélectionner</option>
                <option value="passager" <?= ($user['travel_role'] ?? '') === 'passager' ? 'selected' : '' ?>>Passager</option>
                <option value="chauffeur" <?= ($user['travel_role'] ?? '') === 'chauffeur' ? 'selected' : '' ?>>Chauffeur</option>
                <option value="les-deux" <?= ($user['travel_role'] ?? '') === 'les-deux' ? 'selected' : '' ?>>Les deux</option>
            </select>

        </div>

        <!-- SECTION CHAUFFEUR -->

        <div id="chauffeur-fields" style="display: none;">
            <hr>
            <!-- Plaque -->
            <div class="mb-3 form-section">
                <label for="immatriculation" class="form-label">Plaque d'immatriculation</label>
                <input type="text" class="form-control" id="immatriculation" name="immatriculation"
                    value="">
            </div>

            <!-- Date -->
            <div class="mb-3 form-section">
                <label for="date_premiere_immatriculation" class="form-label">Date de première immatriculation</label>
                <input type="date" class="form-control" id="date_premiere_immatriculation" name="date_premiere_immatriculation"
                    value="" required>
            </div>


            <!-- Modèle -->
            <div class="mb-3 form-section">
                <label for="marque" class="form-label">Marque</label>
                <input type="text" class="form-control" id="marque" name="marque"
                    value="">
                <label for="modele" class="form-label">Modèle</label>
                <input type="text" class="form-control" id="modele" name="modele"
                    value="">
                <label for="couleur" class="form-label">Couleur</label>
                <input type="text" class="form-control" id="couleur" name="couleur"
                    value="">

                <!-- Motorisation -->
                <div class="mb-3 form-section">
                    <label for="fuel_type_id" class="form-label">Type de motorisation</label>
                    <select class="form-select form-control" id="fuel_type_id" name="fuel_type_id">
                        <option value="1">Essence</option>
                        <option value="2">Diesel</option>
                        <option value="3">Électrique</option>
                        <option value="4">Hybride</option>
                    </select>
                </div>

                <!-- Nombre de places -->
                <div class="mb-3 form-section">
                    <label for="places_dispo" class="form-label">Nombre de places disponibles</label>
                    <select class="form-select form-control" id="places_dispo" name="places_dispo">
                        <option value="">Sélectionner</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4+">4+</option>

                    </select>
                </div>

                <!-- PRÉFÉRENCES -->

                <div class="mb-3">
                    <label class="form-label">Préférences</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="fumeur" name="preferences[]">
                        <label class="form-check-label">Fumeur</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="non-fumeur" name="preferences[]">
                        <label class="form-check-label">Non-fumeur</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="animaux" name="preferences[]">
                        <label class="form-check-label">Animaux acceptés</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="pas-animaux" name="preferences[]">
                        <label class="form-check-label">Pas d'animal</label>
                    </div>

                    <!-- Ajout personnalisé -->
                    <label for="custom_preferences" class="form-label mt-2">Ajouter vos préférences</label>
                    <textarea class="form-control" id="custom_preferences" name="custom_preferences"
                        rows="3" maxlength="250"></textarea>

                </div>
            </div>

            <!-- Boutons -->
            <div class="text-end mt-4">
                <a href="/" class="btn btn-custom-outline">Annuler</a>
                <button type="submit" class="btn btn-inscription me-2">Sauvegarder</button>
            </div>
    </form>
</div>



<script>
    document.getElementById("photo").addEventListener("change", function() {
        const file = this.files[0];
        const nameDisplay = document.getElementById("photo-name");
        const preview = document.getElementById("avatarPreview");

        if (file) {
            nameDisplay.textContent = file.name;

            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        } else {
            nameDisplay.textContent = "Télécharger votre photo";
            preview.src = "/assets/images/logo.svg"; // Valeur par défaut
        }
    });


    function toggleChauffeurFields() {
        const role = document.getElementById('travel_role').value;
        const chauffeurFields = document.getElementById('chauffeur-fields');
        if (role === 'chauffeur' || role === 'les-deux') {
            chauffeurFields.style.display = 'block';
        } else {
            chauffeurFields.style.display = 'none';
        }
    }

    function togglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector("i");

        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove("bi-eye-slash");
            icon.classList.add("bi-eye");
        } else {
            input.type = "password";
            icon.classList.remove("bi-eye");
            icon.classList.add("bi-eye-slash");
        }
    }
    document.addEventListener("DOMContentLoaded", () => {
        toggleChauffeurFields(); // Pour afficher les champs si besoin
    });
</script>