<?php
require_once 'Personne.php';

class Locataire extends Personne
{
    public ?int $IdLocation = null;
    public string $CNI;

    public array $Locations = [];
} 