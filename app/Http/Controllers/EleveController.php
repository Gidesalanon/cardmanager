<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Eleve;
use App\Models\Classe;

class EleveController extends Controller
{
    public function index()
    {
        $ecole = auth()->user()->ecole;

        $eleves = Eleve::with('classe')
            ->where('ecole_id', $ecole->id)
            ->get();

        return view('school.eleves.index', compact('eleves'));
    }

    public function create()
    {
        $ecole = auth()->user()->ecole;
        $classes = Classe::where('ecole_id', $ecole->id)->get();

        return view('school.eleves.create', compact('classes'));
    }

    public function store(Request $request)
    {
        
        $request->validate([
            'nom' => 'required',
            'prenom' => 'required',
            'classe_id' => 'required|exists:classes,id',
            'matricule_edumaster' => 'required|unique:eleves,matricule_edumaster',
            'photo' => 'required|image|mimes:jpg,jpeg,png|max:2048', // 2MB max
        ]);

        Eleve::create([
            ...$request->all(),
            'ecole_id' => auth()->user()->ecole->id,
        ]);

        return redirect()->route('school.students.index')
            ->with('success', 'Élève enregistré');
    }
}
