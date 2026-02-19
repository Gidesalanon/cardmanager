<?php

namespace App\Http\Controllers;
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Eleve;
use App\Models\Classe;
use App\Models\Ecole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;



class AdminStudentImportController extends Controller
{
    public function create()
    {
        return view('admin.eleves.import.create', [
            'classes' => Classe::orderBy('nom')->get()->unique('nom'),
            'ecoles' => Ecole::orderBy('nom_ecole')->get(),
        ]);
    }

    public function preview(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $rows = Excel::toArray([], $request->file('document'))[0] ?? [];

        return response()->json(['rows' => $rows]);
    }

    public function storeAll(Request $request)
    {
        $request->validate([
            'ecole_id' => 'required|exists:ecoles,id',
            'classe_id' => 'required|exists:classes,id',
            'students' => 'required|array'
        ]);

        DB::transaction(function () use ($request) {

            foreach ($request->students as $s) {

                Eleve::create([
                    'ecole_id' => $request->ecole_id,
                    'classe_id' => $request->classe_id,
                    'nom' => $s['nom'],
                    'prenom' => $s['prenom'],
                    'sexe' => $s['sexe'],
                    'date_naissance' => $s['date_naissance'],
                    'lieu_naissance' => $s['lieu_naissance'],
                    'telephone_tuteur' => $s['telephone_tuteur'],
                    'matricule_edumaster' => $s['matricule'],
                ]);
            }
        });

        return response()->json(['success'=>true]);
    }
}
