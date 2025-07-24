// SOLUTION : Déplacer les fonctions utilitaires en dehors du DOMContentLoaded

// Fonction pour afficher les messages
function showAlert(message, type = "danger") {
    const alertDiv = document.getElementById("authAlert");
    alertDiv.className = `alert alert-${type} mx-3`;
    alertDiv.textContent = message;
    alertDiv.classList.remove("d-none");

    // Auto-masquer après 5 secondes
    setTimeout(() => {
        alertDiv.classList.add("d-none");
    }, 5000);
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
    try {
        const response = await fetch(`/api/auth/${endpoint}`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
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
                    window.location.reload();
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

// 🔥 Les événements restent dans le DOMContentLoaded
document.addEventListener("DOMContentLoaded", () => {
    const authModal = document.getElementById("authModal");
    const showLogin = document.getElementById("showLogin");
    const showRegister = document.getElementById("showRegister");
    const loginForm = document.getElementById("loginForm");
    const registerForm = document.getElementById("registerForm");

    // Gestionnaires pour les boutons de changement d'onglet
    showLogin.addEventListener("click", () => setActiveTab("login"));
    showRegister.addEventListener("click", () => setActiveTab("register"));

    // Gestionnaires pour les formulaires
    registerForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        hideAlert();
        setLoading(registerForm, true);

        const formData = new FormData(registerForm);
        const data = Object.fromEntries(formData);

        // Validation côté client
        if (data.password !== data.confirmPassword) {
            showAlert("Les mots de passe ne correspondent pas", "danger");
            setLoading(registerForm, false);
            return;
        }

        const result = await handleAuth("register", data);

        if (!result) {
            setLoading(registerForm, false);
        }
    });

    // Gestion du formulaire de connexion
    loginForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        hideAlert();
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
                showAlert("Vous êtes bien déconnecté(e) !", "success");
                setTimeout(() => {
                    window.location.href = "/"; // Redirige vers l'accueil ou où tu veux
                }, 1000);
            }
        } catch (err) {
            showAlert("Erreur lors de la déconnexion", "danger");
        }
    });
}
