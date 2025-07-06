<?php
class Erreur
{
    public string $Message;
    public bool $Valeur;

    public function __construct(string $message, bool $valeur)
    {
        $this->Message = $message;
        $this->Valeur = $valeur;
    }
} 