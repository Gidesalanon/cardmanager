<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;

class SchoolDashboardController extends Controller
{
     public function index()
{

    return view('school.dashboard');
}

}

