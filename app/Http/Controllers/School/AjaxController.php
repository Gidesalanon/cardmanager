<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\Partition;
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
     * Partitions selon classe + série (ou classe seule)
     */
    public function partitions(Request $request)
    {
        $query = Partition::where('classe_id', $request->classe_id);

        return $query->orderBy('nom')->get();
    }
}
