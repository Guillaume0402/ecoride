/*
Module: Création Profil
Rôle: Prévisualiser la photo choisie et basculer l’affichage du mot de passe.
Prérequis: #photo, #photo-name, #avatarPreview; bouton appelant togglePassword(id, this).
Utilisation: Inclure dans les pages de création/édition de profil.
*/

const photoInput = document.getElementById("photo");

if (photoInput) {
    // Quand l'utilisateur choisit un fichier dans l'input
    photoInput.addEventListener("change", function () {
        // Premier fichier sélectionné (si multiple=false)
        const file = this.files[0];
        // Éléments d'affichage (doivent exister dans le DOM)
        const nameDisplay = document.getElementById("photo-name");
        const preview = document.getElementById("avatarPreview");

        if (file) {
            // Affiche le nom du fichier sélectionné
            nameDisplay.textContent = file.name;

            // Affiche un aperçu de l'image sélectionnée
            // FileReader lit le fichier côté client et renvoie une Data URL
            const reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        } else {
            // Réinitialise l'affichage si aucun fichier n'est sélectionné
            nameDisplay.textContent = "Télécharger votre photo";
            preview.src = "/assets/images/logo.svg"; // Valeur par défaut
        }
    });
}

// Affiche/masque le mot de passe dans les champs concernés
// inputId: l'id du champ <input type="password">
// btn: le bouton cliqué (utilisé pour changer l'icône à l'intérieur)
function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector("i");

    if (input.type === "password") {
        // Passe en clair et met à jour l'icône (Bootstrap Icons)
        input.type = "text";
        icon.classList.remove("bi-eye-slash");
        icon.classList.add("bi-eye");
    } else {
        // Re-passe en masqué et remet l'icône initiale
        input.type = "password";
        icon.classList.remove("bi-eye");
        icon.classList.add("bi-eye-slash");
    }
}
