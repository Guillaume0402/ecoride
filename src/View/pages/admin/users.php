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
        <h1 class="mb-4">👥 Gestion des utilisateurs & employés</h1>

        <!-- ✅ Alertes globales succès/erreur -->
        <?php if (!empty($_SESSION['success'])): ?>
            <div class="custom-alert alert-success">
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="custom-alert alert-danger">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Navigation des onglets -->
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link <?= (($_SESSION['active_tab'] ?? '') !== 'utilisateurs') ? 'active' : '' ?>"
                    data-bs-toggle="tab" href="#employes">Employés</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= (($_SESSION['active_tab'] ?? '') === 'utilisateurs') ? 'active' : '' ?>"
                    data-bs-toggle="tab" href="#utilisateurs">Utilisateurs</a>
            </li>
        </ul>
        <!-- Contenus des onglets -->
        <div class="tab-content mt-3">
            <!-- Onglet Employés -->
            <div class="tab-pane fade <?= (($_SESSION['active_tab'] ?? '') !== 'utilisateurs') ? 'show active' : '' ?>" id="employes">
                <button class="btn btn-inscription mb-3 text-white"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseAddEmployee"
                    aria-expanded="false"
                    aria-controls="collapseAddEmployee">
                    <i class="bi bi-plus-circle me-2"></i> Ajouter un employé
                </button>
                <div class="collapse mb-3 <?= !empty($formErrors) ? 'show' : '' ?>" id="collapseAddEmployee">
                    <div class="form-box text-black rounded p-4 w-100">
                        <h4 class="text-center">Ajouter un employé</h4>

                        <!-- Bloc d'erreur -->
                        <?php if (!empty($formErrors)): ?>
                            <div class="custom-alert alert-danger">
                                <?= implode('<br>', array_map('htmlspecialchars', $formErrors)) ?>
                            </div>
                        <?php endif; ?>

                        <form action="/admin/users/create" method="POST">
                            <div class="mb-3">
                                <label for="pseudo" class="form-label">Pseudo</label>
                                <input type="text" class="form-control" id="pseudo" name="pseudo"
                                    value="<?= htmlspecialchars($old['pseudo'] ?? '') ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="passwordRegister" class="form-label">Mot de passe*</label>
                                <input type="password" class="form-control" id="passwordRegisterEmployee" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirmPassword" class="form-label">Confirmer mot de passe*</label>
                                <input type="password" class="form-control" id="confirmPasswordEmployee" name="confirmPassword" required>
                            </div>
                            <div class="mb-3 text-center">
                                <button type="submit" class="btn btn-inscription">Créer l'employé</button>
                            </div>
                        </form>
                    </div>
                </div>


                <table class="table table-bordered table-hover align-middle bg-white text-dark table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Pseudo</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Crédits</th>
                            <th>Créé le</th>
                            <th class="actions-col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($employees)) : ?>
                            <?php foreach ($employees as $employee): ?>
                                <tr>
                                    <td><?= $employee->getId() ?></td>
                                    <td><?= htmlspecialchars($employee->getPseudo()) ?></td>
                                    <td><?= htmlspecialchars($employee->getEmail()) ?></td>
                                    <td><?= ucfirst($employee->getRoleId()) ?></td>
                                    <td><?= $employee->getCredits() ?></td>
                                    <td><?= $employee->getCreatedAt()
                                            ? $employee->getCreatedAt()->format('d/m/Y')
                                            : '' ?></td>
                                    <td class="actions-col-user">
                                        <!-- Supprimer -->
                                        <a href="/admin/users/delete/<?= $employee->getId() ?>"
                                            class="btn btn-sm btn-danger delete-btn"
                                            data-type="employé"
                                            data-action="supprimer">

                                            <i class="bi bi-trash"></i>
                                        </a>

                                        <!-- Activer / Désactiver -->
                                        <form action="/admin/users/toggle/<?= $employee->getId() ?>"
                                            method="POST"
                                            class="d-inline">
                                            <button type="submit"
                                                class="btn btn-sm <?= $employee->getIsActive() ? 'btn-success' : 'btn-warning' ?> toggle-btn"
                                                data-type="employé"
                                                data-action="<?= $employee->getIsActive() ? 'désactiver' : 'activer' ?>">
                                                <i class="bi <?= $employee->getIsActive() ? 'bi-check-circle' : 'bi-x-circle' ?>"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        <?php endif ?>

                    </tbody>
                </table>
            </div>
            <!-- Onglet Utilisateurs -->
            <div class="tab-pane fade <?= (($_SESSION['active_tab'] ?? '') === 'utilisateurs') ? 'show active' : '' ?>" id="utilisateurs">
                <table class="table table-bordered table-hover align-middle bg-white text-dark table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Pseudo</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Crédits</th>
                            <th>Créé le</th>
                            <th class="actions-col-user">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= $user->getId() ?></td>
                                <td><?= htmlspecialchars($user->getPseudo()) ?></td>
                                <td><?= htmlspecialchars($user->getEmail()) ?></td>
                                <td><?= ucfirst($user->getRoleId()) ?></td>
                                <td><?= $user->getCredits() ?></td>
                                <td><?= $user->getCreatedAt() ? $user->getCreatedAt()->format('d/m/Y') : '' ?></td>
                                <td class="actions-col-user">
                                    <a href="/admin/users/delete/<?= $user->getId() ?>" class="btn btn-sm btn-danger delete-btn"
                                        data-type="utilisateur">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
    <!-- Modal de confirmation suppression -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">⚠️ Confirmation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Voulez-vous vraiment supprimer cet élément ?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Supprimer</a>
                </div>
            </div>
        </div>
    </div>

</div>


<?php unset($_SESSION['active_tab']); ?>