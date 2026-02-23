<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    public function modeleEleve()
    {
        $path = storage_path('app/public/modeles/Model-fichier-import_eleve.xlsx');

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->download($path, 'Model-fichier-import_eleve.xlsx');
    }
}
