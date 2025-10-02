<?php
// Vue: Formulaire de test d'envoi d'e-mail en dev
?>

<div class="container my-4">
  <h1 class="h3 mb-3">Test d’envoi d’e‑mail (dev)</h1>

  <?php $flashes = \App\Service\Flash::pull(); if (!empty($flashes)): ?>
    <div id="alerts">
      <?php foreach ($flashes as $f): ?>
        <div class="alert custom-alert alert-<?= htmlspecialchars($f['type'] ?? 'info') ?>" role="alert">
          <div class="d-flex align-items-center justify-content-between">
            <div><?= $f['message'] ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form action="/mail/test" method="post" class="card p-3">
    <div class="mb-3">
      <label for="to" class="form-label">Destinataire</label>
      <input type="email" class="form-control" id="to" name="to" placeholder="destinataire@example.com" required>
    </div>
    <div class="mb-3">
      <label for="subject" class="form-label">Sujet</label>
      <input type="text" class="form-control" id="subject" name="subject" value="EcoRide: Test SMTP">
    </div>
    <div class="mb-3">
      <label for="body" class="form-label">Contenu (HTML)</label>
      <textarea class="form-control" id="body" name="body" rows="6"><p>Ceci est un e‑mail de test envoyé par EcoRide (dev).</p><p>Date: <?= date('Y-m-d H:i:s') ?></p></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Envoyer</button>
  </form>

  <p class="text-muted mt-3">
    En dev sans SMTP, l’e‑mail est journalisé dans <code>/tmp/ecoride-mail.log</code> dans le conteneur web.
  </p>
</div>
