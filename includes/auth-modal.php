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




            <!-- FORM INSCRIPTION -->
            <form id="registerForm" class="auth-form p-0 p-lg-5">
                <div class="mb-3">
                    <label for="username" class="form-label">Pseudo*</label>
                    <input type="text" class="form-control" id="username" required>
                </div>
                <div class="mb-3">
                    <label for="emailRegister" class="form-label">Email*</label>
                    <input type="email" class="form-control" id="emailRegister" required>
                </div>
                <div class="mb-3">
                    <label for="passwordRegister" class="form-label">Mot de passe*</label>
                    <input type="password" class="form-control" id="passwordRegister" required>
                </div>
                <div class="mb-3">
                    <label for="confirmPassword" class="form-label">Confirmer mot de passe*</label>
                    <input type="password" class="form-control" id="confirmPassword" required>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-inscription">Inscription</button>
                </div>
            </form>

            <!-- FORM CONNEXION -->
            <form id="loginForm" class="auth-form d-none p-0 p-lg-5">
                <div class="mb-3">
                    <label for="emailLogin" class="form-label">Email*</label>
                    <input type="email" class="form-control" id="emailLogin" required>
                </div>
                <div class="mb-3">
                    <label for="passwordLogin" class="form-label">Mot de passe*</label>
                    <input type="password" class="form-control" id="passwordLogin" required>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-inscription">Connexion</button>
                </div>
            </form>
        </div>

    </div>
</div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const authModal = document.getElementById('authModal');
        const showLogin = document.getElementById('showLogin');
        const showRegister = document.getElementById('showRegister');
        const loginForm = document.getElementById('loginForm');
        const registerForm = document.getElementById('registerForm');
        const title = document.getElementById('authModalLabel');

        function setActiveTab(tab) {
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

        showLogin.addEventListener('click', () => setActiveTab('login'));
        showRegister.addEventListener('click', () => setActiveTab('register'));

        const modal = new bootstrap.Modal(authModal);

        document.querySelectorAll('[data-bs-target="#authModal"]').forEach(button => {
            button.addEventListener('click', () => {
                const start = button.getAttribute('data-start') || 'register';
                setActiveTab(start);
                modal.show();
            });
        });
    });
</script>