<?php
namespace App\Security;

// Gestion des tokens CSRF (Cross-Site Request Forgery)
final class Csrf
{
    // Génère ou retourne le token CSRF stocké en session
    public static function token(): string
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }

    // Vérifie la validité d’un token CSRF fourni
    public static function check(?string $t): bool
    {
        return is_string($t) && hash_equals($_SESSION['csrf'] ?? '', $t);
    }
}
