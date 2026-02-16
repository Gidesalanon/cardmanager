<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable = [
        'nom',
        'ecole_id',
    ];

    public function ecole()
    {
        return $this->belongsTo(Ecole::class);
    }

    public function classes()
    {
        return $this->hasMany(Classe::class);
    }
}
