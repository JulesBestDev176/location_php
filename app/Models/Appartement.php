<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appartement extends Model
{
    use HasFactory;

    protected $primaryKey = 'IdAppartement';
    protected $table = 'appartements';

    protected $fillable = [
        'AdresseAppartement',
        'Surface',
        'NombrePiece',
        'Capacite',
        'Disponiblite',
        'nbrLocataire',
        'IdProprietaire'
    ];

    protected $casts = [
        'Disponiblite' => 'boolean',
        'Surface' => 'float'
    ];

    // Relations
    public function proprietaire()
    {
        return $this->belongsTo(Proprietaire::class, 'IdProprietaire', 'IdPersonne');
    }

    public function locations()
    {
        return $this->hasMany(Location::class, 'IdAppartement', 'IdAppartement');
    }
} 