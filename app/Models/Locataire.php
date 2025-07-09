<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Locataire extends Model
{
    use HasFactory;

    protected $primaryKey = 'IdPersonne';
    protected $table = 'locataires';
    public $incrementing = false;

    protected $fillable = [
        'IdPersonne',
        'CNI',
        'IdLocation'
    ];

    // Relations
    public function personne()
    {
        return $this->belongsTo(Personne::class, 'IdPersonne', 'IdPersonne');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'IdLocation', 'IdLocation');
    }

    public function locations()
    {
        return $this->hasMany(Location::class, 'IdLocataire', 'IdPersonne');
    }
} 