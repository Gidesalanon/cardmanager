<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Eleve extends Model
{
    protected $fillable = [
        'nom',
        'prenom',
        'sexe',
        'nationalite',
        'date_naissance',
        'lieu_naissance',
        'telephone_tuteur',
        'photo',
        'qr_code',
        'matricule_edumaster',
        'classe_id',
        'ecole_id',
    ];

    protected $casts = [
        'date_naissance' => 'date',
    ];

    public function classe()
    {
        return $this->belongsTo(\App\Models\Classe::class);
    }

    public function ecole()
    {
        return $this->belongsTo(Ecole::class);
    }

    public function scopeActiveYear($query)
    {
        return $query->whereHas('ecole', function ($q) {
            $q->whereHas('schoolYears', function ($qy) {
                $qy->where('is_active', true);
            });
        });
    }
}
