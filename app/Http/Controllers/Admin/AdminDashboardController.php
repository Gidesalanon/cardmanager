<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class AdminDashboardController extends Controller
{
    public function index()
{
    $totalEcoles = \App\Models\Ecole::count();
    $totalUsers = \App\Models\User::count();
    $totalEleves = \App\Models\Eleve::count();

    // Classes réellement utilisées (via élèves)
    $totalClasses = \App\Models\Classe::whereHas('eleves')->count();

    // Top 5 écoles par effectif
    $topEcoles = \App\Models\Ecole::withCount('eleves')
        ->orderByDesc('eleves_count')
        ->take(5)
        ->get();

    // Répartition garçons / filles plateforme
    $filles = \App\Models\Eleve::where('sexe','F')->count();
    $garcons = \App\Models\Eleve::where('sexe','M')->count();

    return view('admin.dashboard', compact(
        'totalEcoles',
        'totalUsers',
        'totalEleves',
        'totalClasses',
        'topEcoles',
        'filles',
        'garcons'
    ));
}

}
