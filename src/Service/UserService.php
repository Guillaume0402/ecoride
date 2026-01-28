<?php

namespace App\Service;

use App\Entity\UserEntity;
use App\Security\PasswordPolicy;

// Service métier autour de l'utilisateur :
// - gestion des mots de passe
// - validation des données
// - gestion des crédits et de la note
// - aide pour l'affichage (photo, initiales, rôle, etc.)
class UserService
{
    // Gestion des mots de passe 
    public function hashPassword(UserEntity $user, string $plainPassword): void
    {
        // Hash via la politique centralisée (Argon2id si dispo, sinon bcrypt cost 12)
        $user->setPassword(PasswordPolicy::hash($plainPassword));
    }


    public function verifyPassword(UserEntity $user, string $plainPassword): bool
    {
        // Compare un mot de passe en clair avec le hash stocké
        return password_verify($plainPassword, $user->getPassword());
    }

    public function needsRehash(UserEntity $user): bool
    {
        // Permet de savoir si le hash actuel est encore conforme à la politique (algo/cost)
        return PasswordPolicy::needsRehash($user->getPassword());
    }

    /**
     * Retourne un NOUVEAU hash conforme à la politique actuelle
     * (la sauvegarde en base sera faite à l’étape suivante).
     */
    public function rehash(UserEntity $user, string $plainPassword): string
    {
        return PasswordPolicy::hash($plainPassword);
    }

    public function isValidEmail(UserEntity $user): bool
    {
        // Validation simple via filter_var
        return filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL) !== false;
    }

    // Validation des données utilisateur
    //  Rassemble toutes les règles de cohérence métier (longueurs, formats, bornes...)
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

    // Gestion du rôle et des droits
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

    // Aide pour l'affichage (initiales, photo, etc.)
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

    // Gestion des crédits (porte-monnaie)
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

    // Note / réputation de l'utilisateur
    public function updateNote(UserEntity $user, float $newNote): void
    {
        // Contraint la note à [0,5] et arrondit à 2 décimales
        if ($newNote >= 0 && $newNote <= 5) {
            $user->setNote(round($newNote, 2));
        }
    }

    // Gestion de la photo de profil
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
