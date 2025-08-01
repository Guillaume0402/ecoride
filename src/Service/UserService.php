<?php

namespace App\Service;

use App\Entity\User;

class UserService
{
    // ===========================
    //  Sécurité
    // ===========================
    public function hashPassword(User $user, string $plainPassword): void
    {
        $user->setPassword(password_hash($plainPassword, PASSWORD_DEFAULT));
    }

    public function verifyPassword(User $user, string $plainPassword): bool
    {
        return password_verify($plainPassword, $user->getPassword());
    }

    public function isValidEmail(User $user): bool
    {
        return filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL) !== false;
    }

    // ===========================
    //  Gestion de l'utilisateur
    // ===========================
    public function validate(User $user): array
    {
        $errors = [];

        if (empty($user->getPseudo()) || strlen($user->getPseudo()) < 3) {
            $errors[] = "Le pseudo doit contenir au moins 3 caractères";
        }
        if (strlen($user->getPseudo()) > 50) {
            $errors[] = "Le pseudo ne peut pas dépasser 50 caractères";
        }
        if (empty($user->getEmail()) || !$this->isValidEmail($user)) {
            $errors[] = "Email invalide";
        }
        if (strlen($user->getEmail()) > 100) {
            $errors[] = "L'email ne peut pas dépasser 100 caractères";
        }
        if ($user->getCredits() < 0) {
            $errors[] = "Les crédits ne peuvent pas être négatifs";
        }
        if ($user->getNote() < 0 || $user->getNote() > 5) {
            $errors[] = "La note doit être entre 0 et 5";
        }
        if (!in_array($user->getRoleId(), [1, 2, 3])) {
            $errors[] = "ID de rôle invalide";
        }

        return $errors;
    }

    public function getRoleName(User $user): string
    {
        return match ($user->getRoleId()) {
            1 => 'Utilisateur',
            2 => 'Employé',
            3 => 'Admin',
            default => 'Visiteur'
        };
    }

    public function isAdmin(User $user): bool
    {
        return $user->getRoleId() === 3;
    }

    public function getInitiales(User $user): string
    {
        $words = explode(' ', $user->getPseudo());
        $initiales = '';
        foreach ($words as $word) {
            $initiales .= strtoupper(substr($word, 0, 1));
        }
        return substr($initiales, 0, 2);
    }

    // ===========================
    //  Gestion des crédits
    // ===========================
    public function addCredits(User $user, int $amount): void
    {
        if ($amount > 0) {
            $user->setCredits($user->getCredits() + $amount);
        }
    }

    public function debitCredits(User $user, int $amount): bool
    {
        if ($amount > 0 && $user->getCredits() >= $amount) {
            $user->setCredits($user->getCredits() - $amount);
            return true;
        }
        return false;
    }

    public function hasEnoughCredits(User $user, int $amount): bool
    {
        return $user->getCredits() >= $amount;
    }

    // ===========================
    //  Note utilisateur
    // ===========================
    public function updateNote(User $user, float $newNote): void
    {
        if ($newNote >= 0 && $newNote <= 5) {
            $user->setNote(round($newNote, 2));
        }
    }

    // ===========================
    //  Gestion de la photo
    // ===========================
    public function hasPhoto(User $user): bool
    {
        return !empty($user->getPhoto());
    }

    public function getPhotoUrl(User $user): string
    {
        return $user->getPhoto() ?? '/assets/images/default-avatar.png';
    }

    // ===========================
    //  Conversion en tableau
    // ===========================
    public function toArray(User $user, bool $includePassword = false): array
    {
        $data = [
            'id'          => $user->getId(),
            'pseudo'      => $user->getPseudo(),
            'email'       => $user->getEmail(),
            'role_id'     => $user->getRoleId(),
            'roleId'      => $user->getRoleId(),
            'role_name'   => $this->getRoleName($user),
            'credits'     => $user->getCredits(),
            'note'        => $user->getNote(),
            'photo'       => $user->getPhoto() ?? '/assets/images/logo.svg',
            'photo_url'   => $this->getPhotoUrl($user),
            'initiales'   => $this->getInitiales($user),
            'created_at'  => $user->getCreatedAt()?->format('Y-m-d H:i:s'),
            'travel_role' => $user->getTravelRole(),
        ];

        if ($includePassword) {
            $data['password'] = $user->getPassword();
        }

        return $data;
    }
}
