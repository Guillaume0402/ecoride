<?php
// Vue: Validation dédiée d'une participation par le passager
// Variables disponibles: $p (array issu de ParticipationRepository::findWithCovoiturageById)
?>
<div class="container py-4">
    <h1>Valider votre voyage</h1>

    <div class="card mt-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="mb-2">Trajet</h5>
                    <p class="mb-1">
                        <strong><?= htmlspecialchars($p['adresse_depart']) ?></strong>
                        <span class="mx-1">→</span>
                        <strong><?= htmlspecialchars($p['adresse_arrivee']) ?></strong>
                    </p>
                    <p class="mb-1 text-muted">Départ: <?= (new DateTime($p['depart']))->format('d/m/Y H\hi') ?></p>
                    <p class="mb-1 text-muted">Conducteur: <?= htmlspecialchars($p['driver_pseudo']) ?></p>
                    <p class="mb-0 text-muted">Crédits: <?= number_format((float)($p['prix'] ?? 0), 2, ',', ' ') ?></p>
                </div>
                <div class="col-md-6">
                    <h5 class="mb-2">Votre avis</h5>
                    <form action="/participations/validate/<?= (int)$p['participation_id'] ?>" method="POST">
                        <input type="hidden" name="csrf" value="<?= \App\Security\Csrf::token() ?>">
                        <div class="mb-3">
                            <label for="rating" class="form-label">Note</label>
                            <select id="rating" name="rating" class="form-select">
                                <option value="">—</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="comment" class="form-label">Avis (optionnel)</label>
                            <textarea id="comment" name="comment" class="form-control" rows="4" placeholder="Partagez votre expérience..."></textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">Valider le trajet</button>
                            <a href="/mes-covoiturages" class="btn btn-outline-secondary">Retour</a>
                        </div>
                    </form>
                    <hr>
                    <div>
                        <button class="btn btn-outline-danger btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#report" aria-expanded="false">Signaler un problème</button>
                        <div class="collapse mt-2" id="report">
                            <form action="/participations/report/<?= (int)$p['participation_id'] ?>" method="POST">
                                <input type="hidden" name="csrf" value="<?= \App\Security\Csrf::token() ?>">
                                <div class="mb-2">
                                    <input type="text" class="form-control" name="reason" placeholder="Raison">
                                </div>
                                <div class="mb-2">
                                    <input type="text" class="form-control" name="comment" placeholder="Commentaire (optionnel)">
                                </div>
                                <button type="submit" class="btn btn-danger">Envoyer</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
