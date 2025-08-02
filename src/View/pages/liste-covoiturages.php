<div class="container">
    <div class="text-center mt-3">
        <h1>Liste des covoiturages</h1>
    </div>
    <section class="container mt-5 ">
        <div class="col-lg-6 col-12 d-flex justify-content-center mb-4  m-auto">
            <div class="form-box rounded p-4 w-100 ">
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
    </section>
    <!-- Filtre & tri -->
    <section class="filter-section m-5">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between flex-wrap filter-bar px-3 py-2 rounded-2">
                <div class="dropdown">
                    <button class="btn btn-filter dropdown-toggle" data-bs-toggle="dropdown">
                        Filtrer
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#">Animaux acceptés</a></li>
                        <li><a class="dropdown-item" href="#">Pas d'animaux</a></li>
                        <li><a class="dropdown-item" href="#">Fumeur</a></li>
                        <li><a class="dropdown-item" href="#">Non-fumeur</a></li>
                    </ul>
                </div>
                <div class="dropdown">
                    <button class="btn btn-sort dropdown-toggle" data-bs-toggle="dropdown">
                        Trier par
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#">Prix</a></li>
                        <li><a class="dropdown-item" href="#">Date</a></li>
                        <li><a class="dropdown-item" href="#">Éco-énergie</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
    <section class="rides-section pb-5">
        <div class="container">
            <div class="row row-cols-1 row-cols-md-2 g-4">

                <!-- Card -->
                <div class="col">
                    <div class="carpool-card d-flex flex-column justify-content-between">
                        <!-- Header : date • prix • badge  /  horloge -->
                        <div class="card-header d-flex justify-content-between mb-3">
                            <div class="card-info">
                                <span class="date">15 juin</span>
                                <span class="sep">•</span>
                                <span class="price">20 €</span>
                                <span class="sep">•</span>
                                <span class="badge-eco ms-2">éco-énergie</span>
                            </div>
                            <div class="card-time">
                                <i class="bi bi-clock-fill"></i>
                                <span>12h45</span>
                            </div>
                        </div>
                        <!-- Body : avatar / détails / bouton -->
                        <div class="card-body d-flex align-items-start justify-content-between flex-wrap mb-3">
                            <img src="assets/images/logo.svg" alt="Avatar" class="avatar rounded-circle">
                            <div class="details flex-grow-1 px-3 m-auto">
                                <h5>Mel gang</h5>
                                <ul>
                                    <li>Animaux accepté</li>
                                    <li>Sans tabac</li>
                                    <li>Sans nourriture</li>
                                    <li>Sans les mains</li>
                                </ul>
                            </div>
                        </div>
                        <!-- Footer : étoiles -->
                        <div class="card-footer">
                            <div class="stars">
                                <span class="star">☆</span>
                                <span class="star">☆</span>
                                <span class="star">☆</span>
                                <span class="star">☆</span>
                                <span class="star">☆</span>
                            </div>
                            <div>
                                <button class="btn btn-inscription">Participer</button>
                            </div>
                        </div>

                    </div>
                </div>
                <!-- Card -->
                <div class="col">
                    <div class="carpool-card d-flex flex-column justify-content-between">
                        <!-- Header : date • prix • badge  /  horloge -->
                        <div class="card-header d-flex justify-content-between mb-3">
                            <div class="card-info">
                                <span class="date">15 juin</span>
                                <span class="sep">•</span>
                                <span class="price">20 €</span>
                                <span class="sep">•</span>
                                <span class="badge-eco">éco-énergie</span>
                            </div>
                            <div class="card-time">
                                <i class="bi bi-clock-fill"></i>
                                <span>12h45</span>
                            </div>
                        </div>
                        <!-- Body : avatar / détails / bouton -->
                        <div class="card-body d-flex align-items-start justify-content-between flex-wrap mb-3">
                            <img src="assets/images/logo.svg" alt="Avatar" class="avatar rounded-circle">
                            <div class="details flex-grow-1 px-3 m-auto">
                                <h5>Mel gang</h5>
                                <ul>
                                    <li>Animaux accepté</li>
                                    <li>Sans tabac</li>
                                    <li>Sans nourriture</li>
                                    <li>Sans les mains</li>
                                </ul>
                            </div>
                        </div>
                        <!-- Footer : étoiles -->
                        <div class="card-footer">
                            <div class="stars">
                                <span class="star">☆</span>
                                <span class="star">☆</span>
                                <span class="star">☆</span>
                                <span class="star">☆</span>
                                <span class="star">☆</span>
                            </div>
                            <div>
                                <button class="btn btn-inscription">Participer</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Card -->
                <div class="col">
                    <div class="carpool-card d-flex flex-column justify-content-between">
                        <!-- Header : date • prix • badge  /  horloge -->
                        <div class="card-header d-flex justify-content-between mb-3">
                            <div class="card-info">
                                <span class="date">15 juin</span>
                                <span class="sep">•</span>
                                <span class="price">20 €</span>
                                <span class="sep">•</span>
                                <span class="badge-eco">éco-énergie</span>
                            </div>
                            <div class="card-time">
                                <i class="bi bi-clock-fill"></i>
                                <span>12h45</span>
                            </div>
                        </div>
                        <!-- Body : avatar / détails / bouton -->
                        <div class="card-body d-flex align-items-start justify-content-between flex-wrap mb-3">
                            <img src="assets/images/logo.svg" alt="Avatar" class="avatar rounded-circle">
                            <div class="details flex-grow-1 px-3 m-auto">
                                <h5>Mel gang</h5>
                                <ul>
                                    <li>Animaux accepté</li>
                                    <li>Sans tabac</li>
                                    <li>Sans nourriture</li>
                                    <li>Sans les mains</li>
                                </ul>
                            </div>
                        </div>
                        <!-- Footer : étoiles -->
                        <div class="card-footer">
                            <div class="stars">
                                <span class="star">☆</span>
                                <span class="star">☆</span>
                                <span class="star">☆</span>
                                <span class="star">☆</span>
                                <span class="star">☆</span>
                            </div>
                            <div>
                                <button class="btn btn-inscription">Participer</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Card -->
                <div class="col">
                    <div class="carpool-card d-flex flex-column justify-content-between">
                        <!-- Header : date • prix • badge  /  horloge -->
                        <div class="card-header d-flex justify-content-between mb-3">
                            <div class="card-info">
                                <span class="date">15 juin</span>
                                <span class="sep">•</span>
                                <span class="price">20 €</span>
                                <span class="sep">•</span>
                                <span class="badge-eco">éco-énergie</span>
                            </div>
                            <div class="card-time">
                                <i class="bi bi-clock-fill"></i>
                                <span>12h45</span>
                            </div>
                        </div>
                        <!-- Body : avatar / détails / bouton -->
                        <div class="card-body d-flex align-items-start justify-content-between flex-wrap mb-3">
                            <img src="assets/images/logo.svg" alt="Avatar" class="avatar rounded-circle">
                            <div class="details flex-grow-1 px-3 m-auto">
                                <h5>Mel gang</h5>
                                <ul>
                                    <li>Animaux accepté</li>
                                    <li>Sans tabac</li>
                                    <li>Sans nourriture</li>
                                    <li>Sans les mains</li>
                                </ul>
                            </div>
                        </div>
                        <!-- Footer : étoiles -->
                        <div class="card-footer">
                            <div class="stars">
                                <span class="star">☆</span>
                                <span class="star">☆</span>
                                <span class="star">☆</span>
                                <span class="star">☆</span>
                                <span class="star">☆</span>
                            </div>
                            <div>
                                <button class="btn btn-inscription">Participer</button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
</div>