<?php foreach (\App\Service\Flash::all() as $f): ?>
  <div class="alert alert-<?= htmlspecialchars($f['type']) ?> alert-dismissible fade show my-2" role="alert">
    <?= htmlspecialchars($f['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endforeach; ?>
