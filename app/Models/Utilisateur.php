<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Utilisateur extends Model
{
    use HasFactory;

    protected $primaryKey = 'IdPersonne';
    protected $table = 'utilisateurs';
    public $incrementing = false;

    protected $fillable = [
        'IdPersonne',
        'Identifiant',
        'MotDePasse',
        'profil',
        'Statut'
    ];

    protected $hidden = [
        'MotDePasse'
    ];

    // Relations
    public function personne()
    {
        return $this->belongsTo(Personne::class, 'IdPersonne', 'IdPersonne');
    }

    public function administrateur()
    {
        return $this->hasOne(Administrateur::class, 'IdPersonne', 'IdPersonne');
    }
} 