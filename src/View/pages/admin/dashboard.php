<div class="d-flex" style="min-height: 100vh;">
    <!-- Sidebar -->
    <nav class="bg-dark text-white p-3" style="width: 250px;">
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
                <a class="nav-link text-danger" id="logoutBtn" href="/logout">🚪 Déconnexion</a>
            </li>
        </ul>
    </nav>

    <!-- Main content -->
    <main class="flex-fill p-4">
        <h1 class="mb-4">Bienvenue, <?= htmlspecialchars($_SESSION['user']['pseudo']) ?> 👋</h1>
        <p class="text-muted">Rôle : <?= htmlspecialchars($_SESSION['user']['role_name']) ?></p>

        <hr>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Total utilisateurs</h5>
                        <p class="card-text">📌 <strong>152</strong></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Covoiturages du jour</h5>
                        <p class="card-text">🛺 <strong>23</strong></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Crédits générés</h5>
                        <p class="card-text">💳 <strong>382</strong></p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>