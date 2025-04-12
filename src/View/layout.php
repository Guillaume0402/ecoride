<?php
use App\Router;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>EcoRide</title>
    <link rel="stylesheet" href="<?= url('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
</head>
<body class="page-container d-flex flex-column min-vh-100">
    <?php require_once __DIR__ . '/../../includes/header.php'; ?>

    <main>
        <?= $content ?>
    </main>

    <?php require_once __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>
