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
            <form id="registerForm" class="auth-form p-0 p-lg-5" novalidate>
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
                        <input type="password" class="form-control" id="passwordRegister" name="password" required minlength="12"
                            pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s])(?!.*\s).+$"
                            autocomplete="new-password" aria-describedby="passwordHelp passwordStrengthText">
                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="passwordRegister" aria-label="Afficher/Masquer le mot de passe">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-outline-secondary toggle-criteria" type="button" aria-label="Afficher l'aide sur le mot de passe" aria-controls="passwordCriteriaList" aria-expanded="false">
                            <i class="bi bi-info-circle"></i>
                        </button>
                    </div>
                    <div id="passwordHelp" class="form-text visually-hidden">Règles de sécurité du mot de passe.</div>
                    <ul id="passwordCriteriaList" class="password-criteria d-none" aria-live="polite" aria-describedby="passwordHelp">
                        <li data-crit="len">Au moins 12 caractères</li>
                        <li data-crit="lower">Contient une minuscule</li>
                        <li data-crit="upper">Contient une majuscule</li>
                        <li data-crit="digit">Contient un chiffre</li>
                        <li data-crit="special">Contient un caractère spécial</li>
                        <li data-crit="space">Ne contient aucun espace</li>
                    </ul>
                    <div class="invalid-feedback">Votre mot de passe ne respecte pas les règles de sécurité.</div>
                    <div class="mt-2" aria-live="polite">
                        <div class="progress" style="height: 6px;">
                            <div id="passwordStrengthBar" class="progress-bar bg-danger" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <small id="passwordStrengthText" class="text-muted">Robustesse : très faible</small>
                    </div>
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
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </div>
            </form>
            <!-- FORM CONNEXION -->
            <form id="loginForm" class="auth-form d-none p-0 p-lg-5" novalidate>
                <div class="mb-3">
                    <label for="emailLogin" class="form-label">Email*</label>
                    <input type="email" class="form-control" id="emailLogin" name="email" required inputmode="email" autocomplete="username">
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
                    <button type="submit" class="btn btn-inscription">
                        <span class="btn-text">Connexion</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>