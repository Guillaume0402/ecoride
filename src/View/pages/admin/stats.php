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
                <a class="nav-link text-danger" id="logoutBtn" href="#">🚪 Déconnexion</a>
            </li>
        </ul>
    </nav>

    <section class="container py-5">
        <h1 class="mb-4">📊 Statistiques de la plateforme</h1>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Évolution des covoiturages</h5>
                        <canvas id="chartCovoiturages" height="200"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Crédits générés (€/jour)</h5>
                        <canvas id="chartCredits" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5">
            <h5>Résumé</h5>
            <ul>
                <li>Total covoiturages : <strong>137</strong></li>
                <li>Total utilisateurs : <strong>58</strong></li>
                <li>Crédits générés ce mois-ci : <strong>846 €</strong></li>
                <li>Moyenne de trajets par jour : <strong>5</strong></li>
            </ul>
        </div>
    </section>
</div>