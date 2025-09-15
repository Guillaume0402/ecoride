// Confirmation stylée pour formulaires sensibles
// Usage: ajout de la classe .js-confirm et de l'attribut data-confirm-text sur le <form>
(function () {
  const STACK_ID = "alerts";

  function createAlert(message, variant = "warning") {
    const div = document.createElement("div");
    div.className = `custom-alert alert-${variant}`;
    div.innerHTML = `
      <button type="button" class="btn-close" aria-label="Close"></button>
      <div class="content">${message}</div>
      <div class="mt-2 d-flex gap-2">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-role="cancel">Annuler</button>
        <button type="button" class="btn btn-sm btn-outline-danger" data-role="ok">OK</button>
      </div>
    `;
    return div;
  }

  function showConfirm(message, variant = "warning") {
    return new Promise((resolve) => {
      const stack = document.getElementById(STACK_ID) || document.body;
      const alert = createAlert(message, variant);
      stack.appendChild(alert);

      function cleanup(ans) {
        alert.remove();
        resolve(ans);
      }

      alert.addEventListener("click", (e) => {
        const btn = e.target.closest("button");
        if (!btn) return;
        if (btn.classList.contains("btn-close") || btn.dataset.role === "cancel") {
          cleanup(false);
        } else if (btn.dataset.role === "ok") {
          cleanup(true);
        }
      });
    });
  }

  function onSubmitIntercept(e) {
    const form = e.target;
    if (!(form instanceof HTMLFormElement)) return;
    if (!form.classList.contains("js-confirm")) return;

    const msg = form.getAttribute("data-confirm-text") || "Confirmer cette action ?";
    const variant = form.getAttribute("data-confirm-variant") || "warning";

    // empêcher submit par défaut et demander confirmation
    e.preventDefault();
    e.stopPropagation();

    showConfirm(msg, variant).then((ok) => {
      if (ok) {
        // soumettre réellement
        form.submit();
      }
    });
  }

  document.addEventListener("submit", onSubmitIntercept, true);
})();
