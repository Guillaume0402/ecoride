// SOLUTION : Déplacer les fonctions utilitaires en dehors du DOMContentLoaded

// Fonction pour afficher les messages
function showAlert(message, type = "danger") {
    const modalAlert = document.querySelector("#authModal #authAlert");
    if (!modalAlert) return;

    modalAlert.className = `custom-alert alert-${type} auto-dismiss fade-in`;
    modalAlert.textContent = message;
    modalAlert.classList.remove("d-none");

    setTimeout(() => {
        modalAlert.classList.add("d-none");
    }, 5000);
}

// Fonction pour afficher des alertes globales (hors modal)
function showGlobalAlert(message, type = "success") {
    const stack = document.getElementById("alerts");
    if (!stack) return;

    const el = document.createElement("div");
    el.className = `custom-alert alert-${type} auto-dismiss fade-in`;
    el.textContent = message;

    stack.appendChild(el);

    // Auto fermeture comme tes flash PHP
    setTimeout(() => el.classList.add("fade-out"), 3500);
    setTimeout(() => el.remove(), 4300);
}


// Fonction pour masquer les messages
function hideAlert() {
    const alertDiv = document.getElementById("authAlert");
    alertDiv.classList.add("d-none");
}

// Fonction pour gérer le loading
function setLoading(form, isLoading) {
    const button = form.querySelector('button[type="submit"]');
    const btnText = button.querySelector(".btn-text");
    const spinner = button.querySelector(".spinner-border");

    if (isLoading) {
        button.disabled = true;
        btnText.classList.add("d-none");
        spinner.classList.remove("d-none");
    } else {
        button.disabled = false;
        btnText.classList.remove("d-none");
        spinner.classList.add("d-none");
    }
}

// Fonction pour changer d'onglet (connexion/inscription)
function setActiveTab(tab) {
    hideAlert(); // Masquer les alertes lors du changement d'onglet

    const loginForm = document.getElementById("loginForm");
    const registerForm = document.getElementById("registerForm");
    const showLogin = document.getElementById("showLogin");
    const showRegister = document.getElementById("showRegister");
    const title = document.getElementById("authModalLabel");

    if (tab === "login") {
        loginForm.classList.remove("d-none");
        registerForm.classList.add("d-none");
        showLogin.classList.add("active-tab");
        showRegister.classList.remove("active-tab");
        title.innerText = "Connexion";
    } else {
        registerForm.classList.remove("d-none");
        loginForm.classList.add("d-none");
        showRegister.classList.add("active-tab");
        showLogin.classList.remove("active-tab");
        title.innerText = "Inscription";
    }
}

// Fonction pour gérer l'authentification (connexion/inscription)
// Utilise fetch pour appeler les API correspondantes
// Renvoie une promesse pour gérer les réponses
async function handleAuth(endpoint, formData) {
    // récupère le token si présent (loginForm)
    const csrf =
        formData.csrf || document.querySelector('input[name="csrf"]')?.value;
    try {
        const response = await fetch(`/api/auth/${endpoint}`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                ...(csrf ? { "X-CSRF-Token": csrf } : {})
            },
            body: JSON.stringify(formData),
        });

        const data = await response.json();

        if (data.success) {
            // Désactiver tous les champs du formulaire courant
            const currentForm =
                endpoint === "login"
                    ? document.getElementById("loginForm")
                    : document.getElementById("registerForm");
            Array.from(currentForm.elements).forEach(
                (el) => (el.disabled = true)
            );
            // Optionnel : désactiver les interactions et réduire l'opacité
            currentForm.style.pointerEvents = "none";
            currentForm.style.opacity = 0.7; // (optionnel, visuel)

            showAlert(data.message, "success");

            if (endpoint === "login") {
                setTimeout(() => {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        window.location.reload();
                    }
                }, 1500);
            } else if (endpoint === "register") {
                setTimeout(() => {
                    setActiveTab("login");
                    // Pré-remplir l'email dans le formulaire de connexion
                    document.getElementById("emailLogin").value =
                        formData.email;
                }, 1500);
            }
            return true; // <-- Retourne true si succès
        } else {
            showAlert(data.message, "danger");
            return false; // <-- Retourne false si erreur serveur/app
        }
    } catch (error) {
        showAlert("Erreur de connexion au serveur", "danger");
        console.error("Erreur:", error);
        return false; // <-- Retourne false si erreur réseau/JS
    }
}

