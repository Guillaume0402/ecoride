<?php

use App\Controller\ErrorController;

// Redirige l'utilisateur vers une autre URL puis arrête le script
if (!function_exists('redirect')) {
    function redirect(string $url, int $statusCode = 302): void
    {
        // Définit le code de statut HTTP (302 = redirection par défaut)
        http_response_code($statusCode);
        // Envoie l'en-tête de redirection vers l'URL passée en paramètre
        header("Location: $url");
        // Arrête immédiatement l'exécution du script
        exit;
    }
}

// Affiche une page d'erreur adaptée au code fourni puis arrête le script
if (!function_exists('abort')) {
    function abort(int $statusCode = 404, string $message = ''): void
    {
        // Contrôleur dédié à l'affichage des pages d'erreur
        $errorController = new ErrorController();

        // Choisit la vue d'erreur à afficher selon le code HTTP
        switch ($statusCode) {
            case 404:
                // Page "non trouvée"
                $errorController->show404($message ?: 'Page non trouvée');
                break;
            case 405:
                // Méthode HTTP non autorisée
                $errorController->show405($message ?: 'Méthode non autorisée');
                break;
            case 500:
                // Erreur interne du serveur
                $errorController->show500($message ?: 'Erreur interne');
                break;
            default:
                // Cas générique pour tout autre code
                $errorController->show($message ?: 'Erreur');
                break;
        }

        // Arrête l'exécution du script après l'affichage de l'erreur
        exit;
    }
}

// Retourne le coût en crédits pour créer un covoiturage
if (!function_exists('getRideCreateFee')) {
    function getRideCreateFee(): int
    {
        // Utilise la constante si elle est définie, sinon 2 comme valeur par défaut
        return (int) (defined('RIDE_CREATE_FEE_CREDITS') ? RIDE_CREATE_FEE_CREDITS : 2);
    }
}
