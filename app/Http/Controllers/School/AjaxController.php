<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\Partition;
use App\Models\Serie;
use Illuminate\Http\Request;

class AjaxController extends Controller
{
    /**
     * Classes selon la section
     */
    public function classesBySection(Request $request)
    {
        return Classe::where('section_id', $request->section_id)
            ->with('serie')
            ->orderBy('nom')
            ->get();
    }

    /**
     * Séries pour une classe secondaire
     */
    public function seriesByClasse(Request $request)
    {
        return Serie::whereHas('classes', function ($q) use ($request) {
            $q->where('classes.id', $request->classe_id);
        })->get();
    }

    /**
     * Partitions selon classe + série (ou classe seule)
     */
    public function partitions(Request $request)
    {
        $query = Partition::where('classe_id', $request->classe_id);

        return $query->orderBy('nom')->get();
    }
}
