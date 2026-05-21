<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DownloadController extends Controller
{
    public function modeleEleve()
    {
        $path = public_path('assets/modeles/modele_import_eleves.xlsx');

        if (!file_exists($path)) {
            abort(404, 'Le fichier modèle est introuvable.');
        }

        return response()->download(
            $path,
            'modele_import_eleves.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }
}