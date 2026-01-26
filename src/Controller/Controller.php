<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\UserService;
use App\Repository\VehicleRepository;

// Contrôleur de base: session, services communs et rendu des vues avec layout
class Controller
{
    //Accès au dépôt des utilisateurs pour les opérations de persistance.

    protected UserRepository $userRepository;

    // Service métier lié aux utilisateurs (logique applicative).

    protected UserService $userService;

    // Initialise la session et les services/dépôts communs.     
    public function __construct()
    {
        // Démarre la session PHP si elle n'est pas déjà active
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Initialisation des services communs
        $this->userRepository = new UserRepository();
        $this->userService = new UserService();
    }

    // Rend une vue au sein du layout global (extrait $data, bufferise la vue, inclut layout)

    protected function render(string $view, array $data = []): void
    {

        // Expose les clés de $data comme variables accessibles dans la vue
        extract($data);

        // Variables globales du layout (navbar, badges, etc.)
        $globals = $this->buildLayoutGlobals();
        extract($globals, EXTR_SKIP);

        // Construit le chemin absolu de la vue et vérifie son existence
        $viewPath = APP_ROOT . '/src/View/' . ltrim($view, '/') . '.php';
        if (!file_exists($viewPath)) {
            throw new \Exception("Le fichier de vue {$viewPath} n'existe pas.");
        }

        // Capture le rendu de la vue dans $content
        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        // Inclut le layout qui utilise $content pour afficher la page complète
        require APP_ROOT . '/src/View/layout.php';
    }

    private function buildLayoutGlobals(): array
    {
        $pendingCount = 0;
        $myTripsCount = 0;
        $employeeModPendingCount = 0;

        $hasVehicle = false;
        $userVehicles = [];

        if (isset($_SESSION['user']) && !empty($_SESSION['user']['id'])) {

            try {
                $currentUser = $this->userRepository->findById((int) $_SESSION['user']['id']);
                if ($currentUser) {
                    $_SESSION['user']['credits'] = $currentUser->getCredits();
                    if (empty($_SESSION['user']['photo'])) {
                        $_SESSION['user']['photo'] = defined('DEFAULT_AVATAR_URL') ? DEFAULT_AVATAR_URL : '/assets/images/logo.svg';
                    }
                    $_SESSION['user']['travel_role'] = $currentUser->getTravelRole();
                }
            } catch (\Throwable $e) {
                error_log('[render] User refresh failed: ' . $e->getMessage());
            }

            try {
                $vehicleRepo = new VehicleRepository();
                $userVehicles = $vehicleRepo->findAllByUserId((int) $_SESSION['user']['id']);
                $hasVehicle = !empty($userVehicles);
            } catch (\Throwable $e) {
                error_log('[render] Vehicle preload failed: ' . $e->getMessage());
            }

            try {
                $userId = (int) $_SESSION['user']['id'];

                $partRepo = new \App\Repository\ParticipationRepository();
                $pending = $partRepo->findPendingByDriverId($userId);
                $pendingCount = is_array($pending) ? count($pending) : 0;

                $pdo = \App\Db\Mysql::getInstance()->getPDO();

                $stmt = $pdo->prepare("SELECT COUNT(*) FROM participations WHERE passager_id = :u AND status = 'confirmee'");
                $stmt->execute([':u' => $userId]);
                $asPassengerCount = (int) $stmt->fetchColumn();

                $stmt2 = $pdo->prepare("SELECT COUNT(*) FROM covoiturages WHERE driver_id = :u AND depart >= NOW() AND status NOT IN ('annule','termine')");
                $stmt2->execute([':u' => $userId]);
                $asDriverUpcomingCount = (int) $stmt2->fetchColumn();

                $myTripsCount = $asPassengerCount + $asDriverUpcomingCount;
            } catch (\Throwable $e) {
                error_log('[render] Counters preload failed: ' . $e->getMessage());
            }

            try {
                if (isset($_SESSION['user']['role_id']) && (int) $_SESSION['user']['role_id'] === 2) {
                    $mod = new \App\Service\ReviewModerationService();
                    $docs = $mod->listPending();
                    $employeeModPendingCount = is_array($docs) ? count($docs) : 0;
                }
            } catch (\Throwable $e) {
                error_log('[render] Employee moderation counter failed: ' . $e->getMessage());
            }
        }

        return [
            'pendingCount' => $pendingCount,
            'myTripsCount' => $myTripsCount,
            'employeeModPendingCount' => $employeeModPendingCount,
            'hasVehicle' => $hasVehicle,
            'userVehicles' => $userVehicles,
        ];
    }
}
