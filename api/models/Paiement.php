<?php

class Paiement
{
    public ?int $IdPaiement = null;
    public ?string $DatePaiement = null;
    public int $MontantPaiement;
    public string $NumeroFacture;
    public bool $Statut;
    public ?int $IdLocation = null;
    public ?int $IdModePaiement = null;

    public ?Location $Location = null;
    public ?ModePaiement $ModePaiement = null;
} 