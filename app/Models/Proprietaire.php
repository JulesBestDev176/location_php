<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proprietaire extends Model
{
    use HasFactory;

    protected $primaryKey = 'IdPersonne';
    protected $table = 'proprietaires';
    public $incrementing = false;

    protected $fillable = [
        'IdPersonne',
        'Ninea',
        'Rccm'
    ];

    // Relations
    public function personne()
    {
        return $this->belongsTo(Personne::class, 'IdPersonne', 'IdPersonne');
    }

    public function appartements()
    {
        return $this->hasMany(Appartement::class, 'IdProprietaire', 'IdPersonne');
    }
} 