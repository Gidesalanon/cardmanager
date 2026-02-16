<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classe extends Model
{
    protected $fillable = [
        'section_id',
        'serie_id',
        'nom',
    ];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function serie()
    {
        return $this->belongsTo(Serie::class);
    }

    public function partitions()
    {
        return $this->hasMany(Partition::class);
    }

    public function eleves()
    {
        return $this->hasMany(\App\Models\Eleve::class);
    }

}
