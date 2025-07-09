<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Personne extends Model
{
    use HasFactory;

    protected $primaryKey = 'IdPersonne';
    protected $table = 'personnes';

    protected $fillable = [
        'Nom',
        'Prenom',
        'Telephone',
        'Email'
    ];

    // Relations
    public function utilisateur()
    {
        return $this->hasOne(Utilisateur::class, 'IdPersonne', 'IdPersonne');
    }

    public function administrateur()
    {
        return $this->hasOne(Administrateur::class, 'IdPersonne', 'IdPersonne');
    }

    public function proprietaire()
    {
        return $this->hasOne(Proprietaire::class, 'IdPersonne', 'IdPersonne');
    }

    public function locataire()
    {
        return $this->hasOne(Locataire::class, 'IdPersonne', 'IdPersonne');
    }
} 