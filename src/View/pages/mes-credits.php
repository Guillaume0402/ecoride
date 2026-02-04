<?php
if (!isset($_SESSION['user'])) {
    header('Location: /login');
    exit;
}
$user = $_SESSION['user'];
$transactions = $transactions ?? [];
?>
<div class="container py-5">
    <h1 class="mb-4">Mes crédits</h1>
    <div class="card bg-transparent border rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <div class="fw-semibold">Solde actuel</div>
                <div class="fs-5">
                    <span class="badge bg-secondary text-black">
                        <?= isset($user['credits']) ? (int)$user['credits'] : 0 ?>
                    </span>
                </div>
            </div>
            <div>
                <a href="/my-profil" class="btn btn-custom-outline">Retour profil</a>
            </div>
        </div>
        <?php if (empty($transactions)): ?>
            <div class="text-muted">Aucune transaction pour le moment.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm table-striped align-middle table-theme">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Montant</th>
                            <th>Motif</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $tx): ?>
                            <tr>
                                <td class="small text-nowrap"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($tx['created_at'] ?? 'now'))) ?></td>
                                <td>
                                    <span class="badge <?= ($tx['type'] ?? '') === 'credit' ? 'bg-success' : 'bg-danger' ?>">
                                        <?= htmlspecialchars($tx['type'] ?? '') ?>
                                    </span>
                                </td>
                                <td><?= (int)($tx['montant'] ?? 0) ?></td>
                                <td class="small" title="<?= htmlspecialchars($tx['motif'] ?? '') ?>">
                                    <?= htmlspecialchars($tx['motif'] ?? '') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>