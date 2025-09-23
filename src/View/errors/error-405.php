<?php require_once __DIR__ . '/../partials/header.php'; ?>

<div class="page-405 container text-center py-5 d-flex flex-column align-items-center justify-content-center">
    <div class="content" style="max-width: 600px;">
        <h1 class="display-4 fw-bold mb-3 text-danger">405</h1>
        <p class="fs-5">Méthode HTTP non autorisée.</p>
        <?php if (!empty($message)): ?>
            <p class="fst-italic small text-light-emphasis">
                <?= htmlspecialchars($message) ?>
            </p>
        <?php endif; ?>
    <a href="/" class="btn btn-inscription mt-4">← Retour à l'accueil</a>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>