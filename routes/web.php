<?php

use App\Http\Controllers\Admin\AdminEcoleController;
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

/*
|--------------------------------------------------------------------------
| TÉLÉCHARGEMENT MODÈLE
|--------------------------------------------------------------------------
*/
Route::get('/telecharger-modele-eleve', [DownloadController::class, 'modeleEleve'])
    ->name('modele.eleve.download');

/*
|--------------------------------------------------------------------------
| VITRINE (accessible à tous)
|--------------------------------------------------------------------------
*/
Route::get('/', fn() => view('welcome'))->name('home');
Route::get('/services', fn() => view('services'))->name('services');
Route::get('/a-propos', fn() => view('apropos'))->name('apropos');
Route::get('/contact', fn() => view('contact'))->name('contact.show');

Route::post('/contact', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'nom'       => 'required|string|max:255',
        'email'     => 'nullable|email|max:255',
        'telephone' => 'nullable|string|max:20',
        'sujet'     => 'required|string',
        'message'   => 'required|string|min:10',
    ], [
        'nom.required'     => 'Votre nom est obligatoire.',
        'message.required' => 'Le message est obligatoire.',
        'message.min'      => 'Le message doit contenir au moins 10 caractères.',
        'email.email'      => 'Adresse email invalide.',
    ]);

    try {
        \Illuminate\Support\Facades\Mail::to('contact@donami.bj')
            ->send(new \App\Mail\ContactMail(
                $request->nom,
                $request->email ?? '',
                $request->telephone ?? '',
                $request->sujet,
                $request->message
            ));

        return redirect()->back()->with('success', 'Votre message a bien été envoyé ! Nous vous répondrons dans les plus brefs délais.');

    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Erreur envoi mail contact: ' . $e->getMessage());
        return redirect()->back()->withInput()
            ->with('error', 'Une erreur est survenue lors de l\'envoi. Contactez-nous directement au +229 01 66 44 92 32.');
    }
})->name('contact.send');

/*
|--------------------------------------------------------------------------
| GUEST uniquement
|--------------------------------------------------------------------------
*/
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
Route::middleware(['auth'])->group(function () {

    // LOGOUT → redirige vers l'accueil vitrine
    Route::post('logout', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    // PROFIL — redirige selon le rôle pour éviter le 403
    Route::get('/profile', function () {
        if (Auth::user()->role === 'admin') {
            return redirect()->route('admin.profile.edit');
        }
        return app(\App\Http\Controllers\ProfileController::class)->edit(request());
    })->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::delete('/profile/avatar', [ProfileController::class, 'destroyAvatar'])->name('profile.avatar.destroy');

    // DASHBOARD REDIRECTION
    Route::get('/dashboard', function () {
        return Auth::user()->role === 'admin'
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

        // Années scolaires
        Route::resource('school-years', SchoolYearController::class);
        Route::post('school-years/{schoolYear}/toggle', [SchoolYearController::class, 'toggle'])
            ->name('school-years.toggle');

        // Classes
        Route::get('classes', [ClasseController::class, 'index'])->name('classes.index');

        // Écoles
        Route::resource('ecoles', AdminEcoleController::class);

        // IMPORT (AVANT resource pour éviter conflits de routing)
        Route::get('eleves/import', [AdminStudentImportController::class, 'create'])
            ->name('students.import.create');
        Route::post('eleves/import/preview', [AdminStudentImportController::class, 'preview'])
            ->name('students.import.preview');
        Route::post('eleves/import/store-all', [AdminStudentImportController::class, 'storeAll'])
            ->name('students.import.storeAll');

        // EXPORTS (AVANT resource pour éviter conflits)
        Route::get('/students/export-ecole-cards', [AdminStudentController::class, 'exportEcoleCardsPdf'])
            ->name('students.export.ecole.cards');
        Route::get('/students/export-class-cards', [AdminStudentController::class, 'exportClassCardsPdf'])
            ->name('students.export.class.cards');

        // GESTION ÉLÈVES
        Route::resource('eleves', AdminStudentController::class)
            ->parameters(['eleves' => 'eleve'])
            ->names('students');

        Route::get('/students/{eleve}/export-card-pdf', [AdminStudentController::class, 'exportCardPdf'])
            ->name('students.export.card.pdf');
        Route::get('/students/{eleve}/export-card-image', [AdminStudentController::class, 'exportCardImage'])
            ->name('students.export.card.image');

        // PROFIL ADMIN
        Route::get('/profile', [\App\Http\Controllers\Admin\AdminProfileController::class, 'edit'])
            ->name('profile.edit');
        Route::patch('/profile', [\App\Http\Controllers\Admin\AdminProfileController::class, 'update'])
            ->name('profile.update');
    });
});

/*
|--------------------------------------------------------------------------
| ÉCOLE
|--------------------------------------------------------------------------
*/
Route::prefix('ecole')->middleware(['ecole'])->name('school.')->group(function () {

    Route::get('/', [SchoolDashboardController::class, 'index'])->name('dashboard');

    // Mon école
    Route::get('mon-ecole/create', [EcoleController::class, 'create'])->name('ecole.create');
    Route::post('mon-ecole', [EcoleController::class, 'store'])->name('ecole.store');
    Route::get('mon-ecole', [EcoleController::class, 'show'])->name('ecole.show');

    // Import élèves (AVANT resource)
    Route::get('eleves/import', [StudentImportController::class, 'create'])
        ->name('students.import.create');
    Route::post('eleves/import/preview', [StudentImportController::class, 'preview'])
        ->name('students.import.preview');
    Route::post('eleves/import/store-all', [StudentImportController::class, 'storeAll'])
        ->name('students.import.storeAll');
    Route::get('/school/eleves/import/canvas', [StudentImportController::class, 'downloadCanvas'])
        ->name('school.eleves.import.canvas');

    // Gestion élèves
    Route::resource('eleves', StudentController::class)
        ->parameters(['eleves' => 'eleve'])
        ->names('students');

    // Cartes
    Route::get('eleves/voir-cartes', [StudentController::class, 'viewCards'])
        ->name('students.viewCards');
    Route::get('eleves/{eleve}/download-card', [StudentController::class, 'downloadCard'])
        ->name('students.downloadCard');

    // Ajax
    Route::get('ajax/classes', [AjaxController::class, 'classesBySection']);
    Route::get('ajax/series', [AjaxController::class, 'seriesByClasse']);
    Route::get('ajax/partitions', [AjaxController::class, 'partitions']);
});

require __DIR__ . '/auth.php';