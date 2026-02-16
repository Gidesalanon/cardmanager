<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Eleve;
use App\Models\Classe;
use App\Models\SchoolYear;
use Barryvdh\Snappy\Facades\SnappyPdf;

class CardExportController extends Controller
{
    public function student(Eleve $eleve)
    {
        $schoolYear = SchoolYear::active()->firstOrFail();

        $pdf = SnappyPdf::loadView('admin.cards.card', [
            'eleves' => collect([$eleve]),
            'schoolYear' => $schoolYear
        ])
        ->setOption('page-width','8.5cm')
        ->setOption('page-height','5.5cm')
        ->setOption('margin-top',0)
        ->setOption('margin-bottom',0)
        ->setOption('margin-left',0)
        ->setOption('margin-right',0);

        return $pdf->inline('carte-'.$eleve->nom.'.pdf');
    }

    public function classe(Classe $classe)
    {
        $schoolYear = SchoolYear::active()->firstOrFail();

        $eleves = $classe->eleves()->with('ecole.directeur','classe')->get();

        $pdf = SnappyPdf::loadView('admin.cards.card', compact('eleves','schoolYear'))
            ->setOption('page-width','8.5cm')
            ->setOption('page-height','5.5cm')
            ->setOption('margin-top',0)
            ->setOption('margin-bottom',0)
            ->setOption('margin-left',0)
            ->setOption('margin-right',0);

        return $pdf->inline('classe-'.$classe->nom.'.pdf');
    }
}
