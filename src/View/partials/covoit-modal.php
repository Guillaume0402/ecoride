<div class="modal fade" id="createCovoitModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content auth-modal-content">
            <div class="modal-header-custom">
                <h3 class="modal-title text-center fw-bold fs-2 mb-0 w-100">Créer un covoiturage</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <form class="auth-form p-0 p-lg-5">
                <div class="mb-3">
                    <label class="form-label">Ville de départ</label>
                    <input type="text" class="form-control" placeholder="Ex : Paris" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Ville d'arrivée</label>
                    <input type="text" class="form-control" placeholder="Ex : Lyon" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Prix du trajet</label>
                    <input type="number" class="form-control" placeholder="Ex : 25" min="0" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Choix du véhicule</label>
                    <select class="form-select" required>
                        <option value="">Sélectionner</option>
                        <option value="Nissan Micra">Nissan Micra</option>
                        <option value="Renault Zoé">Renault Zoé</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Date du départ</label>
                    <input type="date" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Heure du départ</label>
                    <input type="time" class="form-control" required>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-inscription">Créer le voyage</button>
                </div>
            </form>
        </div>
    </div>
</div>