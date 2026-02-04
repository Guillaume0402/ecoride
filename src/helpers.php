<?php

use App\Controller\ErrorController;

// Redirige l'utilisateur vers une autre URL puis arrête le script
if (!function_exists('redirect')) {
    function redirect(string $url, int $statusCode = 302): void
    {
        $url = trim($url);

        // Anti header injection
        if (preg_match("/[\r\n]/", $url)) {
            abort(400, 'Redirection invalide');
        }
        // Anti open-redirect : autoriser uniquement les chemins internes
        if ($url === '' || $url[0] !== '/') {
            abort(400, 'Redirection invalide');
        }
        http_response_code($statusCode);
        header("Location: $url");
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

// Rend des étoiles HTML pour une note (0..5)
if (!function_exists('renderStars')) {
    function renderStars($rating, int $max = 5, bool $showValue = false): string
    {
        if (!is_numeric($rating)) {
            $rating = 0;
        }
        $rating = (float) $rating;

        // clamp 0..max
        if ($rating < 0) $rating = 0;
        if ($rating > $max) $rating = $max;

        $filled = (int) floor($rating);
        $empty  = $max - $filled;

        $html = '<span class="rating-stars" aria-label="Note ' . $filled . ' sur ' . $max . '">';

        for ($i = 0; $i < $filled; $i++) {
            $html .= '<span class="rating-stars__star is-full">★</span>';
        }
        for ($i = 0; $i < $empty; $i++) {
            $html .= '<span class="rating-stars__star is-empty">★</span>';
        }

        $html .= '</span>';

        if ($showValue) {
            $html .= '<span class="rating-stars__value">' . $filled . '/' . $max . '</span>';
        }

        return $html;
    }
}
