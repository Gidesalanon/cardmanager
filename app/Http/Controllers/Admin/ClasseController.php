<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classe;

class ClasseController extends Controller
{
    public function index()
    {
        $classes = Classe::with(['section', 'serie'])
            ->orderBy('section_id')
            ->orderBy('nom')
            ->get();

        return view('admin.classes.index', compact('classes'));
    }
}

