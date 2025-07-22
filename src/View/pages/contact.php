<?php require_once __DIR__ . '/partials/header.php'; ?>

<section class="contact-section d-flex flex-column align-items-center justify-content-center">
    <h1 class="contact-title mb-5 mt-5">Contactez-nous</h1>
    <div class="contact-card-wrapper w-100 d-flex justify-content-center px-3 mb-5">
        
            <form class="form-box">
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" id="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" class="form-control" required>
                </div>
                <div class="mb-4">
                    <label for="message" class="form-label">Message</label>
                    <textarea id="message" rows="5" class="form-control" required></textarea>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-inscription">Envoyer</button>
                </div>
            </form>
        </div>
    </div>
</section>



<?php require_once __DIR__ . '/partials/footer.php'; ?>