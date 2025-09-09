<footer class="mt-auto d-flex flex-column flex-lg-row justify-content-center align-items-center gap-2 gap-lg-5 py-3  w-100 ">
    <a class="mb-0" href="/privacy" rel="noopener" aria-label="Politique de confidentialité">Politique de confidentialité</a>
    <a class="mb-0" href="/terms" rel="noopener" aria-label="Mentions légales">Mentions légales</a>
    <a class="mb-0" href="/contact" rel="noopener" aria-label="Contact">Contact</a>
    <span class="small ms-lg-4 mt-2 mt-lg-0">&copy; <?= date('Y') ?> EcoRide. Tous droits réservés.</span>
</footer>


<!-- Un seul script Bootstrap ici -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<?php if (isset($isAdminPage) && $isAdminPage): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php endif; ?>

