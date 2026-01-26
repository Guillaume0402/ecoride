<section class="contact-section d-flex flex-column align-items-center justify-content-center">
    <h1 class="contact-title mb-5 mt-5">Contactez-nous</h1>
    <div class="contact-card-wrapper w-100 d-flex justify-content-center px-3 mb-5">
        <form class="form-box w-100" action="/contact" method="POST">
            <input type="hidden" name="csrf" value="<?= \App\Security\Csrf::token() ?>">
            <div class="m-3">
                <label for="name" class="form-label">Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-control"
                    value="<?= htmlspecialchars($_SESSION['user']['pseudo'] ?? '') ?>"
                    required>
            </div>
            <div class="m-3">
                <label for="email" class="form-label">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control"
                    value="<?= htmlspecialchars($_SESSION['user']['email'] ?? '') ?>"
                    required>
            </div>
            <div class="m-3">
                <label for="subject" class="form-label">Subject</label>
                <input
                    type="text"
                    id="subject"
                    name="subject"
                    class="form-control"
                    placeholder="(optionnel)">
            </div>
            <div class="m-4">
                <label for="message" class="form-label">Message</label>
                <textarea
                    id="message"
                    name="message"
                    rows="5"
                    class="form-control"
                    placeholder="Ex: Bonjour, je vous contacte pour…"
                    required></textarea>
            </div>
            <div class="text-center mt-5">
                <button type="submit" class="btn btn-inscription">Envoyer</button>
            </div>
        </form>
    </div>
</section>