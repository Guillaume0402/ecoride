<?php foreach (\App\Service\Flash::all() as $f): ?>
  <div class="custom-alert alert-<?= htmlspecialchars($f['type']) ?> fade-in" data-banner>
    <?= htmlspecialchars($f['message']) ?>
  </div>
<?php endforeach; ?>

