<?php require_once __DIR__ . '/../partials/header.php'; ?>

<div class="container text-center py-5 page-500 d-flex flex-column justify-content-center align-items-center">
    <img src="/assets/images/500.png ?>" alt="Erreur 500 EcoRide" class="img-fluid mb-4 rounded shadow" style="max-width: 300px;" />


    <h1 class="display-4 text-danger fw-bold mb-3">500</h1>
    <p class="fs-5">Oups ! Une erreur interne est survenue. Nos équipes s’en occupent 🌱</p>

    <?php if (!empty($message)) : ?>
        <p class="fst-italic small text-light-emphasis"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <a href="/" class="btn btn-inscription mt-4">← Retour à l'accueil</a>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>