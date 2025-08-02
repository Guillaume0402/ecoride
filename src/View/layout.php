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

</head>

<body class="page-container d-flex flex-column min-vh-100">
    <div id="globalAlert" class="d-none"></div>


    <?php require_once __DIR__ . '/partials/header.php'; ?>

    <main class="flex-fill">
        <?= $content ?>
    </main>

    <?php require_once __DIR__ . '/partials/footer.php'; ?>
    <?php require_once __DIR__ . '/partials/auth-modal.php'; ?>
    <?php require_once __DIR__ . '/partials/covoit-modal.php'; ?>



</body>
<?php if (isset($isAdminPage) && $isAdminPage): ?>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const chartCovoiturages = document.getElementById('chartCovoiturages');
            const chartCredits = document.getElementById('chartCredits');

            if (chartCovoiturages) {
                new Chart(chartCovoiturages, {
                    type: 'bar',
                    data: {
                        labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
                        datasets: [{
                            label: 'Covoiturages',
                            data: [3, 5, 7, 6, 4, 2, 1],
                            backgroundColor: 'rgba(75, 192, 192, 0.6)',
                            borderRadius: 6
                        }]
                    }
                });
            }
            if (chartCredits) {
                new Chart(chartCredits, {
                    type: 'line',
                    data: {
                        labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
                        datasets: [{
                            label: 'Crédits (€/jour)',
                            data: [40, 55, 60, 38, 80, 20, 15],
                            borderColor: 'rgba(255, 99, 132, 0.8)',
                            fill: false,
                            tension: 0.3
                        }]
                    }
                });
            }
        });
    </script>
<?php endif; ?>


</body>

</html>