// Les événements de la modale restent dans le DOMContentLoaded
document.addEventListener("DOMContentLoaded", () => {
    const authModal = document.getElementById("authModal");
    const showLogin = document.getElementById("showLogin");
    const showRegister = document.getElementById("showRegister");
    const loginForm = document.getElementById("loginForm");
    const registerForm = document.getElementById("registerForm");

    // Gestionnaires pour les boutons de changement d'onglet
    showLogin.addEventListener("click", () => setActiveTab("login"));
    showRegister.addEventListener("click", () => setActiveTab("register"));

    // Helpers validation visuelle
    const setInvalid = (input, message) => {
        input.classList.add("is-invalid");
        const fb = input.parentElement.querySelector(".invalid-feedback");
        if (fb && message) fb.textContent = message;
    };
    const setValid = (input) => {
        input.classList.remove("is-invalid");
    };

    // Règles de validation registration
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s])(?!.*\s).+$/;
    const validateRegisterField = (input) => {
        const id = input.id;
        const val = input.value.trim();
        if (id === "username") {
            if (val.length < 3) return setInvalid(input, "Veuillez renseigner un pseudo (3 caractères minimum)."), false;
            return setValid(input), true;
        }
        if (id === "emailRegister") {
            if (!input.checkValidity()) return setInvalid(input, "Veuillez entrer une adresse email valide."), false;
            return setValid(input), true;
        }
        if (id === "passwordRegister") {
            if (val.length < 12 || !passwordRegex.test(val)) return setInvalid(input, "Votre mot de passe ne respecte pas les règles de sécurité."), false;
            return setValid(input), true;
        }
        if (id === "confirmPassword") {
            const pwd = document.getElementById("passwordRegister").value;
            if (val !== pwd) return setInvalid(input, "Les mots de passe ne correspondent pas."), false;
            return setValid(input), true;
        }
        return true;
    };

    const validateLoginField = (input) => {
        const id = input.id;
        if (id === "emailLogin") {
            if (!input.checkValidity()) return setInvalid(input, "Veuillez entrer une adresse email valide."), false;
            return setValid(input), true;
        }
        if (id === "passwordLogin") {
            if (!input.value) return setInvalid(input, "Veuillez saisir votre mot de passe."), false;
            return setValid(input), true;
        }
        return true;
    };

    const attachLiveValidation = (form, validator) => {
        form.querySelectorAll("input").forEach((input) => {
            input.addEventListener("input", () => validator(input));
            input.addEventListener("blur", () => validator(input));
        });
    };

    attachLiveValidation(registerForm, validateRegisterField);
    attachLiveValidation(loginForm, validateLoginField);

    const validateForm = (form, validator) => {
        let firstInvalid = null;
        let ok = true;
        form.querySelectorAll("input").forEach((input) => {
            const valid = validator(input);
            if (!valid && !firstInvalid) firstInvalid = input, ok = false;
        });
        if (!ok && firstInvalid) firstInvalid.focus();
        return ok;
    };

    // Gestionnaires pour les formulaires
    registerForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        hideAlert();
        // Validation custom au submit
        if (!validateForm(registerForm, validateRegisterField)) {
            return; // messages affichés via invalid-feedback
        }
        setLoading(registerForm, true);

        const formData = new FormData(registerForm);
        const data = Object.fromEntries(formData);

        // pas besoin du check ici, déjà fait via validateForm

        const result = await handleAuth("register", data);

        if (!result) {
            setLoading(registerForm, false);
        }
    });

    // Gestion du formulaire de connexion
    loginForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        hideAlert();
        if (!validateForm(loginForm, validateLoginField)) {
            return;
        }
        setLoading(loginForm, true);

        const formData = new FormData(loginForm);
        const data = Object.fromEntries(formData);

        // handleAuth renvoie une promesse, tu peux savoir s’il y a eu succès ou non
        const result = await handleAuth("login", data);

        // ATTENTION : setLoading(loginForm, false) ne doit être fait QUE si erreur !
        // Donc : si le login a échoué, on réactive, sinon NON
        if (!result) {
            setLoading(loginForm, false);
        }
    });

    // Gestion de l'ouverture de la modal
    const modal = new bootstrap.Modal(authModal);
    document
        .querySelectorAll('[data-bs-target="#authModal"]')
        .forEach((button) => {
            button.addEventListener("click", () => {
                const start = button.getAttribute("data-start") || "register";
                setActiveTab(start);
                modal.show();
            });
        });

    // Réinitialiser les formulaires à la fermeture
    authModal.addEventListener("hidden.bs.modal", () => {
        registerForm.reset();
        loginForm.reset();
        registerForm.querySelectorAll(".is-invalid").forEach((el)=>el.classList.remove("is-invalid"));
        loginForm.querySelectorAll(".is-invalid").forEach((el)=>el.classList.remove("is-invalid"));
        hideAlert();
        setLoading(registerForm, false);
        setLoading(loginForm, false);
    });
});

// Déconnexion via API (AJAX)
const logoutBtn = document.getElementById("logoutBtn");
if (logoutBtn) {
    logoutBtn.addEventListener("click", async (e) => {
        e.preventDefault();
        try {
            const response = await fetch("/api/auth/logout", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
            });
            const data = await response.json();
            if (data.success) {
                showGlobalAlert("Vous êtes bien déconnecté(e) !", "success");
                setTimeout(() => {
                    window.location.href = "/"; // Redirige vers l'accueil ou où tu veux
                }, 1000);
            } else {
                showGlobalAlert("Erreur lors de la déconnexion", "danger");
            }
        } catch (err) {
            showGlobalAlert("Erreur lors de la déconnexion", "danger");
        }
    });
}
