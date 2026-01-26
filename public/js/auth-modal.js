/*
Module: Auth Modal (version simplifiée DWWM)
Rôle: onglets, submit AJAX (login/register), affichage messages, redirection, logout.
Sécurité: CSRF envoyé via header X-CSRF-Token (si présent) + cookies session same-origin.
*/

// --- UI helpers ---
function showAlert(message, type = "danger") {
    const modalAlert = document.querySelector("#authModal #authAlert");
    if (!modalAlert) return;

    modalAlert.className = `custom-alert alert-${type} auto-dismiss fade-in`;
    modalAlert.textContent = message;
    modalAlert.classList.remove("d-none");

    setTimeout(() => modalAlert.classList.add("d-none"), 5000);
}

function showGlobalAlert(message, type = "success") {
    const stack = document.getElementById("alerts");
    if (!stack) return;

    const el = document.createElement("div");
    el.className = `custom-alert alert-${type} auto-dismiss fade-in`;
    el.textContent = message;

    stack.appendChild(el);

    setTimeout(() => el.classList.add("fade-out"), 3500);
    setTimeout(() => el.remove(), 4300);
}

function hideAlert() {
    const alertDiv = document.getElementById("authAlert");
    if (!alertDiv) return;
    alertDiv.classList.add("d-none");
}

function setLoading(form, isLoading) {
    const button = form?.querySelector('button[type="submit"]');
    if (!button) return;

    const btnText = button.querySelector(".btn-text");
    const spinner = button.querySelector(".spinner-border");

    button.disabled = !!isLoading;
    if (btnText) btnText.classList.toggle("d-none", !!isLoading);
    if (spinner) spinner.classList.toggle("d-none", !isLoading);
}

function setActiveTab(tab) {
    hideAlert();

    const loginForm = document.getElementById("loginForm");
    const registerForm = document.getElementById("registerForm");
    const showLogin = document.getElementById("showLogin");
    const showRegister = document.getElementById("showRegister");
    const title = document.getElementById("authModalLabel");

    if (!loginForm || !registerForm || !showLogin || !showRegister || !title) return;

    const isLogin = tab === "login";

    loginForm.classList.toggle("d-none", !isLogin);
    registerForm.classList.toggle("d-none", isLogin);

    showLogin.classList.toggle("active-tab", isLogin);
    showRegister.classList.toggle("active-tab", !isLogin);

    title.innerText = isLogin ? "Connexion" : "Inscription";
}

// --- API call (login/register) ---
async function handleAuth(endpoint, formDataObj) {
    const csrf =
        formDataObj.csrf || document.querySelector('input[name="csrf"]')?.value;

    const fallbackRedirect = (() => {
        try {
            return window.location.pathname + window.location.search;
        } catch (_) {
            return "/";
        }
    })();

    // si pas de redirect dans le form, on met la page courante
    if (!formDataObj.redirect) {
        formDataObj.redirect = fallbackRedirect;
    }

    try {
        const response = await fetch(`/api/auth/${endpoint}`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                ...(csrf ? { "X-CSRF-Token": csrf } : {}),
            },
            credentials: "same-origin",
            body: JSON.stringify(formDataObj),
        });

        const data = await response.json();

        if (!data?.success) {
            showAlert(data?.message || "Erreur.", "danger");
            return { ok: false };
        }

        showAlert(data.message || "Succès.", "success");

        if (endpoint === "login") {
            setTimeout(() => {
                const to = data.redirect || formDataObj.redirect || fallbackRedirect || "/";
                window.location.href = to;
            }, 800);
        } else {
            // register
            setTimeout(() => {
                setActiveTab("login");
                const emailLogin = document.getElementById("emailLogin");
                if (emailLogin && formDataObj.email) emailLogin.value = formDataObj.email;
            }, 800);
        }

        return { ok: true, data };
    } catch (err) {
        console.error(err);
        showAlert("Erreur de connexion au serveur", "danger");
        return { ok: false };
    }
}

