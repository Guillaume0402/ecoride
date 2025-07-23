<div class="modal fade" id="authModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content auth-modal-content">
            <div class="modal-header-custom">
                <div class="form-switcher d-flex justify-content-center gap-5">
                    <button id="showRegister" class="btn btn-link active-tab ">Inscription</button>
                    <button id="showLogin" class="btn btn-link">Connexion</button>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <h3 class="modal-title text-center fw-bold fs-2 mb-4 mt-4" id="authModalLabel">Inscription</h3>

            <!-- Zone d'alerte pour les messages -->
            <div id="authAlert" class="alert d-none mx-3" role="alert"></div>

            <!-- FORM INSCRIPTION -->
            <form id="registerForm" class="auth-form p-0 p-lg-5">
                <div class="mb-3">
                    <label for="username" class="form-label">Pseudo*</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="emailRegister" class="form-label">Email*</label>
                    <input type="email" class="form-control" id="emailRegister" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="passwordRegister" class="form-label">Mot de passe*</label>
                    <input type="password" class="form-control" id="passwordRegister" name="password" required>
                </div>
                <div class="mb-3">
                    <label for="confirmPassword" class="form-label">Confirmer mot de passe*</label>
                    <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-inscription">
                        <span class="btn-text">Inscription</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </div>
            </form>
            <!-- FORM CONNEXION -->
            <form id="loginForm" class="auth-form d-none p-0 p-lg-5">
                <div class="mb-3">
                    <label for="emailLogin" class="form-label">Email*</label>
                    <input type="email" class="form-control" id="emailLogin" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="passwordLogin" class="form-label">Mot de passe*</label>
                    <input type="password" class="form-control" id="passwordLogin" name="password" required>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-inscription">
                        <span class="btn-text">Connexion</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // SOLUTION : Déplacer les fonctions utilitaires en dehors du DOMContentLoaded

    // Fonction pour afficher les messages
    function showAlert(message, type = 'danger') {
        const alertDiv = document.getElementById('authAlert');
        alertDiv.className = `alert alert-${type} mx-3`;
        alertDiv.textContent = message;
        alertDiv.classList.remove('d-none');

        // Auto-masquer après 5 secondes
        setTimeout(() => {
            alertDiv.classList.add('d-none');
        }, 5000);
    }

    // Fonction pour masquer les messages
    function hideAlert() {
        const alertDiv = document.getElementById('authAlert');
        alertDiv.classList.add('d-none');
    }

    // Fonction pour gérer le loading
    function setLoading(form, isLoading) {
        const button = form.querySelector('button[type="submit"]');
        const btnText = button.querySelector('.btn-text');
        const spinner = button.querySelector('.spinner-border');

        if (isLoading) {
            button.disabled = true;
            btnText.classList.add('d-none');
            spinner.classList.remove('d-none');
        } else {
            button.disabled = false;
            btnText.classList.remove('d-none');
            spinner.classList.add('d-none');
        }
    }

    // Fonction pour changer d'onglet (connexion/inscription)
    function setActiveTab(tab) {
        hideAlert(); // Masquer les alertes lors du changement d'onglet

        const loginForm = document.getElementById('loginForm');
        const registerForm = document.getElementById('registerForm');
        const showLogin = document.getElementById('showLogin');
        const showRegister = document.getElementById('showRegister');
        const title = document.getElementById('authModalLabel');

        if (tab === 'login') {
            loginForm.classList.remove('d-none');
            registerForm.classList.add('d-none');
            showLogin.classList.add('active-tab');
            showRegister.classList.remove('active-tab');
            title.innerText = "Connexion";
        } else {
            registerForm.classList.remove('d-none');
            loginForm.classList.add('d-none');
            showRegister.classList.add('active-tab');
            showLogin.classList.remove('active-tab');
            title.innerText = "Inscription";
        }
    }

    // Fonction pour gérer l'authentification (connexion/inscription)
    // Utilise fetch pour appeler les API correspondantes
    // Renvoie une promesse pour gérer les réponses
    async function handleAuth(endpoint, formData) {
        try {
            const response = await fetch(`/api/auth/${endpoint}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (data.success) {
                showAlert(data.message, 'success');

                if (endpoint === 'login') {
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else if (endpoint === 'register') {
                    setTimeout(() => {
                        setActiveTab('login');
                        // Pré-remplir l'email dans le formulaire de connexion
                        document.getElementById('emailLogin').value = formData.email;
                    }, 1500);
                }
            } else {
                showAlert(data.message, 'danger');
            }
        } catch (error) {
            showAlert('Erreur de connexion au serveur', 'danger');
            console.error('Erreur:', error);
        }
    }

    // 🔥 Les événements restent dans le DOMContentLoaded
    document.addEventListener('DOMContentLoaded', () => {
        const authModal = document.getElementById('authModal');
        const showLogin = document.getElementById('showLogin');
        const showRegister = document.getElementById('showRegister');
        const loginForm = document.getElementById('loginForm');
        const registerForm = document.getElementById('registerForm');

        // Gestionnaires pour les boutons de changement d'onglet
        showLogin.addEventListener('click', () => setActiveTab('login'));
        showRegister.addEventListener('click', () => setActiveTab('register'));

        // Gestionnaires pour les formulaires
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            hideAlert();
            setLoading(registerForm, true);

            const formData = new FormData(registerForm);
            const data = Object.fromEntries(formData);

            // Validation côté client
            if (data.password !== data.confirmPassword) {
                showAlert('Les mots de passe ne correspondent pas', 'danger');
                setLoading(registerForm, false);
                return;
            }

            await handleAuth('register', data);
            setLoading(registerForm, false);
        });

        // Gestion du formulaire de connexion
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            hideAlert();
            setLoading(loginForm, true);

            const formData = new FormData(loginForm);
            const data = Object.fromEntries(formData);

            await handleAuth('login', data);
            setLoading(loginForm, false);
        });

        // Gestion de l'ouverture de la modal
        const modal = new bootstrap.Modal(authModal);
        document.querySelectorAll('[data-bs-target="#authModal"]').forEach(button => {
            button.addEventListener('click', () => {
                const start = button.getAttribute('data-start') || 'register';
                setActiveTab(start);
                modal.show();
            });
        });

        // Réinitialiser les formulaires à la fermeture
        authModal.addEventListener('hidden.bs.modal', () => {
            registerForm.reset();
            loginForm.reset();
            hideAlert();
            setLoading(registerForm, false);
            setLoading(loginForm, false);
        });
    });
</script>