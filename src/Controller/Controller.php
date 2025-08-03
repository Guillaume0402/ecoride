<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\UserService;

class Controller
{
    protected UserRepository $userRepository;
    protected UserService $userService;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Initialisation des services communs
        $this->userRepository = new UserRepository();
        $this->userService = new UserService();
    }

    protected function render(string $view, array $data = []): void
    {
        extract($data);

        $viewPath = APP_ROOT . '/src/View/' . ltrim($view, '/') . '.php';
        if (!file_exists($viewPath)) {
            throw new \Exception("Le fichier de vue {$viewPath} n'existe pas.");
        }

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        require APP_ROOT . '/src/View/layout.php';
    }
}
