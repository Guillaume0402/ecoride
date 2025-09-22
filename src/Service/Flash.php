<?php
namespace App\Service;

final class Flash {
  public static function add(string $message, string $type='success'): void {
    if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
    $_SESSION['flash'][] = ['message'=>$message, 'type'=>$type];
  }
  public static function pull(): array {
    if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
    $out = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']); // on consomme
    return $out;
  }
}

