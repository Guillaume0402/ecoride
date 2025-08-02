document.addEventListener("DOMContentLoaded", () => {
    // Gestion des alertes auto
    document.querySelectorAll(".custom-alert").forEach((alert) => {
        setTimeout(() => alert.classList.add("fade-out"), 3000);
    });

    // Confirmation suppression
    document.querySelectorAll(".delete-btn").forEach((button) => {
        button.addEventListener("click", (e) => {
            e.preventDefault();
            const url = button.getAttribute("href");
            const type = button.dataset.type || "élément";
            const action = button.dataset.action || "supprimer";

            document.querySelector(
                "#deleteConfirmModal .modal-body"
            ).textContent = `Voulez-vous vraiment ${action} cet ${type} ?`;

            const confirmBtn = document.getElementById("confirmDeleteBtn");
            confirmBtn.textContent =
                action.charAt(0).toUpperCase() + action.slice(1); // ✅ Change le texte
            confirmBtn.setAttribute("href", url);

            const modal = new bootstrap.Modal(
                document.getElementById("deleteConfirmModal")
            );
            modal.show();
        });
    });

    // Confirmation activer/désactiver
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
            confirmBtn.textContent =
                action.charAt(0).toUpperCase() + action.slice(1); // ✅ Change le texte
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
