<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="container py-5 d-flex flex-column align-items-center justify-content-center">
    <h2 class="text-primary mb-4 fw-semibold">Création covoiturage</h2>
    <div class="card shadow-lg border-0 rounded-4 p-4" style="max-width: 400px; width: 100%; background: linear-gradient(135deg, #137a03 0%, #128208 31%, #3abb34 58%, #129717 70%);">
        <div class="d-flex align-items-center mb-3">
            <img src="<?= asset('images/logo.svg') ?>" alt="Logo EcoRide" style="width: 40px; height: 40px;">
            <span class="logo-title ms-2 text-white fs-4 fw-bold">Ecoride</span>
        </div>
        <h3 class="text-white fw-bold text-center mb-4">Voyage</h3>
        <?php if (!empty($message)): ?>
            <div class="alert alert-success text-center py-2"> <?= htmlspecialchars($message) ?> </div>
        <?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label class="form-label text-white">Ville de départ</label>
                <input type="text" class="form-control" name="ville_depart" required>
            </div>
            <div class="mb-3">
                <label class="form-label text-white">Ville d'arrivée</label>
                <input type="text" class="form-control" name="ville_arrivee" required>
            </div>
            <div class="mb-3">
                <label class="form-label text-white">Prix du trajet</label>
                <input type="number" class="form-control" name="prix" min="0" required>
            </div>
            <div class="mb-3">
                <label class="form-label text-white">Choix du véhicule</label>
                <select class="form-select" name="vehicule" required>
                    <option value="">Sélectionner</option>
                    <option value="Nissan Micra">Nissan Micra</option>
                    <option value="Renault Zoé">Renault Zoé</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label text-white">Date du départ</label>
                <input type="date" class="form-control" name="date_depart" required>
            </div>
            <div class="mb-3">
                <label class="form-label text-white">Heure du départ</label>
                <input type="time" class="form-control" name="heure_depart" required>
            </div>
            <div class="text-center">
                <button type="submit" class="btn btn-inscription">Créer le voyage</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>