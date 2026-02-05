<?php

namespace App\Controller;

use App\Repository\CovoiturageRepository;
use App\Service\ReviewService;

// Contrôleur pour les pages publiques de covoiturage
// - liste des covoiturages avec filtres et tris
// - détail d'un covoiturage

class CovoituragePageController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    // GET /liste-covoiturages (liste publique avec filtres)
    public function index(): void
    {
        $depart  = isset($_GET['depart']) ? trim((string) $_GET['depart']) : null;
        $arrivee = isset($_GET['arrivee']) ? trim((string) $_GET['arrivee']) : null;
        $date    = isset($_GET['date']) ? trim((string) $_GET['date']) : null;

        $prefParam = $_GET['pref'] ?? null;
        $prefs = [];
        if (is_array($prefParam)) {
            $prefs = array_values(array_filter(array_map('strval', $prefParam)));
        } elseif (is_string($prefParam) && $prefParam !== '') {
            $prefs = [$prefParam];
        }

        $fuel = isset($_GET['fuel']) ? trim((string) $_GET['fuel']) : null;

        $sort = isset($_GET['sort']) ? trim((string) $_GET['sort']) : null;
        $dir  = isset($_GET['dir'])  ? trim((string) $_GET['dir'])  : null;

        $results = [];
        try {
            $repo = new CovoiturageRepository();
            $currentUserId = isset($_SESSION['user']) ? (int) $_SESSION['user']['id'] : null;

            // IMPORTANT: ordre conforme à la signature du repository
            $results = $repo->search($depart, $arrivee, $date, $prefs, $fuel, $sort, $dir, $currentUserId);
        } catch (\Throwable $e) {
            error_log('Search error: ' . $e->getMessage());
        }

        $this->render('pages/liste-covoiturages', [
            'criteria' => [
                'depart' => $depart,
                'arrivee' => $arrivee,
                'date' => $date,
                'pref' => $prefs,
                'fuel' => $fuel,
                'sort' => $sort,
                'dir' => $dir,
            ],
            'results' => $results,
            'pageTitle' => 'Covoiturages',
            'metaDescription' => 'Parcourez les annonces de covoiturage EcoRide et trouvez un conducteur ou un passager correspondant à vos critères.',
        ]);
    }

    // GET /covoiturages/{id} (détail d'un covoiturage)
    public function show(int $id): void
    {
        $repo = new CovoiturageRepository();
        $ride = $repo->findOneWithVehicleById($id);

        if (!$ride) {
            abort(404, 'Covoiturage introuvable');
        }

        // Meta dynamiques
        $from = (string)($ride['adresse_depart'] ?? 'Départ');
        $to   = (string)($ride['adresse_arrivee'] ?? 'Arrivée');

        $when = null;
        try {
            $when = (new \DateTime((string)($ride['depart'] ?? '')))->format('d/m/Y H\hi');
        } catch (\Throwable $e) {
        }

        $titleBits = [$from . ' → ' . $to];
        if ($when) {
            $titleBits[] = $when;
        }

        $pageTitle = implode(' • ', $titleBits);
        $desc = 'Trajet de ' . $from . ' à ' . $to . ($when ? ' le ' . $when : '') . ' — trouvez votre place avec EcoRide.';

        // avis Mongo (conducteur du trajet) via ReviewService
        $reviews = [];
        $avgRating = 0.0;
        $reviewsCount = 0;

        $driverId = (int)($ride['driver_id'] ?? $ride['user_id'] ?? $ride['chauffeur_id'] ?? 0);

        try {
            $svc = new ReviewService();

            $reviews = $svc->getApprovedDriverReviews($driverId, 100);

            foreach ($reviews as &$r) {
                $pseudo = null;
                try {
                    $pid = isset($r['passager_id']) ? (int)$r['passager_id'] : 0;
                    if ($pid > 0) {
                        $uRepo = new \App\Repository\UserRepository();
                        $u = $uRepo->findById($pid);
                        if ($u) {
                            $pseudo = $u->getPseudo();
                        }
                    }
                } catch (\Throwable $e) {
                }
                $r['passager_pseudo'] = $pseudo;
            }
            unset($r);

            $stats = $svc->getDriverRatingStats($reviews);
            $avgRating = (float)$stats['avg'];
            $reviewsCount = (int)$stats['count'];
        } catch (\Throwable $e) {
            error_log('[show] load driver reviews failed: ' . $e->getMessage());
        }
        $this->render('pages/covoiturages/show', [
            'ride' => $ride,
            'pageTitle' => $pageTitle,
            'metaDescription' => $desc,
            'canonical' => SITE_URL . 'covoiturages/' . $id,
            'reviews' => $reviews,
            'avgRating' => $avgRating,
            'reviewsCount' => $reviewsCount,
        ]);
    }
}
