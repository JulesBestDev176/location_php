<?php

class Appartement
{
    public ?int $IdAppartement = null;
    public string $AdresseAppartement;
    public ?float $Surface = null;
    public ?int $NombrePiece = null;
    public int $Capacite;
    public bool $Disponiblite = true;
    public int $nbrLocataire = 0;
    public ?int $IdProprietaire = null;

    public ?Proprietaire $Proprietaire = null;
    public array $Locations = [];
} 