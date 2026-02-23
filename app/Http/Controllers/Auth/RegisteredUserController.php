<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;


class RegisteredUserController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        if ($validator->fails()) {
            // Si requête AJAX, retourner les erreurs en JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Sinon, comportement normal avec redirection
            return redirect()->route('register')
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'ecole', // IMPORTANT
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Si requête AJAX, retourner JSON
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Compte créé avec succès !'
            ]);
        }

        // Sinon, comportement normal (redirection)
        // Message de succès avant la redirection
        session()->flash('success', 'Compte créé avec succès ! Bienvenue sur CardManager.');
        return redirect()->route('verification.notice');
    }
}
