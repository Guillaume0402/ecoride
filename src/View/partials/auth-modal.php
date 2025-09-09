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
                    <input type="hidden" name="csrf" value="<?= \App\Security\Csrf::token() ?>">
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
                    <input type="hidden" name="csrf" value="<?= \App\Security\Csrf::token() ?>">
                    <button type="submit" class="btn btn-inscription">
                        <span class="btn-text">Connexion</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>