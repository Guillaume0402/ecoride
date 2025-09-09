(function () {
  const STACK_ID = "alerts";
  const DEFAULT_DELAY = 12000;

  function fadeAndRemove(el, delayMs, i = 0) {
    setTimeout(() => {
      el.style.transition = "opacity .5s ease, transform .5s ease";
      el.style.opacity = "0";
      el.style.transform = "translateY(-6px)";
      setTimeout(() => el.remove(), 500);
    }, delayMs + i * 120);
  }

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
  document.addEventListener("DOMContentLoaded", () => {
    scheduleAll();
    // (re)trouve #alerts même si le script a tourné avant que le DOM soit prêt
    attachObserver(document.getElementById(STACK_ID));
  });

  // 2) tentative d’attache immédiate (si #alerts existe déjà)
  attachObserver(document.getElementById(STACK_ID));
})();

// Fermer au clic sur la croix
document.addEventListener("click", (e) => {
  if (e.target.matches(".custom-alert .btn-close")) {
    e.target.closest(".custom-alert")?.remove();
  }
});
