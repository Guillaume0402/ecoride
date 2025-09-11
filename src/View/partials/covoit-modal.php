<div class="modal fade" id="createCovoitModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content auth-modal-content">
            <div class="modal-header-custom">
                <h3 class="modal-title text-center fw-bold fs-2 mb-0 w-100">Créer un covoiturage</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <form class="auth-form p-0 p-lg-5" action="/covoiturages/create" method="POST">
                <input type="hidden" name="csrf" value="<?= \App\Security\Csrf::token() ?>">
                <div class="mb-3">
                    <label class="form-label">Ville de départ</label>
                    <input type="text" class="form-control" name="ville_depart" placeholder="Ex : Paris" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Ville d'arrivée</label>
                    <input type="text" class="form-control" name="ville_arrivee" placeholder="Ex : Lyon" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Prix du trajet</label>
                    <input type="number" class="form-control" name="prix" placeholder="Ex : 25" min="0" step="0.01" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Choix du véhicule</label>
                    <?php if (!empty($userVehicles)): ?>
                        <select class="form-select" name="vehicle_id" id="vehicleSelect" required>
                            <option value="">Sélectionner</option>
                            <?php foreach ($userVehicles as $veh): ?>
                                <option value="<?= htmlspecialchars((string)$veh->getId(), ENT_QUOTES, 'UTF-8') ?>" data-places="<?= (int)$veh->getPlacesDispo() ?>">
                                    <?= htmlspecialchars($veh->getMarque() . ' ' . $veh->getModele() . ' — ' . $veh->getImmatriculation(), ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <div class="alert alert-warning" role="alert">
                            Vous n'avez pas encore ajouté de véhicule. <a class="alert-link" href="/vehicle/create">Ajouter un véhicule</a>
                        </div>
                        <select class="form-select" disabled>
                            <option value="">Aucun véhicule disponible</option>
                        </select>
                    <?php endif; ?>
                </div>
                <?php if (!empty($userVehicles)): ?>
                <div class="mb-3">
                    <label class="form-label">Places disponibles pour ce trajet</label>
                    <input type="number" class="form-control" name="places" id="placesInput" min="1" step="1" placeholder="Ex : 3" required>
                    <small class="form-text">Limité au nombre de places du véhicule sélectionné.</small>
                </div>
                <?php endif; ?>
                <div class="mb-3">
                    <label class="form-label">Date du départ</label>
                    <input type="date" class="form-control" name="date" min="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Heure du départ</label>
                    <input type="time" class="form-control" name="time" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Heure d'arrivée</label>
                    <input type="time" class="form-control" name="time_arrivee" required>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-inscription" <?= empty($userVehicles) ? 'disabled' : '' ?>>Créer le voyage</button>
                </div>
            </form>
            <?php if (!empty($userVehicles)): ?>
            <script>
            (function(){
                const sel = document.getElementById('vehicleSelect');
                const places = document.getElementById('placesInput');
                const dateInput = document.querySelector('#createCovoitModal input[name="date"]');
                const timeInput = document.querySelector('#createCovoitModal input[name="time"]');
                const timeArrInput = document.querySelector('#createCovoitModal input[name="time_arrivee"]');
                function syncMax(){
                    const opt = sel?.selectedOptions?.[0];
                    if (!opt || !places) return;
                    const max = parseInt(opt.dataset.places || '1', 10) || 1;
                    places.max = String(max);
                    if (places.value) {
                        const v = parseInt(places.value, 10) || 1;
                        if (v > max) places.value = String(max);
                    }
                }
                function pad(n){ return String(n).padStart(2,'0'); }
                function syncTimeMin(){
                    if (!dateInput || !timeInput) return;
                    const today = new Date();
                    const selDate = new Date(dateInput.value + 'T00:00:00');
                    if (isNaN(selDate.getTime())) return;
                    // Si la date sélectionnée est aujourd’hui, min = heure courante; sinon pas de min
                    const isToday = dateInput.value === today.toISOString().slice(0,10);
                    if (isToday) {
                        const hh = pad(today.getHours());
                        const mm = pad(today.getMinutes());
                        timeInput.min = `${hh}:${mm}`;
                    } else {
                        timeInput.removeAttribute('min');
                    }
                    // Arrivée doit être > départ
                    if (timeInput.value && timeArrInput) {
                        // fixe min de l’arrivée à l’heure de départ (ou +1 minute pour être >)
                        const [h,m] = timeInput.value.split(':').map(Number);
                        const d = new Date(); d.setHours(h||0,m||0,0,0);
                        d.setMinutes(d.getMinutes()+1);
                        timeArrInput.min = `${pad(d.getHours())}:${pad(d.getMinutes())}`;
                        if (timeArrInput.value && timeArrInput.value < timeArrInput.min) {
                            timeArrInput.value = timeArrInput.min;
                        }
                    }
                }
                sel?.addEventListener('change', syncMax);
                syncMax();
                dateInput?.addEventListener('change', syncTimeMin);
                timeInput?.addEventListener('change', syncTimeMin);
                syncTimeMin();
            })();
            </script>
            <?php endif; ?>
        </div>
    </div>
</div>