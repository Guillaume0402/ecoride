<?php require_once __DIR__ . '/../partials/header.php'; ?>

<div class="container text-center py-5 d-flex flex-column align-items-center justify-content-center flex-fill">
    <div class="content" style="max-width: 600px;">
        <h1 class="display-4 fw-bold mb-3 text-danger">Erreur</h1>
        <p class="fs-5"><?= htmlspecialchars($message ?? 'Une erreur est survenue') ?></p>
        <a href="/" class="btn btn-inscription mt-4">← Retour à l'accueil</a>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>