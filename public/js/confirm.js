(function () {
    /* Petit module de confirmation: intercepte certains formulaires et
    affiche une alerte personnalisée avec boutons OK / Annuler.
    Utilisation: ajouter la classe "js-confirm" sur le <form>
    et, optionnellement, des attributs data:
    data-confirm-text: message principal
    data-confirm-text2: message de 2e étape (si nécessaire)
    data-confirm-steps: nombre d'étapes (1 ou 2)
    data-confirm-variant: style visuel (info, success, warning, danger) */
    
    const STACK_ID = "alerts"; // Id du conteneur où empiler les alertes (fallback sur <body>)

    // Construit le DOM de l'alerte avec message + boutons d'action
    function createAlert(message, variant = "warning") {
        const div = document.createElement("div");
        div.className = `custom-alert alert-${variant}`;
        div.innerHTML = `
      <button type="button" class="btn-close" aria-label="Close"></button>
      <div class="content">${message}</div>
            <div class="actions">
                <button type="button" class="btn-cancel" data-role="cancel">Annuler</button>
                <button type="button" class="btn-ok" data-role="ok">OK</button>
            </div>
    `;
        return div;
    }

    // Affiche une alerte et retourne une Promise résolue à true/false
    // selon le bouton cliqué par l'utilisateur.
    function showConfirm(message, variant = "warning") {
        return new Promise((resolve) => {
            // On place l'alerte soit dans #alerts, soit à défaut dans <body>
            const stack = document.getElementById(STACK_ID) || document.body;
            const alert = createAlert(message, variant);
            stack.appendChild(alert);

            // Ferme l'alerte et renvoie la réponse
            function cleanup(ans) {
                alert.remove();
                resolve(ans);
            }

            // Écoute des clics sur les boutons de l'alerte
            alert.addEventListener("click", (e) => {
                const btn = e.target.closest("button");
                if (!btn) return;
                if (
                    btn.classList.contains("btn-close") ||
                    btn.dataset.role === "cancel"
                ) {
                    // Fermeture/Annulation => réponse false
                    cleanup(false);
                } else if (btn.dataset.role === "ok") {
                    // Validation => réponse true
                    cleanup(true);
                }
            });
        });
    }

    // Intercepte la soumission des formulaires marqués .js-confirm
    // et déclenche 1 ou 2 étapes de confirmation selon les attributs data.
    function onSubmitIntercept(e) {
        const form = e.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (!form.classList.contains("js-confirm")) return;

        // Récupération des messages et options via data-attributes
        const msg =
            form.getAttribute("data-confirm-text") ||
            "Confirmer cette action ?";
        const msg2 = form.getAttribute("data-confirm-text2");
        const steps =
            parseInt(form.getAttribute("data-confirm-steps") || "1", 10) || 1;
        const variant = form.getAttribute("data-confirm-variant") || "warning";

        // empêcher submit par défaut et demander confirmation
        e.preventDefault();
        e.stopPropagation();

        showConfirm(msg, variant).then((ok) => {
            if (!ok) return;
            // Si 2 étapes sont demandées et qu'un 2e message est présent,
            // on enchaîne une seconde confirmation.
            if (steps >= 2 && msg2) {
                showConfirm(msg2, variant).then((ok2) => {
                    if (ok2) form.submit();
                });
            } else {
                // Sinon, on soumet le formulaire directement
                form.submit();
            }
        });
    }

    // Capture en phase de capture (true) pour intercepter avant autres handlers
    document.addEventListener("submit", onSubmitIntercept, true);
})();
