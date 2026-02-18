<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Admin\SchoolYearController;
use App\Http\Controllers\Admin\ClasseController;
use App\Http\Controllers\School\SchoolDashboardController;
use App\Http\Controllers\School\EcoleController;
use App\Http\Controllers\School\StudentController;
use App\Http\Controllers\School\StudentImportController;
use App\Http\Controllers\School\AjaxController;
use App\Http\Controllers\Admin\AdminStudentController;

/*
|--------------------------------------------------------------------------
| ACCUEIL
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
})->name('home');

/*
|--------------------------------------------------------------------------
| AUTHENTIFIÉS
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // PROFIL
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    |--------------------------------------------------------------------------
    | ADMIN
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')
        ->middleware(['admin'])
        ->name('admin.')
        ->group(function () {

            Route::get('/', [AdminDashboardController::class, 'index'])
                ->name('dashboard');

            Route::resource('school-years', SchoolYearController::class);
            Route::post('school-years/{schoolYear}/toggle',
                [SchoolYearController::class, 'toggle'])
                ->name('school-years.toggle');

            Route::get('classes', [ClasseController::class, 'index'])
                ->name('classes.index');

                            /*
            |--------------------------------------------------------------------------
            | ADMIN - GESTION ÉLÈVES
            |--------------------------------------------------------------------------
            */

            Route::get('eleves', [\App\Http\Controllers\Admin\AdminStudentController::class, 'index'])
                ->name('students.index');

            Route::get('eleves/create', [\App\Http\Controllers\Admin\AdminStudentController::class, 'create'])
                ->name('students.create');

            Route::post('eleves', [\App\Http\Controllers\Admin\AdminStudentController::class, 'store'])
                ->name('students.store');

            Route::get('eleves/{eleve}/edit', [\App\Http\Controllers\Admin\AdminStudentController::class, 'edit'])
                ->name('students.edit');

            Route::put('eleves/{eleve}', [\App\Http\Controllers\Admin\AdminStudentController::class, 'update'])
                ->name('students.update');

            Route::delete('eleves/{eleve}', [\App\Http\Controllers\Admin\AdminStudentController::class, 'destroy'])
                ->name('students.destroy');


            /*
            |--------------------------------------------------------------------------
            | ADMIN - IMPORT
            |--------------------------------------------------------------------------
            */

            Route::get('eleves/import', [\App\Http\Controllers\Admin\AdminStudentImportController::class, 'create'])
                ->name('students.import.create');

            Route::post('eleves/import/preview', [\App\Http\Controllers\Admin\AdminStudentImportController::class, 'preview'])
                ->name('students.import.preview');

            Route::post('eleves/import/store-all', [\App\Http\Controllers\Admin\AdminStudentImportController::class, 'storeAll'])
                ->name('students.import.storeAll');
 
            Route::get('/students/{eleve}/export-card-pdf', 
                [AdminStudentController::class, 'exportCardPdf']
            )->name('students.export.card.pdf');

            Route::get('/students/{eleve}/export-card-image', 
                [AdminStudentController::class, 'exportCardImage']
            )->name('students.export.card.image');

            Route::get('/students/export-class-cards', 
                [AdminStudentController::class, 'exportClassCardsPdf']
            )->name('students.export.class.cards');

                });

    /*
    |--------------------------------------------------------------------------
    | ECOLE
    |--------------------------------------------------------------------------
    */
    Route::prefix('ecole')
        ->middleware(['ecole'])
        ->name('school.')
        ->group(function () {

            Route::get('/', [SchoolDashboardController::class, 'index'])
                ->name('dashboard');

            // MON ECOLE
            Route::get('mon-ecole/create', [EcoleController::class, 'create'])
                ->name('ecole.create');

            Route::post('mon-ecole', [EcoleController::class, 'store'])
                ->name('ecole.store');

            Route::get('mon-ecole', [EcoleController::class, 'show'])
                ->name('ecole.show');

            // IMPORT ÉLÈVES
            Route::get('eleves/import',
                [StudentImportController::class, 'create'])
                ->name('students.import.create');

            Route::post('eleves/import/preview',
                [StudentImportController::class, 'preview'])
                ->name('students.import.preview');

            Route::post('eleves/import/store-all',
                [StudentImportController::class, 'storeAll'])
                ->name('students.import.storeAll');

            Route::resource('eleves', StudentController::class)
                ->parameters(['eleves' => 'eleve']) // 🔥 CORRECTION ICI
                ->names('students');

            // AJAX
            Route::get('ajax/classes', [AjaxController::class, 'classesBySection']);
            Route::get('ajax/series', [AjaxController::class, 'seriesByClasse']);
            Route::get('ajax/partitions', [AjaxController::class, 'partitions']);
        });
});

/*
|--------------------------------------------------------------------------
| GUEST
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])
        ->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
});

// GOOGLE AUTH
Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])
    ->name('google.login');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback']);
/*
|--------------------------------------------------------------------------
| REDIRECTION APRÈS CONNEXION / VÉRIFICATION
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    $user = Auth::user();

    // Redirection vers l'admin si l'utilisateur est admin
    // (Adapte 'usertype' ou la condition selon ta base de données)
    if ($user->usertype === 'admin') { 
        return redirect()->route('admin.dashboard');
    }

    // Sinon redirection vers l'école
    return redirect()->route('school.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
require __DIR__.'/auth.php';
