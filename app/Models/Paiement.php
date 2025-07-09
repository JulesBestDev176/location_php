<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    use HasFactory;

    protected $primaryKey = 'IdPaiement';
    protected $table = 'paiements';

    protected $fillable = [
        'DatePaiement',
        'MontantPaiement',
        'NumeroFacture',
        'Statut',
        'IdLocation',
        'IdModePaiement'
    ];

    protected $casts = [
        'DatePaiement' => 'datetime',
        'Statut' => 'boolean'
    ];

    // Relations
    public function location()
    {
        return $this->belongsTo(Location::class, 'IdLocation', 'IdLocation');
    }

    public function modePaiement()
    {
        return $this->belongsTo(ModePaiement::class, 'IdModePaiement', 'IdModePaiement');
    }
} 