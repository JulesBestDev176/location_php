<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Administrateur extends Model
{
    use HasFactory;

    protected $primaryKey = 'IdPersonne';
    protected $table = 'administrateurs';
    public $incrementing = false;

    protected $fillable = [
        'IdPersonne'
    ];

    // Relations
    public function personne()
    {
        return $this->belongsTo(Personne::class, 'IdPersonne', 'IdPersonne');
    }

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'IdPersonne', 'IdPersonne');
    }
} 