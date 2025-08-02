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
    private ?\DateTimeImmutable $createdAt = null;
    private string $travelRole = 'passager';
    private int $isActive = 1;

    //  Constructeur accepte directement un tableau pour l'hydratation
    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->hydrate($data);
        }
        if (!$this->createdAt) {
            $this->createdAt = new \DateTimeImmutable();
        }
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
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
    public function getTravelRole(): string
    {
        return $this->travelRole;
    }
    public function getIsActive(): int
    {
        return $this->isActive;
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
    public function setCredits(?int $credits = 0): self
    {
        $this->credits = $credits ?? 0;
        return $this;
    }
    public function setNote(?float $note = 0): self
    {
        $this->note = $note ?? 0;
        return $this;
    }
    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;
        return $this;
    }
    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
    public function setTravelRole(string $role): self
    {
        $this->travelRole = $role;
        return $this;
    }
    public function setIsActive(int $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    //  Hydratation automatique
    private function hydrate(array $data): void
    {
        foreach ($data as $key => $value) {
            $method = 'set' . str_replace('_', '', ucwords($key, '_'));
            if (method_exists($this, $method)) {
                if ($key === 'created_at' && $value) {
                    $value = new \DateTimeImmutable($value);
                }
                $this->$method($value);
            }
        }
    }
}
