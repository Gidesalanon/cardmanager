<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class AnneeScolaire extends Model
{
    protected $fillable = [
        'libelle',      // ex: 2025-2026
        'date_debut',
        'date_fin',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'date_debut' => 'date',
        'date_fin' => 'date',
    ];

    // 🔗 Relations
    public function directions()
    {
        return $this->hasMany(Direction::class);
    }

    //  Scope : année active
    public function scopeActive(Builder $query)
    {
        return $query->where('active', true);
    }
}
