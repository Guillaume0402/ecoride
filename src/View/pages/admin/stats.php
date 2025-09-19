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
        <?php $daysInt = isset($days) ? (int)$days : 15; ?>
        <h1 class="mb-4 d-flex align-items-center justify-content-between">
            <span>📊 Statistiques de la plateforme</span>
            <div class="btn-group period-switch" role="group" aria-label="Période">
                <a href="/admin/stats?days=7" class="btn btn-sm <?php echo htmlspecialchars(isset($ui['btn7']) ? $ui['btn7'] : 'btn-outline-success'); ?>">7j</a>
                <a href="/admin/stats?days=15" class="btn btn-sm <?php echo htmlspecialchars(isset($ui['btn15']) ? $ui['btn15'] : 'btn-outline-success'); ?>">15j</a>
                <a href="/admin/stats?days=30" class="btn btn-sm <?php echo htmlspecialchars(isset($ui['btn30']) ? $ui['btn30'] : 'btn-outline-success'); ?>">30j</a>
            </div>
        </h1>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Évolution des covoiturages (<?php echo (int)$daysInt; ?>j)</h5>
                        <canvas id="chartCovoiturages" height="200"
                            data-labels='<?php echo isset($ui['labelsR']) ? $ui['labelsR'] : "[]"; ?>'
                            data-values='<?php echo isset($ui['valuesR']) ? $ui['valuesR'] : "[]"; ?>'></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Crédits générés (par jour, <?php echo (int)$daysInt; ?>j)</h5>
                        <canvas id="chartCredits" height="200"
                            data-labels='<?php echo isset($ui['labelsC']) ? $ui['labelsC'] : "[]"; ?>'
                            data-values='<?php echo isset($ui['valuesC']) ? $ui['valuesC'] : "[]"; ?>'></canvas>
                    </div>
                </div>
            </div>
        </div>


        <div class="mt-5">
            <h5 class="mb-3">Résumé</h5>
            <div class="summary-grid">
                <div class="kpi-card">
                    <div class="kpi-label">🚗 Trajets aujourd'hui</div>
                    <div class="kpi-value"><?php echo (int)(isset($summaryStrings['todayRides']) ? $summaryStrings['todayRides'] : 0); ?></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">📆 Total (<?php echo (int)$daysInt; ?>j)</div>
                    <div class="kpi-value"><?php echo (int)(isset($summaryStrings['totalRidesWindow']) ? $summaryStrings['totalRidesWindow'] : 0); ?></div>
                    <div class="kpi-sub">covoiturages</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">📈 Moy./jour (<?php echo (int)$daysInt; ?>j)</div>
                    <div class="kpi-value"><?php echo htmlspecialchars(isset($summaryStrings['avgRidesPerDayWindow']) ? $summaryStrings['avgRidesPerDayWindow'] : '0,0'); ?></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">💳 Crédits (<?php echo (int)$daysInt; ?>j)</div>
                    <div class="kpi-value"><?php echo htmlspecialchars(isset($summaryStrings['totalCreditsWindow']) ? $summaryStrings['totalCreditsWindow'] : '0'); ?></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">💰 Crédit/trajet</div>
                    <div class="kpi-value"><?php echo htmlspecialchars(isset($summaryStrings['avgCreditsPerRide']) ? $summaryStrings['avgCreditsPerRide'] : '0,0'); ?></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">💸 Moy. crédits/j (<?php echo (int)$daysInt; ?>j)</div>
                    <div class="kpi-value"><?php echo htmlspecialchars(isset($summaryStrings['avgCreditsPerDayWindow']) ? $summaryStrings['avgCreditsPerDayWindow'] : '0,0'); ?></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">🏆 Meilleur jour (trajets)</div>
                    <div class="kpi-value small">
                        <?php echo htmlspecialchars(isset($summaryStrings['bestRideDayLabel']) ? $summaryStrings['bestRideDayLabel'] : '-'); ?>
                        <span class="kpi-chip"><?php echo (int)(isset($summaryStrings['bestRideDayValue']) ? $summaryStrings['bestRideDayValue'] : 0); ?></span>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">🏆 Meilleur jour (crédits)</div>
                    <div class="kpi-value small">
                        <?php echo htmlspecialchars(isset($summaryStrings['bestCreditDayLabel']) ? $summaryStrings['bestCreditDayLabel'] : '-'); ?>
                        <span class="kpi-chip"><?php echo htmlspecialchars(isset($summaryStrings['bestCreditDayValue']) ? $summaryStrings['bestCreditDayValue'] : '0'); ?></span>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">👥 Utilisateurs</div>
                    <div class="kpi-value"><?php echo (int)(isset($summaryStrings['usersCount']) ? $summaryStrings['usersCount'] : 0); ?></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">✅ Taux de confirmation (30j)</div>
                    <div class="kpi-value"><?php echo htmlspecialchars(isset($summaryStrings['confirmRate']) ? $summaryStrings['confirmRate'] : '0,00'); ?>%</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">🧮 Total covoiturages (all-time)</div>
                    <div class="kpi-value"><?php echo (int)(isset($summaryStrings['totalRidesAll']) ? $summaryStrings['totalRidesAll'] : 0); ?></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">💼 Total crédits (all-time)</div>
                    <div class="kpi-value"><?php echo htmlspecialchars(isset($summaryStrings['totalCreditsAll']) ? $summaryStrings['totalCreditsAll'] : '0'); ?></div>
                </div>
            </div>
        </div>
    </section>
</div>