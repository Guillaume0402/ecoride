<div class="container mt-5 mb-5">
    <div class="container mt-2 d-flex align-items-center justify-content-between form-section">
        <h4>Votre Profil</h4>
        <!-- Boutons -->
        <div class="mb-3">
            <a href="/" class="btn btn-custom-outline">Annuler</a>
            <button type="submit" class="btn btn-inscription me-2">Sauvegarder</button>
        </div>
    </div>
    <form method="POST" action="/profile/update" enctype="multipart/form-data" class="p-4">
        <!-- Photo -->
        <div class="mb-4">
            <label for="photo" class="form-label">Photo</label>
            <div class="d-flex align-items-center gap-4 flex-wrap">
                <!-- Image preview -->
                <img id="avatarPreview" src="/assets/images/logo.svg" alt="Avatar"
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
            <input type="text" class="form-control" id="pseudo" name="pseudo" required>
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
            <select class="form-select form-control" id="role" name="role" required onchange="toggleChauffeurFields()">
                <option value="">Sélectionner</option>
                <option value="passager">Passager</option>
                <option value="chauffeur">Chauffeur</option>
                <option value="les-deux">Chauffeur & Passager</option>
            </select>
        </div>

        <!-- SECTION CHAUFFEUR -->
        <div id="chauffeur-fields" style="display: none;">
            <hr>
            <!-- Plaque -->
            <div class="mb-3 form-section">
                <label for="plate" class="form-label">Plaque d'immatriculation</label>
                <input type="text" class="form-control" id="plate" name="plate">
            </div>

            <!-- Date -->
            <div class="mb-3 form-section">
                <label for="registration_date" class="form-label">Date de première immatriculation</label>
                <input type="date" class="form-control" id="registration_date" name="registration_date">
            </div>

            <!-- Modèle -->
            <div class="mb-3 form-section">
                <label for="model" class="form-label">Modèle, Couleur, Marque</label>
                <input type="text" class="form-control" id="model" name="model">
            </div>

            <!-- Motorisation -->
            <div class="mb-3 form-section">
                <label for="motor_type" class="form-label form-control">Type de motorisation</label>
                <select class="form-select form-control" id="motor_type" name="motor_type">
                    <option value="">Sélectionner</option>
                    <option value="essence">Essence</option>
                    <option value="diesel">Diesel</option>
                    <option value="electrique">Électrique</option>
                    <option value="hybride">Hybride</option>
                </select>
            </div>

            <!-- Nombre de places -->
            <div class="mb-3 form-section>
                <label for=" seats" class="form-label form-control">Nombre de places disponibles</label>
                <select class="form-select form-control" id="seats" name="seats">
                    <option value="">Sélectionner</option>
                    <option>1</option>
                    <option>2</option>
                    <option>3</option>
                    <option>4+</option>
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
                <textarea class="form-control" id="custom_preferences" name="custom_preferences" rows="3" maxlength="250" placeholder="Ajoutez des préférences (250 caractères max)"></textarea>
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
        const role = document.getElementById('role').value;
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
</script>