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
        'numero_table',
        'classe_id',
        'division',
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

    /**
     * Division / groupe facultatif (M1, M2, A, B...) : espaces nettoyés, majuscules,
     * null si vide. Une seule règle pour les formulaires, les imports et les exports.
     */
    public static function normaliserDivision($value): ?string
    {
        if (is_int($value) || is_float($value)) {
            $value = (string) $value;
        }

        if (!is_string($value)) {
            return null;
        }

        $clean = preg_replace('/\s+/u', ' ', $value);

        if ($clean === null) { // texte non UTF-8 : on ignore la valeur
            return null;
        }

        $clean = trim($clean);

        return $clean === '' ? null : mb_strtoupper($clean);
    }

    public function setDivisionAttribute($value): void
    {
        $this->attributes['division'] = self::normaliserDivision($value);
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