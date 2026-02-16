<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolYear extends Model
{
    protected $fillable = [
        'label',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_active'  => 'boolean',
    ];

    public function inscriptions()
    {
        return $this->hasMany(Inscription::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
