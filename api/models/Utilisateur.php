<?php
require_once 'Personne.php';

class Utilisateur extends Personne
{
    public string $Identifiant;
    public string $MotDePasse;
    public ?string $profil = null;
    public ?string $Statut = null;
} 