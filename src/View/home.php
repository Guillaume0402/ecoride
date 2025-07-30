<div class="container-fluid text-white">
    <div id="globalAlert" class="alert d-none"></div>

    <!-- H1 PRINCIPAL -->
    <section class="text-center mb-3 mt-5">
        <h1 class="fw-bold">Éco-conduite, éco-attitude, EcoRide !</h1>
        <p class="lead">Rejoignez le mouvement du covoiturage responsable</p>
        <div class="mt-1 mb-3 d-none d-lg-inline-block">
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
        <
            </div>
    </div>


    <!-- DESTINATIONS POPULAIRES CENTRÉES -->
    <div class="row justify-content-center mb-5 mt-5">
        <div class="col">
            <div class="container-fluid popular-destinations p-4 rounded shadow-sm w-100">
                <h3 class="text-center mb-4">
                    <i class="bi bi-car-front text-success"></i>
                    Les destinations les plus populaires
                    <i class="bi bi-car-front text-success"></i>
                </h3>
                <div class="row g-3 mt-5 mb-5">

                    <!-- PARIS -->
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="destination-card p-3 rounded">
                            <h5 class="text-center">Paris</h5>
                            <hr>
                            <ul class="list-unstyled">
                                <li>Depuis > Lille – <span class="credits">25 crédits</span> <button class="btn btn-sm btn-outline-success float-end">+</button></li>
                                <li>Depuis > Lyon – <span class="credits">48 crédits</span> <button class="btn btn-sm btn-outline-success float-end">+</button></li>
                                <li>Depuis > Rennes – <span class="credits">35 crédits</span> <button class="btn btn-sm btn-outline-success float-end">+</button></li>
                                <li>Depuis > Rouen – <span class="credits">19 crédits</span> <button class="btn btn-sm btn-outline-success float-end">+</button></li>
                            </ul>
                        </div>
                    </div>

                    <!-- LYON -->
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="destination-card p-3 rounded">
                            <h5 class="text-center">Lyon</h5>
                            <hr>
                            <ul class="list-unstyled">
                                <li>Depuis > Paris – <span class="credits">53 crédits</span> <button class="btn btn-sm btn-outline-success float-end">+</button></li>
                                <li>Depuis > St-Étienne – <span class="credits">12 crédits</span> <button class="btn btn-sm btn-outline-success float-end">+</button></li>
                                <li>Depuis > Grenoble – <span class="credits">21 crédits</span> <button class="btn btn-sm btn-outline-success float-end">+</button></li>
                                <li>Depuis > Marseille – <span class="credits">30 crédits</span> <button class="btn btn-sm btn-outline-success float-end">+</button></li>
                            </ul>
                        </div>
                    </div>

                    <!-- RENNES -->
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="destination-card p-3 rounded">
                            <h5 class="text-center">Rennes</h5>
                            <hr>
                            <ul class="list-unstyled">
                                <li>Depuis > Nantes – <span class="credits">20 crédits</span> <button class="btn btn-sm btn-outline-success float-end">+</button></li>
                                <li>Depuis > Caen – <span class="credits">29 crédits</span> <button class="btn btn-sm btn-outline-success float-end">+</button></li>
                                <li>Depuis > Paris – <span class="credits">45 crédits</span> <button class="btn btn-sm btn-outline-success float-end">+</button></li>
                                <li>Depuis > Bordeaux – <span class="credits">40 crédits</span> <button class="btn btn-sm btn-outline-success float-end">+</button></li>
                            </ul>
                        </div>
                    </div>

                    
                    <h3 class="text-center mb-4 mt-5">
                        <i class="bi bi-car-front text-success"></i>
                        Rechercher un trajet
                        <i class="bi bi-car-front text-success"></i>
                    </h3>
                    <div class="text-center mt-3">
                        <button class="btn btn-inscription">Rechercher un trajet</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--  FORMULAIRE CENTRÉ 
    <div class="row justify-content-center mb-4">
        <div class="col-lg-6 col-md-8">
            <div class="form-box text-white rounded p-4 shadow">
                <form>
                    <div class="mb-3">
                        <label class="form-label text-dark">Ville de départ :</label>
                        <input type="text" class="form-control" placeholder="Ex : Fleurance">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-dark">Ville d’arrivée :</label>
                        <input type="text" class="form-control" placeholder="Ex : Auch">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-dark">Date de départ :</label>
                        <input type="date" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-inscription d-block m-auto">Rechercher</button>
                </form>
            </div>
        </div>
    </div>  -->


    <!-- TEXTE EN BAS -->
    <section class="text-center px-4 mt-5 mb-5">
        <h2 class="fw-bold mb-3">Partageons la route,<br>préservons la planète ensemble</h2>
        <p>
            Envie de voyager autrement ? Avec EcoRide, découvrez la solution de covoiturage 100% éco-responsable pensée pour allier économies et respect de la planète !<br>
            Trouvez votre itinéraire en quelques clics, partagez vos trajets et réduisez votre empreinte carbone tout en rencontrant des personnes partageant les mêmes valeurs.
        </p>
    </section>
</div>