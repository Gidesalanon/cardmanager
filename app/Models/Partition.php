<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partition extends Model
{
    protected $fillable = [
        'nom',
        'classe_id',
    ];

    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }

    public function eleves()
    {
        return $this->hasMany(Eleve::class);
    }
}
