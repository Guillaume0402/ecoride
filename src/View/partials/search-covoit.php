<div class="modal fade" id="searchCovoitModal" tabindex="-1" aria-labelledby="covoitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="form-box modal-content auth-modal-content text-white">
            <div class="modal-header">
                <h5 class="modal-title" id="covoitModalLabel">Rechercher un covoiturage</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body ">
                <form method="get" action="/liste-covoiturages">
                    <div class="mb-3">
                        <label class="form-label" for="home-depart">Ville de départ :</label>
                        <input
                            id="home-depart"
                            type="text"
                            name="depart"
                            class="form-control"
                            placeholder="Ex : Fleurance"
                            value="<?= htmlspecialchars($_GET['depart'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="home-arrivee">Ville d’arrivée :</label>
                        <input
                            id="home-arrivee"
                            type="text"
                            name="arrivee"
                            class="form-control"
                            placeholder="Ex : Auch"
                            value="<?= htmlspecialchars($_GET['arrivee'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="home-date">Date de départ :</label>
                        <input
                            id="home-date"
                            type="date"
                            name="date"
                            class="form-control"
                            value="<?= htmlspecialchars($_GET['date'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <button type="submit" class="btn btn-inscription d-block m-auto">Rechercher</button>
                </form>

            </div>
        </div>
    </div>
</div>