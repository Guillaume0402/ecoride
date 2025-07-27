<?php

namespace App\Entity;

class Vehicle
{
    private ?int $id;
    private int $userId;
    private string $marque;
    private string $modele;
    private string $couleur;
    private string $immatriculation;
    private string $datePremiereImmatriculation;
    private int $fuelTypeId;
    private int $placesDispo;
    private ?string $createdAt;

    public function __construct(array $data = [])
    {
        $this->hydrate($data);
    }

    private function hydrate(array $data): void
    {
        foreach ($data as $key => $value) {
            $method = 'set' . str_replace('_', '', ucwords($key, '_'));
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getMarque(): string { return $this->marque; }
    public function getModele(): string { return $this->modele; }
    public function getCouleur(): string { return $this->couleur; }
    public function getImmatriculation(): string { return $this->immatriculation; }
    public function getDatePremiereImmatriculation(): string { return $this->datePremiereImmatriculation; }
    public function getFuelTypeId(): int { return $this->fuelTypeId; }
    public function getPlacesDispo(): int { return $this->placesDispo; }
    public function getCreatedAt(): ?string { return $this->createdAt; }

    // Setters
    public function setId(?int $id): void { $this->id = $id; }
    public function setUserId(int $userId): void { $this->userId = $userId; }
    public function setMarque(string $marque): void { $this->marque = $marque; }
    public function setModele(string $modele): void { $this->modele = $modele; }
    public function setCouleur(string $couleur): void { $this->couleur = $couleur; }
    public function setImmatriculation(string $immatriculation): void { $this->immatriculation = $immatriculation; }
    public function setDatePremiereImmatriculation(string $date): void { $this->datePremiereImmatriculation = $date; }
    public function setFuelTypeId(int $id): void { $this->fuelTypeId = $id; }
    public function setPlacesDispo(int $places): void { $this->placesDispo = $places; }
    public function setCreatedAt(?string $createdAt): void { $this->createdAt = $createdAt; }

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
            'fuel_type_id' => $this->fuelTypeId,
            'places_dispo' => $this->placesDispo,
            'created_at' => $this->createdAt,
        ];
    }
}
