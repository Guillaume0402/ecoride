<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="text-center mt-5">
    <h1>Liste des covoiturages</h1>
</div>

<section class="container mt-5 ">
    <div class="col-lg-6 col-12 d-flex justify-content-center mb-4  m-auto">
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
</section>
<section class="filter-wrapper">
    <div class="filter-bg"></div>
    <div class="filter-bar container my-4">
        <div class="row g-3 align-items-center">
            <!-- Filters -->
            <div class="col-md-6">
                <div class="input-group filter-input">
                    <button class="btn btn-outline-light d-flex align-items-center" type="button">
                        <i class="bi bi-funnel-fill me-2"></i> Filters
                    </button>
                </div>
            </div>
            <!-- Sort -->
            <div class="col-md-6 text-md-end">
                <div class="dropdown">
                    <button class="btn btn-outline-light dropdown-toggle" type="button" id="sortDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        Sort by
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="sortDropdown">
                        <li><a class="dropdown-item" href="#">Price</a></li>
                        <li><a class="dropdown-item" href="#">Date</a></li>
                        <li><a class="dropdown-item" href="#">Eco-energy</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>