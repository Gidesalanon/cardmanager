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

    /**
     * Retrouve la classe exacte (niveau + série) à partir de l'id choisi dans la liste
     * et du nom de série éventuel. Sans série, l'id reçu est conservé.
     */
    public static function resolveId($classeId, ?string $serieNom = null): int
    {
        $classe = static::findOrFail($classeId);

        if (empty($serieNom)) {
            return $classe->id;
        }

        $serie = Serie::where('nom', $serieNom)->first();

        if (!$serie) {
            return $classe->id;
        }

        $resolved = static::where('nom', $classe->nom)
            ->where('section_id', $classe->section_id)
            ->where('serie_id', $serie->id)
            ->value('id');

        return $resolved ?? $classe->id;
    }
}
