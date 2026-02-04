/*
====================================================
 Module : Auth Modal (DWWM – pédagogique)
----------------------------------------------------
 Rôle :
 - Gérer la modale d’authentification (inscription / connexion)
 - Envoyer les formulaires en AJAX (fetch)
 - Afficher les messages d’erreur ou de succès
 - Gérer la redirection après connexion
 - Gérer la déconnexion via l’API

 Sécurité :
 - CSRF envoyé via l’en-tête HTTP X-CSRF-Token
 - Cookies de session envoyés avec credentials: "same-origin"
====================================================
*/

/* ==================================================
   FONCTIONS UI (affichage / feedback utilisateur)
   ================================================== */

// Affiche un message global (pile #alerts en haut de page)
function showGlobalAlert(message, type = "success", timeout = 4500) {
    const stack = document.getElementById("alerts");
    if (!stack) return;

    const el = document.createElement("div");
    el.className = `custom-alert alert-${type} auto-dismiss`;
    el.setAttribute("role", "alert");
    el.dataset.timeout = String(timeout);

    el.innerHTML = `<div class="content"></div>`;

    el.querySelector(".content").textContent = message;

    stack.appendChild(el);
}

// Timer pour l’alerte de la modale
let authAlertTimer = null;

// Affiche un message dans la modale d’authentification
function showAlert(message, type = "danger", timeout = 5000) {
    const el = document.getElementById("authAlert");
    if (!el) return;

    // même style que les alertes globales
    el.className = `custom-alert alert-${type} mx-3`;
    el.innerHTML = `<div class="content"></div>`;
    el.querySelector(".content").textContent = message;
    el.classList.remove("d-none");

    // si un timeout est défini, on masque l’alerte après ce délai
    if (authAlertTimer) clearTimeout(authAlertTimer);
    authAlertTimer = setTimeout(() => el.classList.add("d-none"), timeout);
}

//Cache le message de la modale
function hideAlert() {
    const el = document.getElementById("authAlert");
    if (!el) return;
    el.className = "custom-alert d-none mx-3";
    el.innerHTML = "";
}

/**
 * Active/désactive l’état "chargement" d’un formulaire
 * (désactive le bouton + affiche le spinner)
 */
function setLoading(form, isLoading) {
    const button = form?.querySelector('button[type="submit"]');
    if (!button) return;

    const btnText = button.querySelector(".btn-text");
    const spinner = button.querySelector(".spinner-border");

    button.disabled = !!isLoading;
    if (btnText) btnText.classList.toggle("d-none", !!isLoading);
    if (spinner) spinner.classList.toggle("d-none", !isLoading);
}

/**
 * Active l’onglet Connexion ou Inscription
 */
function setActiveTab(tab) {
    hideAlert();

    const loginForm = document.getElementById("loginForm");
    const registerForm = document.getElementById("registerForm");
    const showLogin = document.getElementById("showLogin");
    const showRegister = document.getElementById("showRegister");
    const title = document.getElementById("authModalLabel");

    if (!loginForm || !registerForm || !showLogin || !showRegister || !title)
        return;

    // Détermine l’onglet actif
    const isLogin = tab === "login";

    // Affiche/masque les formulaires
    loginForm.classList.toggle("d-none", !isLogin);
    registerForm.classList.toggle("d-none", isLogin);
    showLogin.classList.toggle("active-tab", isLogin);
    showRegister.classList.toggle("active-tab", !isLogin);

    title.innerText = isLogin ? "Connexion" : "Inscription";
}

/* ==================================================
   APPEL API AUTH (LOGIN / REGISTER)
   ================================================== */

//Envoie une requête AJAX vers l’API d’authentification

async function handleAuth(endpoint, payload) {
    // Récupération du token CSRF depuis le formulaire
    const csrf = payload?.csrf;

    // URL de retour par défaut
    payload.redirect = payload.redirect || window.location.pathname || "/";

    try {
        const response = await fetch(`/api/auth/${endpoint}`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                ...(csrf ? { "X-CSRF-Token": csrf } : {}),
            },
            credentials: "same-origin",
            body: JSON.stringify(payload),
        });

        let data = null;

        try {
            data = await response.json();
        } catch {
            // Si le serveur ne renvoie pas du JSON (HTML/erreur), on gère proprement
            showAlert("Réponse serveur invalide. Réessayez.", "danger");
            return false;
        }

        if (!response.ok || !data?.success) {
            showAlert(data?.message || "Erreur.", "danger");
            return false;
        }

        showAlert(data.message || "Succès.", "success");

        if (endpoint === "login") {
            setTimeout(() => {
                window.location.href = data.redirect || payload.redirect || "/";
            }, 800);
        } else {
            setTimeout(() => setActiveTab("login"), 800);
        }

        return true;
    } catch (error) {
        console.error(error);
        showAlert("Erreur de communication avec le serveur", "danger");
        return false;
    }
}

