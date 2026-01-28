<?php
namespace App\Service;

final class Flash {

    // Ajoute un message flash en session 
  public static function add(string $message, string $type='success'): void {
    if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
    $_SESSION['flash'][] = ['message'=>$message, 'type'=>$type];
  }

    // Récupère et supprime les messages flash de la session
  public static function pull(): array {
    if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
    $out = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']); // on consomme
    return $out;
  }
}

