// Bloque l'ouverture de la modale de création si l'utilisateur n'a pas de véhicule
(function () {
  function showInlineAlert(message, type = "info") {
    const stack = document.getElementById("alerts");
    if (!stack) return;
    const el = document.createElement("div");
    el.className = `custom-alert alert-${type} auto-dismiss fade-in`;
    el.setAttribute("role", "alert");
    el.innerHTML = `
      <button type="button" class="btn-close" aria-label="Close"></button>
      <div class="content"></div>
    `;
    el.querySelector(".content").textContent = message;
    stack.appendChild(el);
  }

  document.addEventListener("click", (e) => {
    const trigger = e.target.closest('[data-bs-target="#createCovoitModal"]');
    if (!trigger) return;

    const hasVehicle = document.body.dataset.hasVehicle === "1";
    if (!hasVehicle) {
      e.preventDefault();
      e.stopPropagation();
      showInlineAlert(
        "Vous devez d'abord ajouter un véhicule pour créer un covoiturage.",
        "warning"
      );
      // Propose une redirection douce
      const link = document.createElement("a");
      link.href = "/vehicle/create";
      link.textContent = "Ajouter un véhicule";
      link.className = "btn btn-inscription btn-sm ms-2";
      const stack = document.getElementById("alerts");
      if (stack?.lastElementChild) {
        stack.lastElementChild.querySelector(".content")?.appendChild(link);
      }
    }
  });
})();
