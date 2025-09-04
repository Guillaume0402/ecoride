// Prévisualisation de la photo de profil et affichage du nom de fichier
const photoInput = document.getElementById("photo");

if (photoInput) {
    photoInput.addEventListener("change", function () {
        const file = this.files[0];
        const nameDisplay = document.getElementById("photo-name");
        const preview = document.getElementById("avatarPreview");

        if (file) {
            // Affiche le nom du fichier sélectionné
            nameDisplay.textContent = file.name;

            // Affiche un aperçu de l'image sélectionnée
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
