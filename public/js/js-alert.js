// Auto-dismiss pour toutes les alertes .auto-dismiss, présentes et futures
(function(){
  const STACK_ID = 'alerts';
  const DEFAULT_DELAY = 4000;

  function fadeAndRemove(el, delayMs, i=0){
    setTimeout(() => {
      el.style.transition = 'opacity .5s ease, transform .5s ease';
      el.style.opacity = '0';
      el.style.transform = 'translateY(-6px)';
      setTimeout(() => el.remove(), 500);
    }, delayMs + (i * 120)); // léger décalage entre chaque bannière
  }

  function scheduleAll(scope=document){
    const alerts = Array.from(scope.querySelectorAll('.auto-dismiss'));
    alerts.forEach((el, idx) => {
      // évite double planification
      if (el.dataset.dismissScheduled) return;
      el.dataset.dismissScheduled = '1';
      const delay = parseInt(el.dataset.timeout||'',10) || DEFAULT_DELAY;
      fadeAndRemove(el, delay, idx);
    });
  }

  // 1) au chargement
  document.addEventListener('DOMContentLoaded', () => {
    scheduleAll();
  });

  // 2) pour les nouvelles alertes ajoutées dynamiquement (ex: showGlobalAlert)
  const stack = document.getElementById(STACK_ID);
  if (stack) {
    const mo = new MutationObserver(muts => {
      muts.forEach(m => {
        m.addedNodes.forEach(n => {
          if (n.nodeType === 1) {
            if (n.matches?.('.auto-dismiss')) {
              scheduleAll(n.parentNode || document);
            } else {
              // si on ajoute un conteneur avec plusieurs enfants
              scheduleAll(n);
            }
          }
        });
      });
    });
    mo.observe(stack, { childList: true, subtree: true });
  }
})();
