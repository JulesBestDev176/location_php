<?php
class Td_Erreur
{
    public ?int $Id = null;
    public string $DateErreur;
    public ?string $DescriptionErreur = null;
    public ?string $TitreErreur = null;

    public function __construct() {
        $this->DateErreur = date('Y-m-d H:i:s');
    }
} 