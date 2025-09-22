/*
Module: Covoit Guard
Rôle: Empêcher l’ouverture de la modale de création si aucun véhicule n’est déclaré.
Prérequis: <body data-has-vehicle="0|1">, conteneur #alerts (pour l’alerte).
Utilisation: Bouton qui ouvre la modale via data-bs-target="#createCovoitModal".
*/
(function () {
    //Affiche une alerte discrète dans le conteneur #alerts

    function showInlineAlert(message, type = "info") {
        // Récupère la pile d'alertes; si absente on abandonne silencieusement
        const stack = document.getElementById("alerts");
        if (!stack) return;

        // Crée le conteneur de l'alerte avec quelques classes utilitaires (CSS custom)
        const el = document.createElement("div");
        el.className = `custom-alert alert-${type} auto-dismiss fade-in`;
        el.setAttribute("role", "alert");

        // Structure interne: un bouton de fermeture (si géré en CSS/JS) et une zone de contenu
        el.innerHTML = `
      <button type="button" class="btn-close" aria-label="Close"></button>
      <div class="content"></div>
    `;

        // Insère le message dans la zone de contenu puis ajoute l'alerte dans la pile
        el.querySelector(".content").textContent = message;
        stack.appendChild(el);
    }

    // Délégation d'événement: on intercepte les clics sur tout le document
    document.addEventListener("click", (e) => {
        // Recherche le déclencheur de la modale parmi la cible ou ses parents
        const trigger = e.target.closest(
            '[data-bs-target="#createCovoitModal"]'
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
                "warning"
            );

            // Ajoute un lien d'action direct vers la page d'ajout de véhicule (UX douce)
            const link = document.createElement("a");
            link.href = "/vehicle/create";
            link.textContent = "Ajouter un véhicule";
            link.className = "btn btn-inscription btn-sm ms-2";

            // Si la pile d'alertes existe et qu'on vient d'ajouter une alerte,
            // on injecte le lien dans son contenu pour guider l'utilisateur
            const stack = document.getElementById("alerts");
            if (stack?.lastElementChild) {
                stack.lastElementChild
                    .querySelector(".content")
                    ?.appendChild(link);
            }
        }
    });
})();
