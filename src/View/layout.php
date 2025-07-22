<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>EcoRide</title>
    <link rel="icon" type="image/svg+xml" href="/assets/images/logo.svg">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
</head>

<body class="page-container d-flex flex-column min-vh-100">

    <?php require_once __DIR__ . '/partials/header.php'; ?>

    <main class="flex-fill">
        <?= $content ?>
    </main>

    <?php require_once __DIR__ . '/partials/footer.php'; ?>
    <?php require_once __DIR__ . '/partials/auth-modal.php'; ?>
    <?php require_once __DIR__ . '/partials/covoit-modal.php'; ?>
    <!-- Un seul script Bootstrap ici -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>