<?php

namespace App\Controller;

use App\Repository\CovoiturageRepository;

class CovoituragePageController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    // GET /liste-covoiturages
    public function index(): void
    {
        $depart = isset($_GET['depart']) ? trim((string)$_GET['depart']) : null;
        $arrivee = isset($_GET['arrivee']) ? trim((string)$_GET['arrivee']) : null;
        $date = isset($_GET['date']) ? trim((string)$_GET['date']) : null;

        $prefParam = $_GET['pref'] ?? null;
        $prefs = [];
        if (is_array($prefParam)) {
            $prefs = array_values(array_filter(array_map('strval', $prefParam)));
        } elseif (is_string($prefParam) && $prefParam !== '') {
            $prefs = [$prefParam];
        }

        $sort = isset($_GET['sort']) ? trim((string)$_GET['sort']) : null;
        $dir  = isset($_GET['dir'])  ? trim((string)$_GET['dir'])  : null;

        $results = [];
        try {
            $repo = new CovoiturageRepository();
            $currentUserId = isset($_SESSION['user']) ? (int)$_SESSION['user']['id'] : null;
            $results = $repo->search($depart, $arrivee, $date, $prefs, $sort, $dir, $currentUserId);
        } catch (\Throwable $e) {
            error_log('Search error: ' . $e->getMessage());
        }

        $this->render('pages/liste-covoiturages', [
            'criteria' => [
                'depart' => $depart,
                'arrivee' => $arrivee,
                'date' => $date,
                'pref' => $prefs,
                'sort' => $sort,
                'dir' => $dir,
            ],
            'results' => $results,
            'pageTitle' => 'Covoiturages',
            'metaDescription' => 'Parcourez les annonces de covoiturage EcoRide et trouvez un conducteur ou un passager correspondant à vos critères.',
        ]);
    }
}
