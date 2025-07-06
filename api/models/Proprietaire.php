<?php
require_once 'Personne.php';

class Proprietaire extends Personne
{
    public string $Ninea;
    public string $Rccm;

    public array $Appartements = [];
} 