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
        $daysInt = 15;
        if (isset($days)) {
            $daysInt = $days;
        }

        // UI buttons classes
        $btn7 = 'btn-outline-success';
        if (isset($ui['btn7'])) {
            $btn7 = $ui['btn7'];
        }
        $btn15 = 'btn-outline-success';
        if (isset($ui['btn15'])) {
            $btn15 = $ui['btn15'];
        }
        $btn30 = 'btn-outline-success';
        if (isset($ui['btn30'])) {
            $btn30 = $ui['btn30'];
        }

        // Charts data (JSON strings expected)
        $labelsR = '[]';
        $valuesR = '[]';
        $labelsC = '[]';
        $valuesC = '[]';
        if (isset($ui) && is_array($ui)) {
            if (isset($ui['labelsR'])) {
                $labelsR = $ui['labelsR'];
            }
            if (isset($ui['valuesR'])) {
                $valuesR = $ui['valuesR'];
            }
            if (isset($ui['labelsC'])) {
                $labelsC = $ui['labelsC'];
            }
            if (isset($ui['valuesC'])) {
                $valuesC = $ui['valuesC'];
            }
        }

        // Summary defaults
        $todayRides = 0;
        $totalRidesWindow = 0;
        $avgRidesPerDayWindowStr = '0,0';
        $totalCreditsWindowStr = '0';
        $avgCreditsPerRideStr = '0,0';
        $avgCreditsPerDayWindowStr = '0,0';
        $bestRideDayLabel = '-';
        $bestRideDayValue = 0;
        $bestCreditDayLabel = '-';
        $bestCreditDayValueStr = '0';
        $usersCount = 0;
        $confirmRateStr = '0,00';
        $totalRidesAll = 0;
        $totalCreditsAllStr = '0';

        if (isset($summaryStrings) && is_array($summaryStrings)) {
            if (isset($summaryStrings['todayRides'])) {
                $todayRides = $summaryStrings['todayRides'];
            }
            if (isset($summaryStrings['totalRidesWindow'])) {
                $totalRidesWindow = $summaryStrings['totalRidesWindow'];
            }
            if (isset($summaryStrings['avgRidesPerDayWindow'])) {
                $avgRidesPerDayWindowStr = $summaryStrings['avgRidesPerDayWindow'];
            }
            if (isset($summaryStrings['totalCreditsWindow'])) {
                $totalCreditsWindowStr = $summaryStrings['totalCreditsWindow'];
            }
            if (isset($summaryStrings['avgCreditsPerRide'])) {
                $avgCreditsPerRideStr = $summaryStrings['avgCreditsPerRide'];
            }
            if (isset($summaryStrings['avgCreditsPerDayWindow'])) {
                $avgCreditsPerDayWindowStr = $summaryStrings['avgCreditsPerDayWindow'];
            }
            if (isset($summaryStrings['bestRideDayLabel'])) {
                $bestRideDayLabel = $summaryStrings['bestRideDayLabel'];
            }
            if (isset($summaryStrings['bestRideDayValue'])) {
                $bestRideDayValue = $summaryStrings['bestRideDayValue'];
            }
            if (isset($summaryStrings['bestCreditDayLabel'])) {
                $bestCreditDayLabel = $summaryStrings['bestCreditDayLabel'];
            }
            if (isset($summaryStrings['bestCreditDayValue'])) {
                $bestCreditDayValueStr = $summaryStrings['bestCreditDayValue'];
            }
            if (isset($summaryStrings['usersCount'])) {
                $usersCount = $summaryStrings['usersCount'];
            }
            if (isset($summaryStrings['confirmRate'])) {
                $confirmRateStr = $summaryStrings['confirmRate'];
            }
            if (isset($summaryStrings['totalRidesAll'])) {
                $totalRidesAll = $summaryStrings['totalRidesAll'];
            }
            if (isset($summaryStrings['totalCreditsAll'])) {
                $totalCreditsAllStr = $summaryStrings['totalCreditsAll'];
            }
        }
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
                            data-labels='<?php echo $labelsR; ?>'
                            data-values='<?php echo $valuesR; ?>'></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Crédits générés – par jour, <?php echo $daysInt; ?>j</h5>
                        <canvas id="chartCredits" height="200"
                            data-labels='<?php echo $labelsC; ?>'
                            data-values='<?php echo $valuesC; ?>'></canvas>
                    </div>
                </div>
            </div>
        </div>


        <div class="mt-5">
            <h5 class="mb-3">Résumé</h5>
            <div class="summary-grid">
                <div class="kpi-card">
                    <div class="kpi-label">🚗 Trajets aujourd'hui</div>
                    <div class="kpi-value"><?php echo $todayRides; ?></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">📆 Total – <?php echo $daysInt; ?>j</div>
                    <div class="kpi-value"><?php echo $totalRidesWindow; ?></div>
                    <div class="kpi-sub">covoiturages</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">📈 Moy./jour – <?php echo $daysInt; ?>j</div>
                    <div class="kpi-value"><?php echo htmlspecialchars($avgRidesPerDayWindowStr); ?></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">💳 Crédits – <?php echo $daysInt; ?>j</div>
                    <div class="kpi-value"><?php echo htmlspecialchars($totalCreditsWindowStr); ?></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">💰 Crédit/trajet</div>
                    <div class="kpi-value"><?php echo htmlspecialchars($avgCreditsPerRideStr); ?></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">💸 Moy. crédits/j – <?php echo (int)$daysInt; ?>j</div>
                    <div class="kpi-value"><?php echo htmlspecialchars($avgCreditsPerDayWindowStr); ?></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">🏆 Meilleur jour – trajets</div>
                    <div class="kpi-value small">
                        <?php echo htmlspecialchars($bestRideDayLabel); ?>
                        <span class="kpi-chip"><?php echo $bestRideDayValue; ?></span>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">🏆 Meilleur jour – crédits</div>
                    <div class="kpi-value small">
                        <?php echo htmlspecialchars($bestCreditDayLabel); ?>
                        <span class="kpi-chip"><?php echo htmlspecialchars($bestCreditDayValueStr); ?></span>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">👥 Utilisateurs</div>
                    <div class="kpi-value"><?php echo $usersCount; ?></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">✅ Taux de confirmation – 30j</div>
                    <div class="kpi-value"><?php echo htmlspecialchars($confirmRateStr); ?>%</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">🧮 Total covoiturages – all-time</div>
                    <div class="kpi-value"><?php echo $totalRidesAll; ?></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">💼 Total crédits – all-time</div>
                    <div class="kpi-value"><?php echo htmlspecialchars($totalCreditsAllStr); ?></div>
                </div>
            </div>
        </div>
    </section>
</div>