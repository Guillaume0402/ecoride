<?php

namespace App\Entity;

class VehicleEntity
{
    private ?int $id;
    private int $userId;
    private string $marque;
    private string $modele;
    private string $couleur;
    private string $immatriculation;
    private string $datePremiereImmatriculation;
    private int $fuelTypeId;
    private ?string $fuelTypeName = null;
    private int $placesDispo;
    private ?\DateTimeImmutable $createdAt;
    private ?string $preferences = null;
    private ?string $customPreferences = null;

    public function __construct(array $data = [])
    {
        $this->hydrate($data);
    }

    private function hydrate(array $data): void
    {
        foreach ($data as $key => $value) {
            // Convertit custom_preferences → CustomPreferences
            $method = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));

            if (method_exists($this, $method)) {
                if ($key === 'created_at' && $value) {
                    $value = new \DateTimeImmutable($value);
                }

                $this->$method($value);
            }
        }
    }



    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getUserId(): int
    {
        return $this->userId;
    }
    public function getMarque(): string
    {
        return $this->marque;
    }
    public function getModele(): string
    {
        return $this->modele;
    }
    public function getCouleur(): string
    {
        return $this->couleur;
    }
    public function getImmatriculation(): string
    {
        return $this->immatriculation;
    }
    public function getDatePremiereImmatriculation(): string
    {
        return $this->datePremiereImmatriculation;
    }
    public function getFuelTypeId(): int
    {
        return $this->fuelTypeId;
    }
    public function getFuelTypeName(): ?string
    {
        return $this->fuelTypeName;
    }
    public function getPlacesDispo(): int
    {
        return $this->placesDispo;
    }
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
    public function getPreferences(): ?string
    {
        return $this->preferences;
    }
    public function getCustomPreferences(): ?string
    {
        return $this->customPreferences;
    }


    // Setters
    public function setId(?int $id): void
    {
        $this->id = $id;
    }
    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }
    public function setMarque(string $marque): void
    {
        $this->marque = $marque;
    }
    public function setModele(string $modele): void
    {
        $this->modele = $modele;
    }
    public function setCouleur(string $couleur): void
    {
        $this->couleur = $couleur;
    }
    public function setImmatriculation(string $immatriculation): void
    {
        $this->immatriculation = $immatriculation;
    }
    public function setDatePremiereImmatriculation(string $date): void
    {
        $this->datePremiereImmatriculation = $date;
    }
    public function setFuelTypeId(int $id): void
    {
        $this->fuelTypeId = $id;
    }
    public function setFuelTypeName(?string $name): void
    {
        $this->fuelTypeName = $name;
    }
    public function setPlacesDispo(int $places): void
    {
        $this->placesDispo = $places;
    }
    public function setCreatedAt(string|\DateTimeImmutable|null $createdAt): void
    {
        if ($createdAt instanceof \DateTimeImmutable) {
            $this->createdAt = $createdAt;
        } else {
            $this->createdAt = $createdAt ? new \DateTimeImmutable($createdAt) : null;
        }
    }
    public function setPreferences(?string $preferences): void
    {
        $this->preferences = $preferences;
    }
    public function setCustomPreferences(?string $customPreferences): void
    {
        $this->customPreferences = $customPreferences;
    }


    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'marque' => $this->marque,
            'modele' => $this->modele,
            'couleur' => $this->couleur,
            'immatriculation' => $this->immatriculation,
            'date_premiere_immatriculation' => $this->datePremiereImmatriculation,
            'fuel_type_name' => $this->fuelTypeName,
            'places_dispo' => $this->placesDispo,
            'created_at' => $this->createdAt,
            'preferences' => $this->preferences,
            'custom_preferences' => $this->customPreferences
        ];
    }
}
