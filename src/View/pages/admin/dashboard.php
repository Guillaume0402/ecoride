<div class="d-flex" style="min-height: 100vh;">
    <!-- Sidebar -->
    <nav class="p-3 admin-side" style="width: 250px;">
        <h4 class="mb-4">Admin</h4>
        <ul class="nav flex-column gap-2">
            <li class="nav-item">
                <a class="nav-link " href="/admin">🏠 Tableau de bord</a>
            </li>
            <li class="nav-item">
                <a class="nav-link " href="/admin/users">👥 Gérer les utilisateurs</a>
            </li>
            <li class="nav-item">
                <a class="nav-link " href="/admin/stats">📊 Statistiques</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-danger" id="logoutBtn" href="/logout">🚪 Déconnexion</a>
            </li>
        </ul>
    </nav>

    <!-- Main content -->
    <section class="flex-fill p-4">
        <h1 class="mb-4">Bienvenue, <?= htmlspecialchars($_SESSION['user']['pseudo']) ?> 👋</h1>


        <div class="row g-4">
            <div class="col-md-4">
                <div class="card dash shadow-sm">
                    <div class="card-body ">
                        <h4 class="card-title">Total utilisateurs</h4>
                        <p class="card-text">📌 <strong><?= (int)($kpi_users ?? 0) ?></strong></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card dash shadow-sm">
                    <div class="card-body ">
                        <h4 class="card-title">Covoiturages du jour</h4>
                        <p class="card-text">🛺 <strong><?= (int)($kpi_rides_today ?? 0) ?></strong></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card dash shadow-sm">
                    <div class="card-body ">
                        <h4 class="card-title">Crédits générés (30j)</h4>
                        <p class="card-text">💳 <strong><?= number_format((float)($kpi_credits_30d ?? 0), 0, ',', ' ') ?></strong></p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>