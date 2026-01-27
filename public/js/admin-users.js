/*
Module: Admin Utilisateurs
Rôle: Gérer les alertes auto, confirmations (suppression/activation) via une modale Bootstrap.
Utilisation: Inclure ce script dans le layout admin.
*/
document.addEventListener("DOMContentLoaded", () => {
    // Masque automatiquement les alertes après un délai
    document.querySelectorAll(".custom-alert").forEach((alert) => {
        setTimeout(() => alert.classList.add("fade-out"), 3000);
    });

// Demande de confirmation avant suppression (soumet le form POST)
document.querySelectorAll(".delete-btn").forEach((button) => {
    button.addEventListener("click", (e) => {
        e.preventDefault();

        const formId = button.dataset.formId;
        if (!formId) return;

        const type = button.dataset.type || "élément";
        const action = button.dataset.action || "supprimer";

        document.querySelector(
            "#deleteConfirmModal .modal-body"
        ).textContent = `Voulez-vous vraiment ${action} cet ${type} ?`;

        const confirmBtn = document.getElementById("confirmDeleteBtn");
        confirmBtn.textContent =
            action.charAt(0).toUpperCase() + action.slice(1);

        // On n'utilise plus href (sinon /admin/null)
        confirmBtn.removeAttribute("href");

        // Submit le form lié (une seule fois)
        confirmBtn.addEventListener("click", (ev) => {
            ev.preventDefault();
            const form = document.getElementById(formId);
            if (form) form.submit();
        }, { once: true });

        const modal = new bootstrap.Modal(
            document.getElementById("deleteConfirmModal")
        );
        modal.show();
    });
});




    // Demande de confirmation pour activer/désactiver (soumet le formulaire)
    document.querySelectorAll(".toggle-btn").forEach((button) => {
        button.addEventListener("click", (e) => {
            e.preventDefault();
            const form = button.closest("form");
            const type = button.dataset.type || "élément";
            const action = button.dataset.action || "changer le statut de";

            document.querySelector(
                "#deleteConfirmModal .modal-body"
            ).textContent = `Voulez-vous vraiment ${action} cet ${type} ?`;

            const confirmBtn = document.getElementById("confirmDeleteBtn");
            // Met à jour le libellé et associe la soumission du formulaire
            confirmBtn.textContent =
                action.charAt(0).toUpperCase() + action.slice(1);
            confirmBtn.removeAttribute("href");
            confirmBtn.addEventListener("click", () => form.submit(), {
                once: true,
            });

            const modal = new bootstrap.Modal(
                document.getElementById("deleteConfirmModal")
            );
            modal.show();
        });
    });
});
