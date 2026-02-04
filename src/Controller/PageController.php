<?php

namespace App\Controller;


use App\Repository\CovoiturageRepository;


// Contrôleur des pages publiques (home + pages statiques)

class PageController extends Controller
{


    // Page d'accueil.
    public function home(): void
    {
        $popular = [];
        try {
            $repo = new CovoiturageRepository();
            $popular = $repo->popularDestinations(3, 3, 30);
            $reviewService = new \App\Service\ReviewService();
            $randomReviews = $reviewService->getRandomApprovedReviews(3);
        } catch (\Throwable $e) {
            error_log('[home] popular destinations failed: ' . $e->getMessage());
        }

        $this->render('home', [
            'popularDestinations' => $popular,
            'randomReviews' => $randomReviews,
            'pageTitle' => 'Accueil',
            'metaDescription' => "EcoRide, la plateforme de covoiturage responsable pour vos trajets du quotidien. Trouvez ou proposez un trajet en quelques clics.",
            'metaImage' => SITE_URL . 'assets/images/logo-share.png',
        ]);
    }

    // Page de contact.
    public function contact(): void
    {
        $this->render('pages/contact', [
            'pageTitle' => 'Contact',
            'metaDescription' => "Contactez l'équipe EcoRide pour toute question sur le covoiturage, votre compte ou l'application.",
        ]);
    }

    // Page "À propos".
    public function about(): void
    {
        $this->render('pages/about');
    }

    // Page des conditions d'utilisation.
    public function terms(): void
    {
        $this->render('pages/terms');
    }

    // Page de politique de confidentialité.
    public function privacy(): void
    {
        $this->render('pages/privacy');
    }

    // Page des mentions légales.
    public function mentionsLegales(): void
    {
        $this->render('pages/mentions-legales');
    }
}
