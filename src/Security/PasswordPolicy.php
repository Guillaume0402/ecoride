<?php

namespace App\Security;

final class PasswordPolicy
{
    // Vérifie la robustesse du mot de passe.

    public static function validate(string $password, ?string $username = null, ?string $email = null): array
    {
        $errors = [];

        // 1) Longueur 12..72
        $len = mb_strlen($password, 'UTF-8');
        if ($len < 12) $errors[] = "Le mot de passe doit contenir au moins 12 caractères.";
        if ($len > 72) $errors[] = "Le mot de passe ne doit pas dépasser 72 caractères.";

        // 2) Complexité
        // - au moins 1 minuscule, 1 majuscule, 1 chiffre, 1 spécial ; pas d'espace
        $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s])(?!.*\s).+$/u';
        if (!preg_match($pattern, $password)) {
            $errors[] = "Le mot de passe doit contenir au moins une minuscule, une majuscule, un chiffre et un caractère spécial, sans espace.";
        }

        // 3) Évite d'inclure pseudo / email (partie locale avant @)
        $lowerPwd = mb_strtolower($password, 'UTF-8');
        if ($username) {
            $u = mb_strtolower($username, 'UTF-8');
            if ($u !== '' && mb_strlen($u) >= 3 && str_contains($lowerPwd, $u)) {
                $errors[] = "Le mot de passe ne doit pas contenir votre pseudo.";
            }
        }        
        if ($email && str_contains($email, '@')) {
            [$local] = explode('@', $email, 2);
            $local = mb_strtolower($local, 'UTF-8');
            if ($local !== '' && mb_strlen($local) >= 3 && str_contains($lowerPwd, $local)) {
                $errors[] = "Le mot de passe ne doit pas contenir votre e-mail.";
            }
        }

        return $errors;
    }

    // Hash sécurisé (Argon2id si dispo, sinon bcrypt).

    public static function hash(string $password): string
    {
        if (defined('PASSWORD_ARGON2ID')) {
            return password_hash($password, PASSWORD_ARGON2ID, [
                'memory_cost' => 1 << 17, // 131072
                'time_cost'   => 3,
                'threads'     => 2,
            ]);
        }
        // Fallback bcrypt
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    //Vérifie si un rehash est nécessaire.

    public static function needsRehash(string $hash): bool
    {
        if (defined('PASSWORD_ARGON2ID')) {
            return password_needs_rehash($hash, PASSWORD_ARGON2ID, [
                'memory_cost' => 1 << 17,
                'time_cost'   => 3,
                'threads'     => 2,
            ]);
        }
        return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => 12]);
    }

}
