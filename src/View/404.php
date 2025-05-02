<div class="page-404 container text-center py-5 d-flex flex-column align-items-center justify-content-center">
    <div class="content" style="max-width: 600px;">
        <img src="<?= asset('images/404.png') ?>" alt="Erreur 404 EcoRide"
            class="img-fluid mb-4 rounded shadow" style="max-width: 300px;" />

        <h1 class="display-4 fw-bold mb-3">404</h1>
        <p class="fs-5">La page que vous cherchez n'existe pas ou a été déplacée.</p>

        <?php if (!empty($message)): ?>
            <p class="fst-italic small text-light-emphasis">
                <?= htmlspecialchars($message) ?>
            </p>
        <?php endif; ?>

        <a href="<?= url('/') ?>" class="btn btn-inscription mt-4">← Retour à l'accueil</a>
    </div>
</div>