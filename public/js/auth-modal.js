/*
Module: Auth Modal
Rôle: Gérer l’interface de connexion/inscription (onglets, validation, force du mot de passe, redirections) et la déconnexion.
Utilisation: Boutons avec data-bs-target="#authModal" pour ouvrir la modale.
*/
// Fonction pour afficher les messages d'erreur/succès dans la modale
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

// Fonction pour masquer le message d'alerte de la modale
function hideAlert() {
    const alertDiv = document.getElementById("authAlert");
    alertDiv.classList.add("d-none");
}

// Fonction pour gérer l'état "chargement" d'un formulaire (désactive le bouton et affiche un spinner)
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

// Fonction générique pour gérer l'authentification (connexion/inscription)
// - envoie les données en JSON à l'API
// - gère l'affichage des messages
// - applique la redirection ou le changement d'onglet en cas de succès
async function handleAuth(endpoint, formData) {
    // récupère le token si présent (loginForm)
    // Récupère le token CSRF si présent (protège contre les attaques CSRF)
    const csrf =
        formData.csrf || document.querySelector('input[name="csrf"]')?.value;
    // URL de redirection par défaut : la page actuelle
    const fallbackRedirect = (() => {
        try {
            return window.location.pathname + window.location.search;
        } catch (_) {
            return "/";
        }
    })();
    // Inclure une cible de redirection si fournie par le formulaire, sinon l’URL courante
    if (!formData.redirect) {
        formData.redirect = fallbackRedirect;
    }
    try {
        // Appel à l'API d'authentification correspondante (login ou register)
        const response = await fetch(`/api/auth/${endpoint}`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                ...(csrf ? { "X-CSRF-Token": csrf } : {}),
            },
            // Important: inclure les cookies de session pour SameSite Lax
            credentials: "same-origin",
            body: JSON.stringify(formData),
        });

        // On suppose que l'API renvoie du JSON { success: bool, message: string, redirect?: string }
        const data = await response.json();

        if (data.success) {
            // Désactiver tous les champs du formulaire courant
            const currentForm =
                endpoint === "login"
                    ? document.getElementById("loginForm")
                    : document.getElementById("registerForm");
            // Désactive tous les champs et boutons du formulaire pour éviter les double-clics
            Array.from(currentForm.elements).forEach(
                (el) => (el.disabled = true)
            );
            // Optionnel : désactiver les interactions et réduire l'opacité
            currentForm.style.pointerEvents = "none";
            currentForm.style.opacity = 0.7; // (optionnel, visuel)

            showAlert(data.message, "success");

            // Cas connexion : après un petit délai, on redirige l'utilisateur
            if (endpoint === "login") {
                setTimeout(() => {
                    const to =
                        data.redirect ||
                        formData.redirect ||
                        fallbackRedirect ||
                        "/";
                    if (to) {
                        window.location.href = to;
                    } else {
                        window.location.reload();
                    }
                }, 1500);
            // Cas inscription : on repasse sur l'onglet login et on pré-remplit l'email
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

// Tout le code qui touche au DOM est exécuté après le chargement de la page
document.addEventListener("DOMContentLoaded", () => {
    const authModal = document.getElementById("authModal");
    const showLogin = document.getElementById("showLogin");
    const showRegister = document.getElementById("showRegister");
    const loginForm = document.getElementById("loginForm");
    const registerForm = document.getElementById("registerForm");

    // Gestionnaires pour les boutons de changement d'onglet (Connexion / Inscription)
    showLogin.addEventListener("click", () => setActiveTab("login"));
    showRegister.addEventListener("click", () => setActiveTab("register"));

    // Helpers pour la validation visuelle des champs (affiche/retire la classe .is-invalid)
    const setInvalid = (input, message) => {
        input.classList.add("is-invalid");
        const fb = input.parentElement.querySelector(".invalid-feedback");
        if (fb && message) fb.textContent = message;
    };
    const setValid = (input) => {
        input.classList.remove("is-invalid");
    };

    // Règles de validation registration
    // Règle de complexité du mot de passe (minuscule, majuscule, chiffre, caractère spécial, pas d'espace)
    const passwordRegex =
        /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s])(?!.*\s).+$/;
    // Validation champ par champ pour le formulaire d'inscription
    const validateRegisterField = (input) => {
        const id = input.id;
        const val = input.value.trim();
        if (id === "username") {
            if (val.length < 3)
                return (
                    setInvalid(
                        input,
                        "Veuillez renseigner un pseudo (3 caractères minimum)."
                    ),
                    false
                );
            return setValid(input), true;
        }
        if (id === "emailRegister") {
            if (!input.checkValidity())
                return (
                    setInvalid(
                        input,
                        "Veuillez entrer une adresse email valide."
                    ),
                    false
                );
            return setValid(input), true;
        }
        if (id === "passwordRegister") {
            if (val.length < 12 || !passwordRegex.test(val))
                return (
                    setInvalid(
                        input,
                        "Votre mot de passe ne respecte pas les règles de sécurité."
                    ),
                    false
                );
            return setValid(input), true;
        }
        if (id === "confirmPassword") {
            const pwd = document.getElementById("passwordRegister").value;
            if (val !== pwd)
                return (
                    setInvalid(
                        input,
                        "Les mots de passe ne correspondent pas."
                    ),
                    false
                );
            return setValid(input), true;
        }
        return true;
    };

    // Validation champ par champ pour le formulaire de connexion
    const validateLoginField = (input) => {
        const id = input.id;
        if (id === "emailLogin") {
            if (!input.checkValidity())
                return (
                    setInvalid(
                        input,
                        "Veuillez entrer une adresse email valide."
                    ),
                    false
                );
            return setValid(input), true;
        }
        if (id === "passwordLogin") {
            if (!input.value)
                return (
                    setInvalid(input, "Veuillez saisir votre mot de passe."),
                    false
                );
            return setValid(input), true;
        }
        return true;
    };

    // Ajoute la validation "en direct" (input + blur) sur tous les champs du formulaire
    const attachLiveValidation = (form, validator) => {
        form.querySelectorAll("input").forEach((input) => {
            input.addEventListener("input", () => validator(input));
            input.addEventListener("blur", () => validator(input));
        });
    };

    attachLiveValidation(registerForm, validateRegisterField);
    attachLiveValidation(loginForm, validateLoginField);

    // Gestion de la jauge de robustesse du mot de passe et des critères détaillés
    const pwdInput = document.getElementById("passwordRegister");
    const strengthBar = document.getElementById("passwordStrengthBar");
    const strengthText = document.getElementById("passwordStrengthText");

    // Calcule un score simple de robustesse de mot de passe (0 à 100)
    const computeStrength = (pwd) => {
        let score = 0;
        if (pwd.length >= 12) score += 25;
        if (/[a-z]/.test(pwd)) score += 15;
        if (/[A-Z]/.test(pwd)) score += 15;
        if (/\d/.test(pwd)) score += 15;
        if (/[^\w\s]/.test(pwd)) score += 20;
        if (pwd.length >= 16) score += 10;
        return Math.min(score, 100);
    };

    // Met à jour l'affichage de la barre et du texte de robustesse
    const updateStrengthUI = (score) => {
        if (!strengthBar || !strengthText) return;
        strengthBar.style.width = `${score}%`;
        strengthBar.setAttribute("aria-valuenow", String(score));
        let label = "très faible",
            cls = "bg-danger";
        if (score >= 25) (label = "faible"), (cls = "bg-danger");
        if (score >= 50) (label = "moyenne"), (cls = "bg-warning");
        if (score >= 75) (label = "bonne"), (cls = "bg-success");
        if (score >= 90) (label = "excellente"), (cls = "bg-success");
        strengthBar.className = `progress-bar ${cls}`;
        strengthText.textContent = `Robustesse : ${label}`;
    };

    if (pwdInput) {
        updateStrengthUI(0);
        // Éléments de la liste des critères (longueur, majuscules, etc.)
        const criteriaEls = {
            len: document.querySelector('.password-criteria [data-crit="len"]'),
            lower: document.querySelector(
                '.password-criteria [data-crit="lower"]'
            ),
            upper: document.querySelector(
                '.password-criteria [data-crit="upper"]'
            ),
            digit: document.querySelector(
                '.password-criteria [data-crit="digit"]'
            ),
            special: document.querySelector(
                '.password-criteria [data-crit="special"]'
            ),
            space: document.querySelector(
                '.password-criteria [data-crit="space"]'
            ),
        };
        const criteriaList = document.querySelector(".password-criteria");

        // Met à jour l'état visuel de chaque critère en fonction du mot de passe saisi
        const updateCriteria = (pwd) => {
            const hasLower = /[a-z]/.test(pwd);
            const hasUpper = /[A-Z]/.test(pwd);
            const hasDigit = /\d/.test(pwd);
            const hasSpecial = /[^\w\s]/.test(pwd);
            const hasLen = pwd.length >= 12;
            const hasNoSpace = !/\s/.test(pwd);

            criteriaEls.lower &&
                criteriaEls.lower.classList.toggle("ok", hasLower);
            criteriaEls.upper &&
                criteriaEls.upper.classList.toggle("ok", hasUpper);
            criteriaEls.digit &&
                criteriaEls.digit.classList.toggle("ok", hasDigit);
            criteriaEls.special &&
                criteriaEls.special.classList.toggle("ok", hasSpecial);
            criteriaEls.len && criteriaEls.len.classList.toggle("ok", hasLen);
            if (criteriaEls.space) {
                criteriaEls.space.classList.toggle("ok", hasNoSpace);
                criteriaEls.space.classList.toggle("bad", !hasNoSpace);
            }
        };

        pwdInput.addEventListener("input", () => {
            const pwd = pwdInput.value;
            updateStrengthUI(computeStrength(pwd));
            updateCriteria(pwd);
            // ne pas auto-afficher/masquer: contrôlé via .toggle-criteria
        });
    }

    // Boutons pour afficher/masquer les mots de passe (œil / œil barré)
    document.querySelectorAll(".toggle-password").forEach((btn) => {
        btn.addEventListener("click", () => {
            const targetId = btn.getAttribute("data-target");
            const input = document.getElementById(targetId);
            if (!input) return;
            const isPwd = input.getAttribute("type") === "password";
            input.setAttribute("type", isPwd ? "text" : "password");
            // swap icon if using Bootstrap Icons
            const icon = btn.querySelector("i");
            if (icon) {
                icon.classList.toggle("bi-eye");
                icon.classList.toggle("bi-eye-slash");
            }
        });
    });

    // Bouton pour afficher/masquer la liste détaillée des critères du mot de passe
    document.querySelectorAll(".toggle-criteria").forEach((btn) => {
        btn.addEventListener("click", () => {
            const list = document.getElementById("passwordCriteriaList");
            if (!list) return;
            const hidden = list.classList.toggle("d-none");
            btn.setAttribute("aria-expanded", String(!hidden));
        });
    });

    // Validation globale d'un formulaire au submit
    // - boucle sur tous les inputs
    // - s'arrête sur le premier champ invalide et lui donne le focus
    const validateForm = (form, validator) => {
        let firstInvalid = null;
        let ok = true;
        form.querySelectorAll("input").forEach((input) => {
            const valid = validator(input);
            if (!valid && !firstInvalid) (firstInvalid = input), (ok = false);
        });
        if (!ok && firstInvalid) firstInvalid.focus();
        return ok;
    };

    // Gestionnaire pour le formulaire d'inscription
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

    // Gestionnaire pour le formulaire de connexion
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

    // Gestion de l'ouverture de la modale via les boutons data-bs-target="#authModal"
    const modal = new bootstrap.Modal(authModal);
    document
        .querySelectorAll('[data-bs-target="#authModal"]')
        .forEach((button) => {
            button.addEventListener("click", () => {
                const start = button.getAttribute("data-start") || "register";
                setActiveTab(start);
                // Pré-remplir le champ hidden redirect avec l'URL courante
                try {
                    const lf = document.getElementById("loginForm");
                    const red = lf?.querySelector('input[name="redirect"]');
                    if (red) {
                        red.value =
                            window.location.pathname + window.location.search;
                    }
                } catch (_) {}
                modal.show();
            });
        });

    // Si on est précisément sur /login, ouvrir automatiquement la modale
    // sur l'onglet Connexion et forcer la redirection post-login vers '/'
    try {
        const isLoginPage = window.location.pathname === "/login";
        if (isLoginPage) {
            setActiveTab("login");
            const lf = document.getElementById("loginForm");
            const red = lf?.querySelector('input[name="redirect"]');
            if (red) {
                red.value = "/"; // éviter de rester bloqué sur /login après connexion
            }
            modal.show();
        }
    } catch (_) {}

    // Réinitialiser les formulaires et les états visuels à la fermeture de la modale
    authModal.addEventListener("hidden.bs.modal", () => {
        registerForm.reset();
        loginForm.reset();
        registerForm
            .querySelectorAll(".is-invalid")
            .forEach((el) => el.classList.remove("is-invalid"));
        loginForm
            .querySelectorAll(".is-invalid")
            .forEach((el) => el.classList.remove("is-invalid"));
        // reset strength meter
        updateStrengthUI(0);
        const list = document.querySelector(".password-criteria");
        if (list) {
            list.classList.add("d-none");
            list.querySelectorAll("li").forEach((li) => {
                li.classList.remove("ok", "bad");
            });
        }
        hideAlert();
        setLoading(registerForm, false);
        setLoading(loginForm, false);
    });
});

// Déconnexion via API (AJAX) quand l'utilisateur clique sur le bouton "logout"
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