/* ==================================================
   OUTILS DE VALIDATION SIMPLE
   ================================================== */

/**
 * Transforme un formulaire en objet JS
 */
function formToObject(form) {
    return Object.fromEntries(new FormData(form));
}

/**
 * Focus le premier champ invalide HTML5
 */
function focusFirstInvalid(form) {
    const first = form.querySelector(":invalid");
    if (first) first.focus();
}

/* ==================================================
   INITIALISATION AU CHARGEMENT DU DOM
   ================================================== */

document.addEventListener("DOMContentLoaded", () => {
    const authModal = document.getElementById("authModal");
    const showLogin = document.getElementById("showLogin");
    const showRegister = document.getElementById("showRegister");
    const loginForm = document.getElementById("loginForm");
    const registerForm = document.getElementById("registerForm");

    if (!authModal || !loginForm || !registerForm) return;

    /* --- Afficher / masquer les mots de passe --- */
    authModal.addEventListener("click", (e) => {
        const btn = e.target.closest(".toggle-password");
        if (!btn) return;

        const targetId = btn.getAttribute("data-target");
        const input = document.getElementById(targetId);
        if (!input) return;

        const isPassword = input.type === "password";
        input.type = isPassword ? "text" : "password";

        const icon = btn.querySelector("i");
        if (icon) {
            icon.classList.toggle("bi-eye", !isPassword);
            icon.classList.toggle("bi-eye-slash", isPassword);
        }
    });

    /* --- Onglets Connexion / Inscription --- */
    showLogin?.addEventListener("click", () => setActiveTab("login"));
    showRegister?.addEventListener("click", () => setActiveTab("register"));

    /* --- Soumission INSCRIPTION --- */
    registerForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        hideAlert();

        if (!registerForm.checkValidity()) {
            registerForm.reportValidity();
            focusFirstInvalid(registerForm);
            return;
        }

        const pwd =
            registerForm.querySelector("#passwordRegister")?.value || "";
        const confirm =
            registerForm.querySelector("#confirmPassword")?.value || "";

        if (pwd !== confirm) {
            showAlert("Les mots de passe ne correspondent pas.", "danger");
            return;
        }

        setLoading(registerForm, true);
        try {
            await handleAuth("register", formToObject(registerForm));
        } finally {
            setLoading(registerForm, false);
        }
    });

    /* --- Soumission CONNEXION --- */
    loginForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        hideAlert();

        if (!loginForm.checkValidity()) {
            loginForm.reportValidity();
            focusFirstInvalid(loginForm);
            return;
        }

        setLoading(loginForm, true);
        try {
            await handleAuth("login", formToObject(loginForm));
        } finally {
            setLoading(loginForm, false);
        }
    });

    /* --- Ouverture de la modale --- */
    const modal = new bootstrap.Modal(authModal);

    document
        .querySelectorAll('[data-bs-target="#authModal"]')
        .forEach((btn) => {
            btn.addEventListener("click", () => {
                setActiveTab(btn.getAttribute("data-start") || "register");

                const red = loginForm.querySelector('input[name="redirect"]');
                if (red)
                    red.value =
                        window.location.pathname + window.location.search;

                modal.show();
            });
        });

    /* --- Ouverture auto sur /login --- */
    if (window.location.pathname === "/login") {
        setActiveTab("login");
        modal.show();
    }

    /* --- Reset à la fermeture --- */
    authModal.addEventListener("hidden.bs.modal", () => {
        registerForm.reset();
        loginForm.reset();
        hideAlert();
        setLoading(registerForm, false);
        setLoading(loginForm, false);
    });

    /* --- Déconnexion AJAX --- */
    const logoutBtn = document.getElementById("logoutBtn");
    if (logoutBtn) {
        logoutBtn.addEventListener("click", async (e) => {
            e.preventDefault();

            // CSRF depuis le <meta> du layout
            const csrf = document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content");

            try {
                const res = await fetch("/api/auth/logout", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        ...(csrf ? { "X-CSRF-Token": csrf } : {}),
                    },
                    credentials: "same-origin",
                });

                const data = await res.json();

                if (data?.success) {
                    showGlobalAlert(
                        "Vous êtes bien déconnecté(e) !",
                        "success",
                    );
                    setTimeout(() => (window.location.href = "/"), 700);
                } else {
                    showGlobalAlert("Erreur lors de la déconnexion", "danger");
                }
            } catch {
                showGlobalAlert("Erreur lors de la déconnexion", "danger");
            }
        });
    }
});
