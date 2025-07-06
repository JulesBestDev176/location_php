<?php

class Location
{
    public ?int $IdLocation = null;
    public string $NumeroLocation;
    public int $MontantLocation;
    public string $DateDebut;
    public ?string $DateFin = null;
    public string $DateCreation;
    public bool $Statut = true;
    public ?int $IdAppartement = null;
    public ?int $IdLocataire = null;

    public ?Appartement $Appartement = null;
    public ?Locataire $Locataire = null;
    public array $Paiements = [];

    public function __construct() {
        $this->DateCreation = date('Y-m-d H:i:s');
    }
} 