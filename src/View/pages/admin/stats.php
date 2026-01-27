<div class="d-flex" style="min-height: 100vh;">

    <!-- Sidebar -->
    <nav class="admin-side p-3" style="width: 250px;">
        <h4 class="text-white mb-4">Admin</h4>
        <ul class="nav flex-column gap-2">
            <li class="nav-item">
                <a class="nav-link text-white" href="/admin/dashboard">🏠 Tableau de bord</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="/admin/users">👥 Gérer les utilisateurs</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="/admin/stats">📊 Statistiques</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-danger" id="logoutBtn" href="#">🚪 Déconnexion</a>
            </li>
        </ul>
    </nav>

    <section class="container py-5">
        <?php
        $daysInt = (int)($days ?? 15);

        // Boutons (classes déjà préparées par le controller)
        $btn7  = $ui['btn7']  ?? 'btn-outline-success';
        $btn15 = $ui['btn15'] ?? 'btn-outline-success';
        $btn30 = $ui['btn30'] ?? 'btn-outline-success';

        // Data charts (JSON strings)
        $labelsR = $ui['labelsR'] ?? '[]';
        $valuesR = $ui['valuesR'] ?? '[]';
        $labelsC = $ui['labelsC'] ?? '[]';
        $valuesC = $ui['valuesC'] ?? '[]';

        // Résumé formaté (strings prêtes pour la vue)
        $summaryStrings = is_array($summaryStrings ?? null) ? $summaryStrings : [];

        $todayRides               = $summaryStrings['todayRides'] ?? 0;
        $totalRidesWindow         = $summaryStrings['totalRidesWindow'] ?? 0;
        $avgRidesPerDayWindowStr  = $summaryStrings['avgRidesPerDayWindow'] ?? '0,0';
        $totalCreditsWindowStr    = $summaryStrings['totalCreditsWindow'] ?? '0';
        $bestRideDayLabel         = $summaryStrings['bestRideDayLabel'] ?? '-';
        $bestRideDayValue         = $summaryStrings['bestRideDayValue'] ?? 0;
        $usersCount               = $summaryStrings['usersCount'] ?? 0;
        $confirmRateStr           = $summaryStrings['confirmRate'] ?? '0,00';
        ?>

        <h1 class="mb-3">📊 Statistiques de la plateforme</h1>
        <div class="mb-4">
            <div class="btn-group period-switch" role="group" aria-label="Periode">
                <a href="/admin/stats?days=7" class="btn btn-sm <?php echo $btn7; ?>">7j</a>
                <a href="/admin/stats?days=15" class="btn btn-sm <?php echo $btn15; ?>">15j</a>
                <a href="/admin/stats?days=30" class="btn btn-sm <?php echo $btn30; ?>">30j</a>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Évolution des covoiturages – <?php echo $daysInt; ?>j</h5>
                        <canvas id="chartCovoiturages" height="200"
                            data-labels='<?= htmlspecialchars($labelsR, ENT_QUOTES, "UTF-8") ?>'
                            data-values='<?= htmlspecialchars($valuesR, ENT_QUOTES, "UTF-8") ?>'></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Crédits générés – par jour, <?php echo $daysInt; ?>j</h5>
                        <canvas id="chartCredits" height="200"
                            data-labels='<?= htmlspecialchars($labelsC, ENT_QUOTES, "UTF-8") ?>'
                            data-values='<?= htmlspecialchars($valuesC, ENT_QUOTES, "UTF-8") ?>'>
                        </canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-5">
            <h5 class="mb-3">Résumé</h5>

            <div class="summary-grid">
                <div class="kpi-card">
                    <div class="kpi-label">🚗 Trajets aujourd'hui</div>
                    <div class="kpi-value"><?= htmlspecialchars($todayRides) ?></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">📆 Total – <?= htmlspecialchars($daysInt) ?>j</div>
                    <div class="kpi-value"><?= htmlspecialchars($totalRidesWindow) ?></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">📈 Moyenne / jour</div>
                    <div class="kpi-value"><?= htmlspecialchars($avgRidesPerDayWindowStr) ?></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">💳 Crédits générés</div>
                    <div class="kpi-value"><?= htmlspecialchars($totalCreditsWindowStr) ?></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">👥 Utilisateurs</div>
                    <div class="kpi-value"><?= htmlspecialchars($usersCount) ?></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">🏆 Meilleur jour</div>
                    <div class="kpi-value small">
                        <?= htmlspecialchars($bestRideDayLabel) ?>
                        <span class="kpi-chip"><?= htmlspecialchars($bestRideDayValue) ?></span>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">✅ Taux de confirmation</div>
                    <div class="kpi-value"><?= htmlspecialchars($confirmRateStr) ?>%</div>
                </div>
            </div>
        </div>
    </section>
</div>