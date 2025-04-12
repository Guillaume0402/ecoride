<?php include __DIR__ . '/../../includes/header.php'; ?>


<div class="container-fluid eco-main py-5 text-white">
    <div class="container-fluid">

        <!-- H1 PRINCIPAL + SOUS-TITRE + BOUTONS -->
        <section class="text-center mb-4">
            <h1 class="fw-bold">Éco-conduite, éco-attitude, EcoRide !</h1>
            <p class="lead">Rejoignez le mouvement du covoiturage responsable</p>
            <div class="mt-3 d-none d-lg-inline-block">
                <a href="#" class="btn btn-inscription me-2">Inscrivez-vous</a>
                <a href="#" class="btn btn-custom-outline">Rechercher un trajet</a>
            </div>
        </section>

        <!-- Collage d’images desktop uniquement -->
        <div class="image-collage d-none d-lg-flex position-relative justify-content-center mb-5">
            <img src="<?= url('assets/images/img1.png') ?>" class="img1 position-absolute rounded shadow" alt="img1">
            <img src="<?= url('assets/images/img2.png') ?>" class="img2 position-absolute rounded shadow" alt="img2">
            <img src="<?= url('assets/images/img3.png') ?>" class="img3 position-absolute rounded shadow" alt="img3">
        </div>

        <!-- Grille responsive texte + formulaire -->
        <div class="row align-items-center flex-column-reverse flex-lg-row">

            <!-- COL GAUCHE : TEXTE + IMAGES MOBILES + BOUTONS -->
            <div class="col-lg-6 col-12 text-center px-4 mb-4">
                <h2 class="fw-bold mb-3">Partageons la route,<br>préservons la planète ensemble</h2>
                <p>
                    Envie de voyager autrement ? Avec EcoRide, découvrez la solution de covoiturage 100% éco-responsable pensée pour allier économies et respect de la planète !<br>
                    Trouvez votre itinéraire en quelques clics, partagez vos trajets et réduisez votre empreinte carbone tout en rencontrant des personnes partageant les mêmes valeurs.
                </p>

                <!-- Image désert (mobile uniquement) -->
                <div class="image-mobile d-lg-none my-4">
                    <img src="../public/assets/images/img1.png" class="img-fluid rounded shadow" alt="Route désert">
                </div>

                <!-- Boutons (mobile uniquement) -->
                <div class="d-lg-none mb-4">
                    <a href="#" class="btn btn-inscription me-2">Inscrivez-vous</a>
                    <a href="#" class="btn btn-custom-outline">Rechercher un trajet</a>
                </div>

                <!-- Image forêt (mobile uniquement) -->
                <div class="image-mobile d-lg-none my-4">
                    <img src="../public/assets/images/img2.png" class="img-fluid rounded shadow" alt="Route verdoyante">
                </div>
            </div>

            <!-- COL DROITE : FORMULAIRE -->
            <div class="col-lg-6 col-12 d-flex justify-content-center mb-4 ">
                <div class="form-box text-white rounded p-4 w-100 ">
                    <form>
                        <div class="mb-3">
                            <label class="form-label">Ville de départ :</label>
                            <input type="text" class="form-control" placeholder="Ex : Fleurance">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ville d’arrivée :</label>
                            <input type="text" class="form-control" placeholder="Ex : Auch">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date de départ :</label>
                            <input type="date" class="form-control" placeholder="05-11-2026">
                        </div>
                        <button type="submit" class="btn btn-inscription fw-semibold d-block m-auto">Rechercher</button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>


<?php include __DIR__ . '/../../includes/footer.php'; ?>