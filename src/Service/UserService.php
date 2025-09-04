<?php

namespace App\Service;

use App\Entity\UserEntity;

class UserService
{
    // Service métier côté utilisateur: sécurité, rôles, crédits, conversions
    // ===========================
    //  Sécurité
    // ===========================
    public function hashPassword(UserEntity $user, string $plainPassword): void
    {
        // Hash sécurisé avec l'algorithme par défaut de PHP
        $user->setPassword(password_hash($plainPassword, PASSWORD_DEFAULT));
    }

    public function verifyPassword(UserEntity $user, string $plainPassword): bool
    {
        // Compare un mot de passe en clair avec le hash stocké
        return password_verify($plainPassword, $user->getPassword());
    }

    public function isValidEmail(UserEntity $user): bool
    {
        // Validation simple via filter_var
        return filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL) !== false;
    }

    // ===========================
    //  Gestion de l'utilisateur
    // ===========================
    public function validate(UserEntity $user): array
    {
        // Rassemble des messages d'erreurs de validation (sans side effects)
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

    public function getRoleName(UserEntity $user): string
    {
        // Mappe role_id -> libellé human-readable
        return match ($user->getRoleId()) {
            1 => 'Utilisateur',
            2 => 'Employé',
            3 => 'Admin',
            default => 'Visiteur'
        };
    }

    public function isAdmin(UserEntity $user): bool
    {
        // Raccourci pour vérifier le rôle admin
        return $user->getRoleId() === 3;
    }

    public function getInitiales(UserEntity $user): string
    {
        // Prend les premières lettres de chaque mot du pseudo (max 2)
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
    public function addCredits(UserEntity $user, int $amount): void
    {
        // Ajoute des crédits si le montant est positif
        if ($amount > 0) {
            $user->setCredits($user->getCredits() + $amount);
        }
    }

    public function debitCredits(UserEntity $user, int $amount): bool
    {
        // Débite si le solde est suffisant; retourne true si succès
        if ($amount > 0 && $user->getCredits() >= $amount) {
            $user->setCredits($user->getCredits() - $amount);
            return true;
        }
        return false;
    }

    public function hasEnoughCredits(UserEntity $user, int $amount): bool
    {
        // Vérifie le solde par rapport à un montant donné
        return $user->getCredits() >= $amount;
    }

    // ===========================
    //  Note utilisateur
    // ===========================
    public function updateNote(UserEntity $user, float $newNote): void
    {
        // Contraint la note à [0,5] et arrondit à 2 décimales
        if ($newNote >= 0 && $newNote <= 5) {
            $user->setNote(round($newNote, 2));
        }
    }

    // ===========================
    //  Gestion de la photo
    // ===========================
    public function hasPhoto(UserEntity $user): bool
    {
        // True si un chemin de photo est défini
        return !empty($user->getPhoto());
    }

    public function getPhotoUrl(UserEntity $user): string
    {
        // Retourne l'URL de la photo ou un avatar par défaut
        return $user->getPhoto() ?? '/assets/images/default-avatar.png';
    }

    // ===========================
    //  Conversion en tableau
    // ===========================
    public function toArray(UserEntity $user, bool $includePassword = false): array
    {
        // Sérialise l'entité en tableau pour la vue/API (password optionnel)
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
            // Attention: n'exposez le hash que si nécessaire
            $data['password'] = $user->getPassword();
        }

        return $data;
    }
}
