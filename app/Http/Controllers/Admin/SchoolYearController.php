<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolYear;
use Illuminate\Http\Request;

class SchoolYearController extends Controller
{
    public function index()
    {
        $schoolYears = SchoolYear::orderByDesc('id')->get();
        return view('admin.school-years.index', compact('schoolYears'));
    }

    public function create()
    {
        return view('admin.school-years.create');
    }

    public function store(Request $request)
    {
        // Validation de base
        $request->validate([
            'label'      => 'required|string|max:50',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
        ]);

        $label = $request->input('label');       // ex: 2024-2025
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Vérification si l'année scolaire existe déjà
        if (SchoolYear::where('label', $label)->exists()) {
            return redirect()->back()
                ->withInput()
                ->with('error', "L'année scolaire '$label' existe déjà !");
        }

        // Vérification cohérence label / dates
        [$yearStart, $yearEnd] = explode('-', $label); // ex: 2024-2025
        if ((int)$yearStart != date('Y', strtotime($startDate)) ||
            (int)$yearEnd   != date('Y', strtotime($endDate))) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Les dates ne correspondent pas au label '$label'.");
        }

        // Enregistrement
        SchoolYear::create([
            'label'      => $label,
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'is_active'  => false,
        ]);

        return redirect()
            ->route('admin.school-years.index')
            ->with('success', "Année scolaire '$label' créée avec succès.");
    }

    public function edit(SchoolYear $schoolYear)
    {
        return view('admin.school-years.edit', compact('schoolYear'));
    }

    public function update(Request $request, SchoolYear $schoolYear)
    {
        $request->validate([
            'label'      => 'required|string|max:50',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
        ]);

        $label = $request->input('label');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Vérifier doublon (sauf sur l'année courante)
        if (SchoolYear::where('label', $label)->where('id', '!=', $schoolYear->id)->exists()) {
            return redirect()->back()
                ->withInput()
                ->with('error', "L'année scolaire '$label' existe déjà !");
        }

        // Vérification cohérence label / dates
        [$yearStart, $yearEnd] = explode('-', $label);
        if ((int)$yearStart != date('Y', strtotime($startDate)) ||
            (int)$yearEnd   != date('Y', strtotime($endDate))) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Les dates ne correspondent pas au label '$label'.");
        }

        // Mise à jour
        $schoolYear->update([
            'label'      => $label,
            'start_date' => $startDate,
            'end_date'   => $endDate,
        ]);

        return redirect()
            ->route('admin.school-years.index')
            ->with('success', "Année scolaire '$label' mise à jour avec succès.");
    }

    /**
     * Activer / désactiver
     */
    public function toggle(SchoolYear $schoolYear)
    {
        // Désactiver toutes les autres
        SchoolYear::where('id', '!=', $schoolYear->id)
            ->update(['is_active' => false]);

        $schoolYear->update([
            'is_active' => !$schoolYear->is_active,
        ]);

        $message = $schoolYear->is_active
            ? "Année scolaire '{$schoolYear->label}' activée avec succès."
            : "Année scolaire '{$schoolYear->label}' désactivée.";

        return back()->with('success', $message);
    }
}
