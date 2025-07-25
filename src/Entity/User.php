<?php

namespace App\Entity;

class User 
{
    private ?int $id = null;
    private string $pseudo;
    private string $email;
    private string $password;
    private int $roleId = 1;
    private int $credits = 20;
    private float $note = 0.00;
    private ?string $photo = null;
    private ?\DateTime $createdAt = null;

    public function __construct(string $pseudo, string $email)
    {
        $this->pseudo = $pseudo;
        $this->email = $email;
        $this->createdAt = new \DateTime();
    }

    // ========== GETTERS ==========
    
    public function getId(): ?int 
    {
        return $this->id;
    }

    public function getPseudo(): string 
    {
        return $this->pseudo;
    }

    public function getEmail(): string 
    {
        return $this->email;
    }

    public function getPassword(): string 
    {
        return $this->password;
    }

    public function getRoleId(): int 
    {
        return $this->roleId;
    }

    public function getCredits(): int 
    {
        return $this->credits;
    }

    public function getNote(): float 
    {
        return $this->note;
    }

    public function getPhoto(): ?string 
    {
        return $this->photo;
    }

    public function getCreatedAt(): ?\DateTime 
    {
        return $this->createdAt;
    }

    // ========== SETTERS ==========
    
    public function setId(int $id): self 
    {
        $this->id = $id;
        return $this;
    }

    public function setPseudo(string $pseudo): self 
    {
        $this->pseudo = $pseudo;
        return $this;
    }

    public function setEmail(string $email): self 
    {
        $this->email = $email;
        return $this;
    }

    public function setPassword(string $password): self 
    {
        $this->password = $password;
        return $this;
    }

    public function setRoleId(int $roleId): self 
    {
        $this->roleId = $roleId;
        return $this;
    }

    public function setCredits(int $credits): self 
    {
        $this->credits = $credits;
        return $this;
    }

    public function setNote(float $note): self 
    {
        $this->note = $note;
        return $this;
    }

    public function setPhoto(?string $photo): self 
    {
        $this->photo = $photo;
        return $this;
    }

    public function setCreatedAt(\DateTime $createdAt): self 
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    // ========== MÉTHODES MÉTIER ==========
    
    /**
     * Hash le mot de passe avant stockage
     */
    public function hashPassword(string $plainPassword): void 
    {
        $this->password = password_hash($plainPassword, PASSWORD_DEFAULT);
    }

    /**
     * Vérifie si le mot de passe fourni correspond au hash stocké
     */
    public function verifyPassword(string $plainPassword): bool 
    {
        return password_verify($plainPassword, $this->password);
    }

    /**
     * Vérifie si l'email est valide
     */
    public function isValidEmail(): bool 
    {
        return filter_var($this->email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Retourne les initiales du pseudo
     */
    public function getInitiales(): string 
    {
        $words = explode(' ', $this->pseudo);
        $initiales = '';
        foreach ($words as $word) {
            $initiales .= strtoupper(substr($word, 0, 1));
        }
        return substr($initiales, 0, 2); // Maximum 2 initiales
    }

    /**
     * Ajoute des crédits au compte utilisateur
     */
    public function addCredits(int $amount): void 
    {
        if ($amount > 0) {
            $this->credits += $amount;
        }
    }

    /**
     * Débite des crédits du compte utilisateur
     */
    public function debitCredits(int $amount): bool 
    {
        if ($amount > 0 && $this->credits >= $amount) {
            $this->credits -= $amount;
            return true;
        }
        return false;
    }

    /**
     * Vérifie si l'utilisateur a suffisamment de crédits
     */
    public function hasEnoughCredits(int $amount): bool 
    {
        return $this->credits >= $amount;
    }

    /**
     * Met à jour la note moyenne de l'utilisateur
     */
    public function updateNote(float $newNote): void 
    {
        if ($newNote >= 0 && $newNote <= 5) {
            $this->note = round($newNote, 2);
        }
    }

    /**
     * Retourne le nom du rôle basé sur l'ID
     */
    public function getRoleName(): string 
    {
        return match($this->roleId) {
            1 => 'Utilisateur',
            2 => 'Employé',
            3 => 'Admin',            
            default => 'Visiteur'
        };
    }

    /**
     * Vérifie si l'utilisateur est admin
     */
    public function isAdmin(): bool 
    {
        return $this->roleId === 4;
    }

    /**
     * Vérifie si l'utilisateur a une photo
     */
    public function hasPhoto(): bool 
    {
        return !empty($this->photo);
    }

    /**
     * Retourne l'URL de la photo ou une image par défaut
     */
    public function getPhotoUrl(): string 
    {
        return $this->photo ?? '/assets/images/default-avatar.png';
    }

    /**
     * Convertit l'entity en tableau (utile pour JSON)
     */
    public function toArray(bool $includePassword = false): array 
    {
        $data = [
            'id' => $this->id,
            'pseudo' => $this->pseudo,
            'email' => $this->email,
            'role_id' => $this->roleId,
            'role_name' => $this->getRoleName(),
            'credits' => $this->credits,
            'note' => $this->note,
            'photo' => $this->photo,
            'photo_url' => $this->getPhotoUrl(),
            'initiales' => $this->getInitiales(),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s')
        ];

        if ($includePassword) {
            $data['password'] = $this->password;
        }

        return $data;
    }

    /**
     * Validation des données de l'utilisateur
     */
    public function validate(): array 
    {
        $errors = [];

        if (empty($this->pseudo) || strlen($this->pseudo) < 3) {
            $errors[] = "Le pseudo doit contenir au moins 3 caractères";
        }

        if (strlen($this->pseudo) > 50) {
            $errors[] = "Le pseudo ne peut pas dépasser 50 caractères";
        }

        if (empty($this->email) || !$this->isValidEmail()) {
            $errors[] = "Email invalide";
        }

        if (strlen($this->email) > 100) {
            $errors[] = "L'email ne peut pas dépasser 100 caractères";
        }

        if ($this->credits < 0) {
            $errors[] = "Les crédits ne peuvent pas être négatifs";
        }

        if ($this->note < 0 || $this->note > 5) {
            $errors[] = "La note doit être entre 0 et 5";
        }

        if (!in_array($this->roleId, [1, 2, 3, 4])) {
            $errors[] = "ID de rôle invalide";
        }

        return $errors;
    }
}