<div class="modal fade" id="authModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content auth-modal-content">

            <div class="modal-header-custom">
                <div class="form-switcher d-flex justify-content-center gap-5">
                    <button id="showRegister" type="button" class="btn btn-link active-tab">Inscription</button>
                    <button id="showLogin" type="button" class="btn btn-link">Connexion</button>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>

            <h3 class="modal-title text-center fw-bold fs-2 mb-4 mt-4" id="authModalLabel">Inscription</h3>

            <!-- Zone d'alerte -->
            <div id="authAlert" class="alert d-none mx-3" role="alert"></div>

            <!-- FORM INSCRIPTION -->
            <form id="registerForm" class="auth-form p-0 p-lg-5">
                <div class="mb-3">
                    <label for="username" class="form-label">Pseudo*</label>
                    <input type="text" class="form-control" id="username" name="username" required minlength="3" autocomplete="username">
                    <div class="invalid-feedback">Veuillez renseigner un pseudo (3 caractères minimum).</div>
                </div>

                <div class="mb-3">
                    <label for="emailRegister" class="form-label">Email*</label>
                    <input type="email" class="form-control" id="emailRegister" name="email" required inputmode="email" autocomplete="email">
                    <div class="invalid-feedback">Veuillez entrer une adresse email valide.</div>
                </div>

                <div class="mb-3">
                    <label for="passwordRegister" class="form-label">Mot de passe*</label>
                    <div class="input-group">
                        <input
                            type="password"
                            class="form-control"
                            id="passwordRegister"
                            name="password"
                            required
                            minlength="12"
                            autocomplete="new-password"
                        >
                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="passwordRegister" aria-label="Afficher/Masquer le mot de passe">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div class="invalid-feedback">Mot de passe invalide.</div>
                </div>

                <div class="mb-3">
                    <label for="confirmPassword" class="form-label">Confirmer mot de passe*</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required autocomplete="new-password">
                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="confirmPassword" aria-label="Afficher/Masquer le mot de passe">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div class="invalid-feedback">Les mots de passe ne correspondent pas.</div>
                </div>

                <div class="text-center">
                    <input type="hidden" name="csrf" value="<?= \App\Security\Csrf::token() ?>">
                    <button type="submit" class="btn btn-inscription">
                        <span class="btn-text">Inscription</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </form>

            <!-- FORM CONNEXION -->
            <form id="loginForm" class="auth-form d-none p-0 p-lg-5">
                <div class="mb-3">
                    <label for="emailLogin" class="form-label">Email*</label>
                    <input type="email" class="form-control" id="emailLogin" name="email" required inputmode="email" autocomplete="email">
                    <div class="invalid-feedback">Veuillez entrer une adresse email valide.</div>
                </div>

                <div class="mb-3">
                    <label for="passwordLogin" class="form-label">Mot de passe*</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="passwordLogin" name="password" required autocomplete="current-password">
                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="passwordLogin" aria-label="Afficher/Masquer le mot de passe">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div class="invalid-feedback">Veuillez saisir votre mot de passe.</div>
                </div>

                <div class="text-center">
                    <input type="hidden" name="csrf" value="<?= \App\Security\Csrf::token() ?>">
                    <input type="hidden" name="redirect" value="">
                    <button type="submit" class="btn btn-inscription">
                        <span class="btn-text">Connexion</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
