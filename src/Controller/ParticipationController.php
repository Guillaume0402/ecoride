<?php

namespace App\Controller;

use App\Repository\CovoiturageRepository;
use App\Repository\ParticipationRepository;
use App\Security\Csrf;
use App\Service\Flash;

class ParticipationController extends Controller
{
    private ParticipationRepository $participationRepository;
    private CovoiturageRepository $covoiturageRepository;

    public function __construct()
    {
        parent::__construct();
        $this->participationRepository = new ParticipationRepository();
        $this->covoiturageRepository = new CovoiturageRepository();
    }

    // POST /participations/create
    public function create(): void
    {
        if (!isset($_SESSION['user'])) {
            Flash::add('Veuillez vous connecter pour participer.', 'warning');
            redirect('/login');
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            abort(405);
        }

        if (!Csrf::check($_POST['csrf'] ?? null)) {
            Flash::add('Requête invalide (CSRF).', 'danger');
            redirect('/liste-covoiturages');
        }

        $userId = (int) $_SESSION['user']['id'];
        $covoiturageId = (int) ($_POST['covoiturage_id'] ?? 0);
        if ($covoiturageId <= 0) {
            Flash::add('Covoiturage invalide.', 'danger');
            redirect('/liste-covoiturages');
        }

        // Récup covoiturage + véhicule pour capacité
        $ride = $this->covoiturageRepository->findOneWithVehicleById($covoiturageId);
        if (!$ride) {
            Flash::add('Covoiturage introuvable.', 'danger');
            redirect('/liste-covoiturages');
        }

        // Interdire au conducteur de participer à son propre trajet
        if ((int)$ride['driver_id'] === $userId) {
            Flash::add('Vous êtes le conducteur de ce trajet.', 'warning');
            redirect('/liste-covoiturages');
        }

        // Interdire si départ passé
        try {
            $depart = new \DateTime($ride['depart']);
            if ($depart < new \DateTime()) {
                Flash::add('Ce trajet est déjà passé.', 'warning');
                redirect('/liste-covoiturages');
            }
        } catch (\Throwable $e) {
            // si problème de parsing, sécurité
            Flash::add('Date de départ invalide.', 'danger');
            redirect('/liste-covoiturages');
        }

        // Déjà participant ?
        if ($this->participationRepository->findByCovoiturageAndPassager($covoiturageId, $userId)) {
            Flash::add('Vous avez déjà une demande pour ce trajet.', 'info');
            redirect('/liste-covoiturages');
        }

        // Capacité restante: places du véhicule - participations confirmées
        $placesVehicule = (int)($ride['vehicle_places'] ?? 0);
        $confirmes = $this->participationRepository->countConfirmedByCovoiturageId($covoiturageId);
        $restantes = max(0, $placesVehicule - $confirmes);
        if ($restantes <= 0) {
            Flash::add('Plus aucune place disponible.', 'warning');
            redirect('/liste-covoiturages');
        }

        // Créer participation en attente de validation (flux simple pour l’instant)
        if ($this->participationRepository->create($covoiturageId, $userId, 'en_attente_validation')) {
            Flash::add('Demande de participation envoyée.', 'success');
            redirect('/liste-covoiturages');
        }

        Flash::add('Erreur lors de la demande de participation.', 'danger');
        redirect('/liste-covoiturages');
    }
}
