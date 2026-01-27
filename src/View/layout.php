<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <?php
    // Données meta dynamiques avec valeurs par défaut
    $appEnv = $_ENV['APP_ENV'] ?? 'prod';
    $siteName = 'EcoRide';
    $defaultTitle = isset($pageTitle) && $pageTitle ? ($pageTitle . ' • ' . $siteName) : $siteName;
    $metaTitle = $metaTitle ?? $defaultTitle;
    $metaDescription = $metaDescription ?? 'EcoRide - Covoiturage simple et durable pour vos trajets quotidiens. Rejoignez une communauté responsable et économisez du temps, de l’argent et du CO₂.';
    // Image de partage par défaut (png existant dans public/assets/images)
    $metaImage = $metaImage ?? (SITE_URL . 'assets/images/Avatar.png');
    $canonical = $canonical ?? (SITE_URL . ltrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '', '/'));
    $noindex = isset($noindex) ? (bool)$noindex : ($appEnv !== 'prod');
    ?>
    <title><?= htmlspecialchars($metaTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="canonical" href="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="robots" content="<?= $noindex ? 'noindex,nofollow,noarchive' : 'index,follow' ?>">
    <meta name="theme-color" content="#0a7c66">
    <link rel="icon" type="image/svg+xml" href="/assets/images/logo.svg">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/images/Avatar.png">
    <link rel="manifest" href="/site.webmanifest">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:site_name" content="<?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:title" content="<?= htmlspecialchars($metaTitle, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:url" content="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:image" content="<?= htmlspecialchars($metaImage, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:image:alt" content="Logo EcoRide">
    <meta property="og:image:type" content="image/png">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($metaTitle, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($metaImage, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <script>
        // Init thème avant paint: respecte la préférence stockée; défaut = dark si aucune préférence
        (function initTheme() {
            try {
                var stored = localStorage.getItem('theme');
                var html = document.documentElement;
                var body = document.body;

                if (stored === 'alt') {
                    html.classList.add('theme-alt');
                    if (body) body.classList.add('theme-alt');
                } else if (stored === 'default') {
                    html.classList.remove('theme-alt');
                    if (body) body.classList.remove('theme-alt');
                } else {
                    // Aucune préférence: appliquer dark par défaut et enregistrer
                    html.classList.add('theme-alt');
                    if (body) body.classList.add('theme-alt');
                    localStorage.setItem('theme', 'alt');
                }
            } catch (e) {
                /* no-op */
            }
        })();
    </script>
    <script type="module" src="/js/main.js" ></script>


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