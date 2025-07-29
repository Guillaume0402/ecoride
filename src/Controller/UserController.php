<?php

namespace App\Controller;

use App\Model\UserModel;
use App\Model\VehicleModel;

class UserController extends Controller
{
    private UserModel $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new UserModel();

        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = "Veuillez vous connecter.";
            redirect('/login');
        }
    }
   

    private function mapMotorType(string $type): int
    {
        return match ($type) {
            'essence' => 1,
            'diesel' => 2,
            'electrique' => 3,
            'hybride' => 4,
            default => 0,
        };
    }

    private function mapRoleToId(string $role): int
    {
        return match ($role) {
            'passager'  => 1,
            'chauffeur' => 2,
            'les-deux'  => 3,
            default     => 1
        };
    }
}
