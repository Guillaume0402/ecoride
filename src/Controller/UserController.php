<?php

namespace App\Controller;

use App\Model\UserModel;
use App\Controller\Controller;

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

    
}
