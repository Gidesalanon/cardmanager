<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Serie extends Model
{
    protected $fillable = ['nom'];

    public function classes()
    {
        return $this->belongsToMany(Classe::class);
    }
}
