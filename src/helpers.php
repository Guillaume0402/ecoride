<?php

use App\Controller\ErrorController;

if (!function_exists('redirect')) {
    function redirect(string $url, int $statusCode = 302): void
    {
        http_response_code($statusCode);
        header("Location: $url");
        exit;
    }
}

if (!function_exists('abort')) {
    function abort(int $statusCode = 404, string $message = ''): void
    {
        $errorController = new ErrorController();

        switch ($statusCode) {
            case 404:
                $errorController->show404($message ?: 'Page non trouvée');
                break;
            case 405:
                $errorController->show405($message ?: 'Méthode non autorisée');
                break;
            case 500:
                $errorController->show500($message ?: 'Erreur interne');
                break;
            default:
                $errorController->show($message ?: 'Erreur');
                break;
        }

        exit;
    }
}

if (!function_exists('getRideCreateFee')) {
    function getRideCreateFee(): int
    {
        return (int) (defined('RIDE_CREATE_FEE_CREDITS') ? RIDE_CREATE_FEE_CREDITS : 2);
    }
}
