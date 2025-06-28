<?php require_once __DIR__ . '/partials/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-lg border-0 rounded-4 p-4 mb-5" style="background:rgba(0,0,0,0.10);backdrop-filter:blur(2px);">
                <div class="row g-0 align-items-center">
                    <div class="col-md-4 text-center text-md-start mb-4 mb-md-0">
                        <div class="d-flex flex-column align-items-center align-items-md-start gap-2">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="text-white fs-5">
                                    <?php for ($i = 0; $i < 5; $i++): ?><i class="bi bi-star-fill text-warning"></i><?php endfor; ?>
                                </span>
                                <span class="text-white ms-2">(24)</span>
                            </div>
                            <h2 class="fw-bold text-white mb-2">Mel Gang</h2>
                            <img src="<?= asset('images/télé1.jpeg') ?>" alt="Avatar" class="rounded-circle bg-white mb-2" style="width:70px;height:70px;object-fit:cover;">
                            <ul class="list-unstyled text-white small mb-3">
                                <li>Animaux accepté</li>
                                <li>Sans tabac</li>
                                <li>Sans nourriture</li>
                                <li>Sans les mains</li>
                            </ul>
                            <a href="#" class="btn btn-inscription px-4">Historique</a>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="row g-3 text-white">
                            <div class="col-6 col-lg-4">
                                <div class="fw-semibold">Date d'inscription</div>
                                <div class="small">12-06-2026</div>
                            </div>
                            <div class="col-6 col-lg-4">
                                <div class="fw-semibold">Chauffeur</div>
                                <div class="small">Oui</div>
                            </div>
                            <div class="col-6 col-lg-4">
                                <div class="fw-semibold">Passager</div>
                                <div class="small">Non</div>
                            </div>
                            <div class="col-6 col-lg-4">
                                <div class="fw-semibold">Véhicules 1</div>
                                <div class="small">Nissan Micra</div>
                            </div>
                            <div class="col-6 col-lg-4">
                                <div class="fw-semibold">Type énergie</div>
                                <div class="small">Essence</div>
                            </div>
                            <div class="col-6 col-lg-4">
                                <div class="fw-semibold">Immatriculation</div>
                                <div class="small">Nissan Micra</div>
                            </div>
                            <div class="col-6 col-lg-4">
                                <div class="fw-semibold">Véhicule 2</div>
                                <div class="small">Renault Zoé</div>
                            </div>
                            <div class="col-6 col-lg-4">
                                <div class="fw-semibold">Type énergie</div>
                                <div class="small">Electrique</div>
                            </div>
                            <div class="col-6 col-lg-4">
                                <div class="fw-semibold">Immatriculation</div>
                                <div class="small">Renault Zoé</div>
                            </div>
                            <div class="col-6 col-lg-4">
                                <div class="fw-semibold">Véhicules</div>
                                <div class="small">Full name</div>
                            </div>
                            <div class="col-6 col-lg-4">
                                <div class="fw-semibold">Car</div>
                                <div class="small">In-person</div>
                            </div>
                            <div class="col-6 col-lg-4">
                                <div class="fw-semibold">Véhicules</div>
                                <div class="small">Full name</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h2 class="text-center text-white mb-5">Les avis des voyageurs</h2>
    <div class="row justify-content-center g-4">
        <?php for ($i = 0; $i < 3; $i++): ?>
            <div class="col-md-4">
                <div class="card shadow border-0 rounded-4 p-4 h-100" style="background:rgba(0,0,0,0.10);backdrop-filter:blur(2px);">
                    <div class="mb-2">
                        <?php for ($j = 0; $j < 5; $j++): ?><i class="bi bi-star-fill text-warning"></i><?php endfor; ?>
                    </div>
                    <blockquote class="blockquote text-white mb-3">
                        <p class="mb-0">"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse varius enim in eros elementum tristique. Duis cursus, mi quis viverra ornare."</p>
                    </blockquote>
                    <div class="d-flex align-items-center gap-2 mt-3">
                        <img src="<?= asset('images/404.png') ?>" alt="Avatar" class="rounded-circle bg-white" style="width:40px;height:40px;object-fit:cover;">
                        <div>
                            <div class="fw-semibold text-white">Name Surname</div>
                            <div class="small text-white-50">Position, Company name</div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endfor; ?>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>