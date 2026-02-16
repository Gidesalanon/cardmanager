<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ecole extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nom_ecole',
        'telephone',
        'adresse_ecole',
        'numero_autorisation',
    ];

    public function directeur()
    {
        return $this->hasOne(Directeur::class);
    }

    public function eleves()
    {
        return $this->hasMany(Eleve::class);
    }

}
