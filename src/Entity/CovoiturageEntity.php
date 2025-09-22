<?php

namespace App\Entity;

class CovoiturageEntity
{
    private ?int $id = null;
    private int $driverId;
    private int $vehicleId;
    private string $adresseDepart;
    private string $adresseArrivee;
    private string $depart;   // Y-m-d H:i:s
    private string $arrivee;  // Y-m-d H:i:s
    private float $prix;
    private string $status = 'en_attente';

    public function __construct(array $data = [])
    {
        foreach ($data as $k => $v) {
            $method = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $k)));
            if (method_exists($this, $method)) {
                $this->$method($v);
            }
        }
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getDriverId(): int
    {
        return $this->driverId;
    }
    public function getVehicleId(): int
    {
        return $this->vehicleId;
    }
    public function getAdresseDepart(): string
    {
        return $this->adresseDepart;
    }
    public function getAdresseArrivee(): string
    {
        return $this->adresseArrivee;
    }
    public function getDepart(): string
    {
        return $this->depart;
    }
    public function getArrivee(): string
    {
        return $this->arrivee;
    }
    public function getPrix(): float
    {
        return $this->prix;
    }
    public function getStatus(): string
    {
        return $this->status;
    }

    // Setters
    public function setId(?int $id): void
    {
        $this->id = $id;
    }
    public function setDriverId(int $id): void
    {
        $this->driverId = $id;
    }
    public function setVehicleId(int $id): void
    {
        $this->vehicleId = $id;
    }
    public function setAdresseDepart(string $v): void
    {
        $this->adresseDepart = $v;
    }
    public function setAdresseArrivee(string $v): void
    {
        $this->adresseArrivee = $v;
    }
    public function setDepart(string $dt): void
    {
        $this->depart = $dt;
    }
    public function setArrivee(string $dt): void
    {
        $this->arrivee = $dt;
    }
    public function setPrix(float $p): void
    {
        $this->prix = $p;
    }
    public function setStatus(string $s): void
    {
        $this->status = $s;
    }
}
