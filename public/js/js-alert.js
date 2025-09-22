(function () {
  // Module d'alertes front:
  // - Auto-fermeture des éléments .auto-dismiss après un délai
  // - Animation de fade/translate avant suppression du DOM
  // - Observation des nouveaux nœuds injectés pour planifier automatiquement leur fermeture
  // - Fermeture manuelle au clic sur la croix .btn-close (géré plus bas)
  const STACK_ID = "alerts";
  const DEFAULT_DELAY = 12000;

  // Programme la disparition d'un élément avec une petite animation
  // delayMs: délai avant démarrage de l'animation
  // i: index pour décaler légèrement (stagger) les disparitions si plusieurs alertes
  function fadeAndRemove(el, delayMs, i = 0) {
    setTimeout(() => {
      el.style.transition = "opacity .5s ease, transform .5s ease";
      el.style.opacity = "0";
      el.style.transform = "translateY(-6px)";
      setTimeout(() => el.remove(), 500);
    }, delayMs + i * 120);
  }

  // Cherche toutes les alertes .auto-dismiss dans 'scope' et les planifie
  // Une alerte n'est planifiée qu'une seule fois (flag data-dismiss-scheduled)
  function scheduleAll(scope = document) {
    const alerts = Array.from(scope.querySelectorAll(".auto-dismiss"));
    alerts.forEach((el, idx) => {
      if (el.dataset.dismissScheduled) return;
      el.dataset.dismissScheduled = "1";
      const delay = parseInt(el.dataset.timeout || "", 10) || DEFAULT_DELAY;
      fadeAndRemove(el, delay, idx);
    });
  }

  // 🔧 nouvelle fonction
  // Attache un MutationObserver sur la pile d'alertes (#alerts) afin que
  // toute nouvelle alerte ajoutée soit automatiquement planifiée.
  function attachObserver(stack) {
    if (!stack || stack.dataset.observer) return;
    const mo = new MutationObserver((muts) => {
      muts.forEach((m) => {
        m.addedNodes.forEach((n) => {
          if (n.nodeType === 1) {
            if (n.matches?.(".auto-dismiss")) {
              scheduleAll(n.parentNode || document);
            } else {
              scheduleAll(n);
            }
          }
        });
      });
    });
    mo.observe(stack, { childList: true, subtree: true });
    stack.dataset.observer = "1";
  }

  // 1) au chargement
  // À la fin du chargement du DOM, on planifie les alertes déjà présentes
  // et on s'assure que #alerts est bien observé.
  document.addEventListener("DOMContentLoaded", () => {
    scheduleAll();
    // (re)trouve #alerts même si le script a tourné avant que le DOM soit prêt
    attachObserver(document.getElementById(STACK_ID));
  });

  // 2) tentative d’attache immédiate (si #alerts existe déjà)
  // Permet de gérer le cas où #alerts est présent avant DOMContentLoaded
  attachObserver(document.getElementById(STACK_ID));
})();

// Fermer au clic sur la croix
// Écoute globale: si on clique sur .custom-alert .btn-close,
// on supprime simplement l'alerte du DOM.
document.addEventListener("click", (e) => {
  if (e.target.matches(".custom-alert .btn-close")) {
    e.target.closest(".custom-alert")?.remove();
  }
});