// --- Minimal validation helpers (simple + jury-friendly) ---
function formToObject(form) {
    return Object.fromEntries(new FormData(form));
}

function focusFirstInvalid(form) {
    const first = form.querySelector(":invalid");
    if (first) first.focus();
}

// --- DOM init ---
document.addEventListener("DOMContentLoaded", () => {
    const authModal = document.getElementById("authModal");
    const showLogin = document.getElementById("showLogin");
    const showRegister = document.getElementById("showRegister");
    const loginForm = document.getElementById("loginForm");
    const registerForm = document.getElementById("registerForm");

    if (!authModal || !loginForm || !registerForm) return;

    // Tabs
    showLogin?.addEventListener("click", () => setActiveTab("login"));
    showRegister?.addEventListener("click", () => setActiveTab("register"));

    // Register submit (validation minimale)
    registerForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        hideAlert();

        // HTML5 validation (required/email/minlength/pattern)
        if (!registerForm.checkValidity()) {
            registerForm.reportValidity();
            focusFirstInvalid(registerForm);
            return;
        }

        const pwd = registerForm.querySelector("#passwordRegister")?.value || "";
        const confirm = registerForm.querySelector("#confirmPassword")?.value || "";
        if (pwd !== confirm) {
            showAlert("Les mots de passe ne correspondent pas.", "danger");
            registerForm.querySelector("#confirmPassword")?.focus();
            return;
        }

        setLoading(registerForm, true);

        const payload = formToObject(registerForm);
        const result = await handleAuth("register", payload);

        if (!result.ok) setLoading(registerForm, false);
        // si ok, on laisse tel quel (tu changes d’onglet)
    });

    // Login submit (validation minimale)
    loginForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        hideAlert();

        if (!loginForm.checkValidity()) {
            loginForm.reportValidity();
            focusFirstInvalid(loginForm);
            return;
        }

        setLoading(loginForm, true);

        const payload = formToObject(loginForm);
        const result = await handleAuth("login", payload);

        if (!result.ok) setLoading(loginForm, false);
        // si ok, redirection arrive
    });

    // Modal opening (buttons data-bs-target="#authModal")
    const modal = new bootstrap.Modal(authModal);

    document.querySelectorAll('[data-bs-target="#authModal"]').forEach((btn) => {
        btn.addEventListener("click", () => {
            const start = btn.getAttribute("data-start") || "register";
            setActiveTab(start);

            // Pré-remplir redirect dans le loginForm
            try {
                const red = loginForm.querySelector('input[name="redirect"]');
                if (red) red.value = window.location.pathname + window.location.search;
            } catch (_) {}

            modal.show();
        });
    });

    // Auto open on /login
    try {
        if (window.location.pathname === "/login") {
            setActiveTab("login");
            const red = loginForm.querySelector('input[name="redirect"]');
            if (red) red.value = "/";
            modal.show();
        }
    } catch (_) {}

    // Reset on close
    authModal.addEventListener("hidden.bs.modal", () => {
        registerForm.reset();
        loginForm.reset();
        hideAlert();
        setLoading(registerForm, false);
        setLoading(loginForm, false);
    });
});

// Logout via API (AJAX)
const logoutBtn = document.getElementById("logoutBtn");
if (logoutBtn) {
    logoutBtn.addEventListener("click", async (e) => {
        e.preventDefault();
        try {
            const response = await fetch("/api/auth/logout", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                credentials: "same-origin",
            });
            const data = await response.json();

            if (data?.success) {
                showGlobalAlert("Vous êtes bien déconnecté(e) !", "success");
                setTimeout(() => (window.location.href = "/"), 700);
            } else {
                showGlobalAlert("Erreur lors de la déconnexion", "danger");
            }
        } catch (err) {
            showGlobalAlert("Erreur lors de la déconnexion", "danger");
        }
    });
}
