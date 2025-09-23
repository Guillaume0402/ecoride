<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>EcoRide</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="/assets/images/logo.svg">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <script>
        // Forcer le thème sombre par défaut avant le paint, sauf si l'utilisateur a déjà une préférence
        (function() {
            try {
                var stored = localStorage.getItem('theme');
                if (stored === 'alt') {
                    document.documentElement.classList.add('js'); // hint CSS optionnel
                    document.body && document.body.classList.add('theme-alt');
                } else if (stored === null || stored === '' || stored === 'default') {
                    // Par défaut: dark (theme-alt)
                    document.body && document.body.classList.add('theme-alt');
                    localStorage.setItem('theme', 'alt');
                }
            } catch (e) {
                /* ignore */ }
        })();
    </script>
    <script type="module" src="/js/main.js" defer></script>


</head>

<body class="page-container d-flex flex-column min-vh-100" data-has-vehicle="<?= !empty($hasVehicle) ? '1' : '0' ?>">


    <?php require_once __DIR__ . '/partials/header.php'; ?>


    <div id="alerts">
        <?php include __DIR__ . '/partials/flash.php'; ?>
    </div>

    <main class="flex-fill">
        <?= $content ?>
    </main>

    <?php require_once __DIR__ . '/partials/footer.php'; ?>
    <?php require_once __DIR__ . '/partials/auth-modal.php'; ?>
    <?php require_once __DIR__ . '/partials/covoit-modal.php'; ?>

</body>

</html>