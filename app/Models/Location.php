<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $primaryKey = 'IdLocation';
    protected $table = 'locations';

    protected $fillable = [
        'NumeroLocation',
        'MontantLocation',
        'DateDebut',
        'DateFin',
        'DateCreation',
        'Statut',
        'IdAppartement',
        'IdLocataire'
    ];

    protected $casts = [
        'DateDebut' => 'date',
        'DateFin' => 'date',
        'DateCreation' => 'datetime',
        'Statut' => 'boolean'
    ];

    // Relations
    public function appartement()
    {
        return $this->belongsTo(Appartement::class, 'IdAppartement', 'IdAppartement');
    }

    public function locataire()
    {
        return $this->belongsTo(Locataire::class, 'IdLocataire', 'IdPersonne');
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class, 'IdLocation', 'IdLocation');
    }
} 