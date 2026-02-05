<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\UserService;
use App\Repository\VehicleRepository;
use App\Security\Csrf;
use App\Service\Flash;

class Controller
{
    protected UserRepository $userRepository;
    protected UserService $userService;

    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }

        $this->userRepository = new UserRepository();
        $this->userService    = new UserService();
    }

       // GARDES COMMUNES (auth / méthode / csrf)
      
    protected function requireAuth(string $redirectTo = '/login'): array
    {
        if (!isset($_SESSION['user']) || empty($_SESSION['user']['id'])) {
            Flash::add('Veuillez vous connecter.', 'danger');
            redirect($redirectTo);
            exit;
        }
        return $_SESSION['user'];
    }

    protected function requirePost(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            abort(405);
        }
    }

    protected function requireCsrf(?string $token, string $redirectTo): void
    {
        if (!Csrf::check($token)) {
            Flash::add('Requête invalide (CSRF).', 'danger');
            redirect($redirectTo);
            exit;
        }
    }

    
       // RENDU DES VUES

    protected function render(string $view, array $data = [], bool $withGlobals = true): void
    {
        extract($data, EXTR_SKIP);

        if ($withGlobals) {
            $globals = $this->buildLayoutGlobals();
            extract($globals, EXTR_SKIP);
        }

        $viewPath = APP_ROOT . '/src/View/' . ltrim($view, '/') . '.php';
        if (!file_exists($viewPath)) {
            throw new \Exception("Le fichier de vue {$viewPath} n'existe pas.");
        }

        ob_start();
        require $viewPath;
        $__content = ob_get_clean();

        require APP_ROOT . '/src/View/layout.php';
    }

    
       // DONNÉES GLOBALES LAYOUT (navbar, badges, etc.)

    private function buildLayoutGlobals(): array
    {
        $pendingCount = 0;
        $myTripsCount = 0;
        $employeeModPendingCount = 0;

        $hasVehicle = false;
        $userVehicles = [];

        if (isset($_SESSION['user']['id'])) {
            $userId = (int) $_SESSION['user']['id'];

            try {
                $currentUser = $this->userRepository->findById($userId);
                if ($currentUser) {
                    $_SESSION['user']['credits'] = $currentUser->getCredits();
                    $_SESSION['user']['travel_role'] = $currentUser->getTravelRole();
                    if (empty($_SESSION['user']['photo'])) {
                        $_SESSION['user']['photo'] = defined('DEFAULT_AVATAR_URL')
                            ? DEFAULT_AVATAR_URL
                            : '/assets/images/logo.svg';
                    }
                }
            } catch (\Throwable $e) {
                error_log('[render] User refresh failed: ' . $e->getMessage());
            }

            try {
                $vehicleRepo = new VehicleRepository();
                $userVehicles = $vehicleRepo->findAllByUserId($userId);
                $hasVehicle = !empty($userVehicles);
            } catch (\Throwable $e) {
                error_log('[render] Vehicle preload failed: ' . $e->getMessage());
            }

            try {
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
                if ((int) ($_SESSION['user']['role_id'] ?? 0) === 2) {
                    $mod = new \App\Service\ReviewModerationService();
                    $docs = $mod->listPending();
                    $employeeModPendingCount = is_array($docs) ? count($docs) : 0;
                }
            } catch (\Throwable $e) {
                error_log('[render] Employee moderation counter failed: ' . $e->getMessage());
            }
        }

        return compact(
            'pendingCount',
            'myTripsCount',
            'employeeModPendingCount',
            'hasVehicle',
            'userVehicles'
        );
    }
}
