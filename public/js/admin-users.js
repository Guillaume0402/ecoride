/*
Module: Admin Utilisateurs
Rôle: confirmations (suppression/activation) via une modale Bootstrap.
Utilisation: Script chargé uniquement sur la page /admin/users.
*/
document.addEventListener("DOMContentLoaded", () => {
    const modalEl = document.getElementById("deleteConfirmModal");
    const confirmBtn = document.getElementById("confirmDeleteBtn");
    const modalBody = document.querySelector("#deleteConfirmModal .modal-body");

    if (!modalEl || !confirmBtn || !modalBody) return;

    let pendingAction = null; // fonction à exécuter quand on confirme

    // Au clic sur "Supprimer" dans la modale, exécuter l’action enregistrée
    confirmBtn.addEventListener("click", (e) => {
        e.preventDefault();
        if (typeof pendingAction === "function") pendingAction();
        pendingAction = null;
    });

    // Delete : ouvre la modale et prépare un submit du form
    document.querySelectorAll(".delete-btn").forEach((button) => {
        button.addEventListener("click", (e) => {
            e.preventDefault();

            const formId = button.dataset.formId;
            if (!formId) return;

            const type = button.dataset.type || "élément";
            const action = button.dataset.action || "supprimer";

            modalBody.textContent = `Voulez-vous vraiment ${action} cet ${type} ?`;
            confirmBtn.textContent = action.charAt(0).toUpperCase() + action.slice(1);
            confirmBtn.removeAttribute("href");

            pendingAction = () => {
                const form = document.getElementById(formId);
                if (form) form.submit();
            };

            new bootstrap.Modal(modalEl).show();
        });
    });

    // Toggle : ouvre la modale et prépare un submit du form
    document.querySelectorAll(".toggle-btn").forEach((button) => {
        button.addEventListener("click", (e) => {
            e.preventDefault();

            const form = button.closest("form");
            if (!form) return;

            const type = button.dataset.type || "élément";
            const action = button.dataset.action || "changer le statut de";

            modalBody.textContent = `Voulez-vous vraiment ${action} cet ${type} ?`;
            confirmBtn.textContent = action.charAt(0).toUpperCase() + action.slice(1);
            confirmBtn.removeAttribute("href");

            pendingAction = () => form.submit();

            new bootstrap.Modal(modalEl).show();
        });
    });
});
