<?php include 'partials/search-covoit.php'; ?>


<div class="container-fluid text-white">
    

    <!-- H1 PRINCIPAL -->
    <section class="text-center mb-3 mt-5">
        <h1 class="fw-bold">Éco-conduite, éco-attitude, EcoRide !</h1>
        <p class="lead">Rejoignez le mouvement du covoiturage responsable</p>
        <div class="mt-1 mb-5 d-none d-lg-inline-block">
            <button class="btn btn-inscription me-2" data-bs-toggle="modal" data-bs-target="#authModal" data-start="register">
                Inscrivez-vous
            </button>
            <a href="/liste-covoiturages" class="btn btn-inscription">Rechercher un trajet</a>
        </div>
    </section>

    <!-- Collage d’images -->
    <div class="image-collage d-none d-lg-flex justify-content-center mb-3">
        <img src="/assets/images/img1.png" class="img1 position-absolute rounded shadow" alt="img1">
        <img src="/assets/images/img2.png" class="img2 position-absolute rounded shadow" alt="img2">
        <img src="/assets/images/img3.png" class="img3 position-absolute rounded shadow" alt="img3">
    </div>
    </div>


    <!-- DESTINATIONS POPULAIRES CENTRÉES -->
    <div class="row justify-content-center ">
        <div class="col-12">
            <div class="container popular-destinations rounded  px-5 py-3 w-100">
                <h3 class="text-center mb-4 mt-3">
                    <i class="bi bi-car-front text-success"></i>
                    Destinations populaires
                    <i class="bi bi-car-front text-success"></i>
                </h3>
                <div class="row g-4 mt-4 mb-5">
                    <?php if (!empty($popularDestinations)): ?>
                        <?php foreach ($popularDestinations as $dest): ?>
                            <div class="col-lg-4 col-md-6">
                                <div class="card shadow-lg h-100 border-0">
                                    <div class="card-header bg-white text-center fw-bold border-0 border-bottom border-success">
                                        <?= htmlspecialchars($dest['arrivee']) ?>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled m-0">
                                            <?php foreach ($dest['departures'] as $d): ?>
                                                <li class="d-flex align-items-center justify-content-between py-2 px-2 rounded mb-2 card-list-item">
                                                    <span>Départ de : <?= htmlspecialchars($d['depart']) ?></span>
                                                    <span class="fw-bold text-success">
                                                        <?php if (!empty($d['min_prix'])): ?>
                                                            <?= (int)ceil($d['min_prix']) ?> crédits
                                                        <?php else: ?>
                                                            —
                                                        <?php endif; ?>
                                                    </span>
                                                    <a class="btn btn-outline-success btn-sm ms-2" href="/liste-covoiturages?depart=<?= urlencode($d['depart']) ?>&arrivee=<?= urlencode($dest['arrivee']) ?>" title="Voir les trajets">
                                                        <i class="bi bi-plus"></i>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-secondary text-center mb-0">Pas encore de destinations populaires. Revenez plus tard.</div>
                        </div>
                    <?php endif; ?>
                </div>

                <h3 class="text-center mb-4 mt-5">
                    <i class="bi bi-car-front text-success"></i>
                    Rechercher un trajet
                    <i class="bi bi-car-front text-success"></i>
                </h3>
                <div class="text-center mt-3 mb-3">
                    <button class="btn btn-inscription" data-bs-toggle="modal" data-bs-target="#searchCovoitModal">Rechercher un trajet</button>
                </div>
            </div>
        </div>
    </div>



    <!-- TEXTE EN BAS -->
    <section class="text-center px-4 mt-5 mb-5">
        <h2 class="fw-bold mb-3">Partageons la route,<br>préservons la planète ensemble</h2>
        <p>
            Envie de voyager autrement ? Avec EcoRide, découvrez la solution de covoiturage 100% éco-responsable pensée pour allier économies et respect de la planète !<br>
            Trouvez votre itinéraire en quelques clics, partagez vos trajets et réduisez votre empreinte carbone tout en rencontrant des personnes partageant les mêmes valeurs.
        </p>
    </section>
</div>