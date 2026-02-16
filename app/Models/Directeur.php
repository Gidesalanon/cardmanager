<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Directeur extends Model
{
    use HasFactory;

    protected $fillable = [
        'ecole_id',
        'nom',
        'prenom',
        'sexe',
        'telephone',
        'email',
        'signature',
        'cachet',
    ];

    public function ecole()
    {
        return $this->belongsTo(Ecole::class);
    }
}
