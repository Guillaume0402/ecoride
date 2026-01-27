/*
Module: Covoit Guard
Rôle: Empêcher l’ouverture de la modale de création si aucun véhicule n’est déclaré.
Utilisation: Bouton qui ouvre la modale via data-bs-target="#createCovoitModal".
*/
(function () {
    //Affiche une alerte discrète dans le conteneur #alerts

    function showInlineAlert(message, type = "info") {
        const stack = document.getElementById("alerts");
        if (!stack) return;

        const el = document.createElement("div");
        el.className = `custom-alert alert-${type} auto-dismiss fade-in`;
        el.setAttribute("role", "alert");

        const close = document.createElement("button");
        close.type = "button";
        close.className = "btn-close";
        close.setAttribute("aria-label", "Close");

        const content = document.createElement("div");
        content.className = "content";
        content.textContent = message;

        el.append(close, content);
        stack.appendChild(el);
    }

    // Délégation d'événement: on intercepte les clics sur tout le document
    document.addEventListener("click", (e) => {
        // Recherche le déclencheur de la modale parmi la cible ou ses parents
        const trigger = e.target.closest(
            '[data-bs-target="#createCovoitModal"]',
        );
        // Si le clic ne concerne pas l'ouverture de la modale, on sort
        if (!trigger) return;

        // Vérifie la présence d'un véhicule via <body data-has-vehicle="1">
        const hasVehicle = document.body.dataset.hasVehicle === "1";
        if (!hasVehicle) {
            // Empêche le comportement par défaut (ouverture de la modale) et stoppe la propagation
            e.preventDefault();
            e.stopPropagation();

            // Affiche une alerte informant l'utilisateur de l'action préalable requise
            showInlineAlert(
                "Vous devez d'abord ajouter un véhicule pour créer un covoiturage.",
                "warning",
            );
            return;
        }
    });
})();
