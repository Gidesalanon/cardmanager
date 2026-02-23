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
use App\Http\Controllers\Admin\AdminStudentImportController;
use App\Http\Controllers\DownloadController;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| La route pour le téléchargement du modele
|--------------------------------------------------------------------------
*/

Route::get('/telecharger-modele-eleve', [DownloadController::class, 'modeleEleve'])
    ->name('modele.eleve.download');


/*
|--------------------------------------------------------------------------
| ACCUEIL & GUEST
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
});

// GOOGLE AUTH
Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('google.login');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback']);


/*
|--------------------------------------------------------------------------
| AUTHENTIFIÉS
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    // LOGOUT (SÉCURITÉ : On définit la route explicitement ici au cas où auth.php bug)
    Route::post('logout', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    // PROFIL
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::delete('/profile/avatar', [ProfileController::class, 'destroyAvatar'])->name('profile.avatar.destroy');

    // DASHBOARD REDIRECTION
    Route::get('/dashboard', function () {
        return Auth::user()->usertype === 'admin'
            ? redirect()->route('admin.dashboard')
            : redirect()->route('school.dashboard');
    })->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | ADMIN
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->middleware(['admin'])->name('admin.')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('school-years', SchoolYearController::class);
        Route::post('school-years/{schoolYear}/toggle', [SchoolYearController::class, 'toggle'])->name('school-years.toggle');
        Route::get('classes', [ClasseController::class, 'index'])->name('classes.index');

        // ADMIN - GESTION ÉLÈVES
        Route::resource('eleves', AdminStudentController::class)->parameters(['eleves' => 'eleve'])->names('students');
        Route::get('/students/{eleve}/export-card-pdf', [AdminStudentController::class, 'exportCardPdf'])->name('students.export.card.pdf');
        Route::get('/students/{eleve}/export-card-image', [AdminStudentController::class, 'exportCardImage'])->name('students.export.card.image');
        Route::get('/students/export-class-cards', [AdminStudentController::class, 'exportClassCardsPdf'])->name('students.export.class.cards');

        // ADMIN - IMPORT
        Route::get('eleves/import', [AdminStudentImportController::class, 'create'])->name('students.import.create');
        Route::post('eleves/import/preview', [AdminStudentImportController::class, 'preview'])->name('students.import.preview');
        Route::post('eleves/import/store-all', [AdminStudentImportController::class, 'storeAll'])->name('students.import.storeAll');
        Route::get('/profile', [App\Http\Controllers\Admin\AdminProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [App\Http\Controllers\Admin\AdminProfileController::class, 'update'])->name('profile.update');
    });
});

/*
    |--------------------------------------------------------------------------
    | ECOLE
    |--------------------------------------------------------------------------
    */
Route::prefix('ecole')->middleware(['ecole'])->name('school.')->group(function () {
    Route::get('/', [SchoolDashboardController::class, 'index'])->name('dashboard');

    // MON ECOLE
    Route::get('mon-ecole/create', [EcoleController::class, 'create'])->name('ecole.create');
    Route::post('mon-ecole', [EcoleController::class, 'store'])->name('ecole.store');
    Route::get('mon-ecole', [EcoleController::class, 'show'])->name('ecole.show');

        // IMPORT ÉLÈVES
        Route::get('eleves/import', [StudentImportController::class, 'create'])->name('students.import.create');
        Route::post('eleves/import/preview', [StudentImportController::class, 'preview'])->name('students.import.preview');
        Route::post('eleves/import/store-all', [StudentImportController::class, 'storeAll'])->name('students.import.storeAll');
        Route::get('/school/eleves/import/canvas', [StudentImportController::class, 'downloadCanvas'])->name('school.eleves.import.canvas');
        // GESTION ÉLÈVES
        Route::resource('eleves', StudentController::class)->parameters(['eleves' => 'eleve'])->names('students');

    // CARTES
    Route::get('eleves/voir-cartes', [StudentController::class, 'viewCards'])->name('students.viewCards');
    Route::get('eleves/{eleve}/download-card', [StudentController::class, 'downloadCard'])->name('students.downloadCard');

    // AJAX
    Route::get('ajax/classes', [AjaxController::class, 'classesBySection']);
    Route::get('ajax/series', [AjaxController::class, 'seriesByClasse']);
    Route::get('ajax/partitions', [AjaxController::class, 'partitions']);
});


require __DIR__ . '/auth.php';
