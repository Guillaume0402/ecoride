<?php foreach (\App\Service\Flash::pull() as $f): ?>
  <div class="custom-alert alert-<?= htmlspecialchars($f['type']) ?> auto-dismiss fade-in" data-banner role="alert">
    <button type="button" class="btn-close" aria-label="Close"></button>
    <div class="content"><?= htmlspecialchars($f['message']) ?></div>
  </div>
<?php endforeach; ?>
