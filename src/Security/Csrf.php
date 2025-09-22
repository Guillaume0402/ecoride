<?php
namespace App\Security;

final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }

    public static function check(?string $t): bool
    {
        return is_string($t) && hash_equals($_SESSION['csrf'] ?? '', $t);
    }
}
