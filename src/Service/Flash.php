<?php
namespace App\Service;

final class Flash
{
    public static function add(string $message, string $type = 'success'): void {
        $_SESSION['flash'][] = ['message' => $message, 'type' => $type];
    }

    /** Récupère et vide (consomme) les flashs */
    public static function all(): array {
        $flashes = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flashes;
    }
}
