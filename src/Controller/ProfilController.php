<?php

namespace App\Controller;

use App\Service\Flash;
use App\Security\Csrf;
use App\Repository\VehicleRepository;
use App\Repository\CovoiturageRepository;
use App\Repository\ParticipationRepository;
use App\Repository\TransactionRepository;
use App\Service\MaintenanceService;
use App\Service\ReviewService;


class ProfilController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!isset($_SESSION['user'])) {
            Flash::add('Vous devez être connecté pour accéder à votre profil.', 'danger');
            session_write_close();
            header('Location: /login', true, 302);
            exit;
        }
    }

    // POST /creation-profil
    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            abort(405);
        }

        if (!Csrf::check($_POST['csrf'] ?? null)) {
            Flash::add('Requête invalide (CSRF).', 'danger');
            redirect('/creation-profil');
            return;
        }

        $userId = (int)($_SESSION['user']['id'] ?? 0);
        $user   = $this->userRepository->findById($userId);

        if (!$user) {
            Flash::add('Utilisateur introuvable.', 'danger');
            redirect('/creation-profil');
            return;
        }

        // Champs requis
        $pseudo      = trim($_POST['pseudo'] ?? '');
        $travelRole  = trim($_POST['travel_role'] ?? '');

        if ($pseudo === '' || $travelRole === '') {
            Flash::add('Champs requis manquants.', 'danger');
            redirect('/creation-profil');
            return;
        }

        // Changement de mot de passe (optionnel)
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword     = $_POST['new_password'] ?? '';

        if ($newPassword !== '') {
            if ($currentPassword === '' || !$this->userService->verifyPassword($user, $currentPassword)) {
                Flash::add('Mot de passe actuel incorrect.', 'danger');
                redirect('/creation-profil');
                return;
            }
            $this->userService->hashPassword($user, $newPassword);
        }

        // Maj champs profil
        $user->setPseudo($pseudo);
        $user->setTravelRole($travelRole);
        $user->setRoleId((int)($_SESSION['user']['role_id'] ?? $user->getRoleId()));

        // Upload photo si fourni
        if (!empty($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $path = $this->handlePhotoUpload($_FILES['photo']);
            if ($path) {
                $user->setPhoto($path);
            }
        }

        // Persistance
        $this->userRepository->update($user);

        // Rafraîchir la session UNE SEULE fois
        $updated = $this->userRepository->findById($userId);
        $_SESSION['user'] = $this->userService->toArray($updated);

        // Flash + PRG
        Flash::add('Profil mis à jour avec succès.', 'success');
        session_write_close();
        header('Location: /my-profil', true, 303);
        exit;
    }
    // GET /creation-profil
    public function creationProfil(): void
    {
        $user = $_SESSION['user'];

        $vehRepo = new VehicleRepository();

        $vehicleEntity = !empty($_GET['id'])
            ? $vehRepo->findById((int) $_GET['id'])
            : $vehRepo->findByUserId($user['id']);

        $vehicle = $vehicleEntity ? $vehicleEntity->toArray() : null;

        $this->render('pages/creation-profil', [
            'user' => $user,
            'vehicle' => $vehicle,
        ]);
    }

    // GET /my-profil
    public function profil(): void
    {
        $user = $_SESSION['user'];

        // véhicules
        $vehRepo = new VehicleRepository();
        $vehicles = $vehRepo->findAllByUserId($user['id']);

        // avis Mongo (conducteur) via ReviewService
        $reviews = [];
        $avgRating = 0.0;
        $reviewsCount = 0;

        try {
            $svc = new ReviewService();

            $reviews = $svc->getApprovedDriverReviews((int)$user['id'], 100);

            // BONUS (garde ton ancien enrich pseudo passager) :
            // si tu veux garder le pseudo passager dans la vue,
            // il faut soit l'enrichir ici, soit ajouter une méthode au service.
            // Pour rester minimal, on le fait ici comme avant.
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
            error_log('[profil] load reviews failed: ' . $e->getMessage());
        }


        $this->render('pages/my-profil', [
            'user' => $user,
            'vehicles' => $vehicles,
            'reviews' => $reviews,
            'avgRating' => $avgRating,
            'reviewsCount' => $reviewsCount,
        ]);
    }

    // Sauvegarde l’upload et renvoie le chemin web ou null
    private function handlePhotoUpload(array $file): ?string
    {
        $dir = PUBLIC_ROOT . '/uploads/';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        // (Optionnel mais recommandé) contrôle du type MIME
        if (class_exists(\finfo::class)) {
            $finfo   = new \finfo(FILEINFO_MIME_TYPE);
            $mime    = $finfo->file($file['tmp_name']) ?: '';
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            if (!isset($allowed[$mime])) {
                return null;
            }
            $ext  = $allowed[$mime];
            // Upload Cloudinary si configuré (persistant prod)
            $cloudUrl = $this->uploadToCloudinary($file);
            if ($cloudUrl) {
                return $cloudUrl;
            }
        } else {
            // fallback simple
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'jpg';
        }

        $name = bin2hex(random_bytes(8)) . '.' . strtolower($ext);
        $ok   = move_uploaded_file($file['tmp_name'], $dir . $name);

        return $ok ? '/uploads/' . $name : null;
    }
    // GET /mes-credits
    public function mesCredits(): void
    {
        $user = $_SESSION['user'];

        $transactions = [];
        try {
            $txRepo = new TransactionRepository();
            $transactions = $txRepo->findByUserId((int)$user['id'], 50);
        } catch (\Throwable $e) {
            error_log('[mesCredits] transactions load failed: ' . $e->getMessage());
        }

        $this->render('pages/mes-credits', [
            'user' => $user,
            'transactions' => $transactions,
        ]);
    }

    private function getCloudinaryConfig(): ?array
    {
        $url = $_ENV['CLOUDINARY_URL'] ?? (getenv('CLOUDINARY_URL') ?: null);
        if (!is_string($url) || $url === '' || !str_starts_with($url, 'cloudinary://')) {
            return null;
        }

        $parsed = parse_url($url);
        if (!$parsed) return null;

        $cloud  = $parsed['host'] ?? null;
        $key    = $parsed['user'] ?? null;
        $secret = $parsed['pass'] ?? null;

        if (!$cloud || !$key || !$secret) return null;

        return ['cloud' => $cloud, 'key' => $key, 'secret' => $secret];
    }

    private function uploadToCloudinary(array $file, string $folder = 'ecoride/avatars'): ?string
    {
        $cfg = $this->getCloudinaryConfig();
        if (!$cfg) return null;

        if (!function_exists('curl_init')) {
            error_log('[Cloudinary] cURL extension not available');
            return null;
        }

        $timestamp = time();
        $toSign = 'folder=' . $folder . '&timestamp=' . $timestamp . $cfg['secret'];
        $signature = sha1($toSign);

        $endpoint = 'https://api.cloudinary.com/v1_1/' . rawurlencode($cfg['cloud']) . '/image/upload';

        $post = [
            'file' => new \CURLFile($file['tmp_name']),
            'api_key' => $cfg['key'],
            'timestamp' => $timestamp,
            'folder' => $folder,
            'signature' => $signature,
        ];

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

        $raw = curl_exec($ch);
        $err = curl_error($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false) {
            error_log('[Cloudinary] curl error: ' . $err);
            return null;
        }

        $json = json_decode($raw, true);
        if ($code >= 200 && $code < 300 && is_array($json)) {
            return isset($json['secure_url']) ? (string)$json['secure_url'] : null;
        }

        error_log('[Cloudinary] upload failed HTTP ' . $code . ' body=' . $raw);
        return null;
    }


    // GET /mes-covoiturages
    public function mesCovoiturages(): void
    {
        // Balayage léger de maintenance (annulations auto + rattrapage remboursements)
        // Limité à 1 exécution toutes les 5 minutes par session pour éviter de relancer à chaque refresh.
        $lastSweep = (int)($_SESSION['maintenance_last_sweep'] ?? 0);
        if (time() - $lastSweep > 300) { // 300s = 5 min
            try {
                (new MaintenanceService())->sweep();
                $_SESSION['maintenance_last_sweep'] = time();
            } catch (\Throwable $e) {
                error_log('[mesCovoiturages maintenance sweep] ' . $e->getMessage());
            }
        }


        $userId = (int) $_SESSION['user']['id'];

        $covoitRepo = new CovoiturageRepository();
        $partRepo   = new ParticipationRepository();

        $asDriverAll    = $covoitRepo->findByDriverId($userId);
        $asPassengerAll = $partRepo->findByPassagerId($userId);

        // Filtrage: aligner avec le header
        $now = new \DateTime();
        $graceMinutes = defined('AUTO_CANCEL_MINUTES') ? (int) AUTO_CANCEL_MINUTES : 60;

        $asDriver = array_values(array_filter($asDriverAll, function ($c) use ($now, $graceMinutes) {
            try {
                $depart = new \DateTime($c['depart']);
            } catch (\Throwable $e) {
                return false;
            }

            $status = (string)($c['status'] ?? 'en_attente');

            if ($status === 'demarre') {
                return true;
            }

            $graceThreshold = (clone $depart)->modify("+{$graceMinutes} minutes");
            return ($depart >= $now || $graceThreshold >= $now) && !in_array($status, ['annule', 'termine'], true);
        }));

        $asPassenger = array_values(array_filter($asPassengerAll, function ($p) use ($now) {
            $status  = (string)($p['status'] ?? '');
            $cStatus = (string)($p['covoit_status'] ?? 'en_attente');

            $isUpcoming = false;
            try {
                $isUpcoming = (new \DateTime($p['depart'])) >= $now;
            } catch (\Throwable $e) {
            }

            return in_array($status, ['en_attente_validation', 'confirmee'], true)
                && $isUpcoming
                && !in_array($cStatus, ['annule', 'termine'], true);
        }));

        // Historique
        $historyDriver = array_values(array_filter($asDriverAll, function ($c) use ($now, $graceMinutes) {
            try {
                $depart = new \DateTime($c['depart']);
            } catch (\Throwable $e) {
                return false;
            }

            $status = (string)($c['status'] ?? 'en_attente');

            if (in_array($status, ['annule', 'termine'], true)) {
                return true;
            }

            $graceThreshold = (clone $depart)->modify("+{$graceMinutes} minutes");
            return $graceThreshold < $now && $status !== 'demarre';
        }));

        $historyPassenger = array_values(array_filter($asPassengerAll, function ($p) use ($now) {
            $status  = (string)($p['status'] ?? '');
            $cStatus = (string)($p['covoit_status'] ?? 'en_attente');

            $isPast = false;
            try {
                $isPast = (new \DateTime($p['depart'])) < $now;
            } catch (\Throwable $e) {
            }

            $isActiveParticipation = in_array($status, ['en_attente_validation', 'confirmee'], true);
            $isActiveRide          = !in_array($cStatus, ['annule', 'termine'], true);

            return !$isActiveParticipation || !$isActiveRide || $isPast;
        }));

        // Compte des validations en attente
        $pendingValidations = 0;
        $txRepo = new TransactionRepository();

        foreach ($asPassengerAll as $p) {
            if (($p['status'] ?? '') === 'confirmee' && ($p['covoit_status'] ?? '') === 'termine') {
                $driverId = (int)($p['driver_user_id'] ?? 0);
                $covoiturageId = (int)($p['covoiturage_id'] ?? 0);
                $motif = 'Crédit conducteur trajet #' . $covoiturageId . ' - passager #' . $userId;

                if (!$txRepo->existsForMotif($driverId, $motif)) {
                    $pendingValidations++;
                }
            }
        }

        $this->render('pages/mes-covoiturages', [
            'asDriver' => $asDriver,
            'asPassenger' => $asPassenger,
            'historyDriver' => $historyDriver,
            'historyPassenger' => $historyPassenger,
            'pendingValidations' => $pendingValidations,
        ]);
    }
}
