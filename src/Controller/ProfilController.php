<?php

namespace App\Controller;

use App\Service\Flash;
use App\Security\Csrf;
use App\Repository\VehicleRepository;
use App\Repository\UserRepository;
use MongoDB\Client;
use MongoDB\Model\BSONDocument;


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
    // GET /my-profil
    public function profil(): void
    {
        $user = $_SESSION['user'];

        // véhicules
        $vehRepo = new VehicleRepository();
        $vehicles = $vehRepo->findAllByUserId($user['id']);

        // avis Mongo (conducteur)
        $reviews = [];
        $avgRating = 0.0;
        $reviewsCount = 0;

        try {
            $client = new Client($_ENV['MONGO_DSN'] ?? ($_ENV['MONGODB_URI'] ?? 'mongodb://mongo:27017'));
            $coll = $client->selectCollection($_ENV['MONGO_DB'] ?? 'ecoride', 'reviews');

            $cursor = $coll->find([
                'kind' => 'review',
                'status' => 'approved',
                'driver_id' => (int)$user['id'],
            ], [
                'sort' => ['created_at_ms' => -1],
                'limit' => 100,
            ]);

            $sum = 0;
            $cnt = 0;

            foreach ($cursor as $doc) {
                if ($doc instanceof BSONDocument) {
                    $doc = $doc->getArrayCopy();
                } elseif (is_object($doc)) {
                    $doc = json_decode(json_encode($doc), true) ?? [];
                } elseif (!is_array($doc)) {
                    $doc = [];
                }

                // enrich pseudo passager (optionnel)
                $passagerPseudo = null;
                try {
                    $pid = isset($doc['passager_id']) ? (int)$doc['passager_id'] : 0;
                    if ($pid > 0) {
                        $uRepo = new UserRepository();
                        $pu = $uRepo->findById($pid);
                        if ($pu) {
                            $passagerPseudo = $pu->getPseudo();
                        }
                    }
                } catch (\Throwable $e) {
                    // silencieux
                }

                $doc['passager_pseudo'] = $passagerPseudo;
                $reviews[] = $doc;

                if (isset($doc['rating']) && is_numeric($doc['rating'])) {
                    $r = (int)$doc['rating'];
                    if ($r >= 1 && $r <= 5) {
                        $sum += $r;
                        $cnt++;
                    }
                }
            }

            $reviewsCount = count($reviews);
            if ($cnt > 0) {
                $avgRating = round($sum / $cnt, 1);
            }
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
        } else {
            // fallback simple
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'jpg';
        }

        $name = bin2hex(random_bytes(8)) . '.' . strtolower($ext);
        $ok   = move_uploaded_file($file['tmp_name'], $dir . $name);

        return $ok ? '/uploads/' . $name : null;
    }
}
