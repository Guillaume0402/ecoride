/*
Module: JS Alerts
Rôle: Gérer les alertes globales (#alerts) : auto-fermeture, animation, observer.
Prérequis: Conteneur #alerts, classes .custom-alert et .auto-dismiss.
*/
(function () {
    const STACK_ID = "alerts";
    const DEFAULT_DELAY = 12000;

    function fadeAndRemove(el, delayMs, i = 0) {
        setTimeout(
            () => {
                if (!el.isConnected) return;
                el.classList.add("fade-out");
                setTimeout(() => el.remove(), 650);
            },
            delayMs + i * 120,
        );
    }

    function scheduleAll(stack) {
        if (!stack) return;

        const alerts = Array.from(stack.querySelectorAll(".auto-dismiss"));
        alerts.forEach((el, idx) => {
            if (el.dataset.dismissScheduled) return;
            el.dataset.dismissScheduled = "1";

            const delay =
                parseInt(el.dataset.timeout || "", 10) || DEFAULT_DELAY;

            fadeAndRemove(el, delay, idx);
        });
    }

    function attachObserver(stack) {
        if (!stack || stack.dataset.observer) return;

        const mo = new MutationObserver((muts) => {
            muts.forEach((m) => {
                m.addedNodes.forEach((n) => {
                    if (n.nodeType !== 1) return;
                    // Dès qu'une alerte est ajoutée dans #alerts, on planifie
                    scheduleAll(stack);
                });
            });
        });

        mo.observe(stack, { childList: true });
        stack.dataset.observer = "1";
    }

    document.addEventListener("DOMContentLoaded", () => {
        const stack = document.getElementById(STACK_ID);
        attachObserver(stack);
        scheduleAll(stack);
    });

    // si le script est chargé après le HTML, ça marche aussi
    const earlyStack = document.getElementById(STACK_ID);
    attachObserver(earlyStack);
    scheduleAll(earlyStack);
})();
