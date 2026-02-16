@extends('layouts.auth')

@section('title', 'Inscription')

@section('content')

<div class="login-theme-toggle">
    <div class="theme-toggle">
        <button class="theme-btn theme-btn-snow active" onclick="setTheme('snow')"></button>
        <button class="theme-btn theme-btn-carbon" onclick="setTheme('carbon')"></button>
    </div>
</div>

<div class="login-page">
    <div class="login-container">
        <div class="login-card">

            <div class="login-header">
                <div class="login-logo">
                    <div class="logo-icon">
                        <img 
                            src="{{ asset('assets/web/images/logo-3.png') }}" 
                            alt="Logo" 
                            style="width: 100%; height: 100%; object-fit: contain;"
                        >
                    </div>
                    <span>{{ config('app.name') }}</span>
                </div>
                <h1 class="login-title">Inscription</h1>
                <p class="login-subtitle">Créer un compte</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="login-form">
                @csrf

                <div class="form-group">
                    <label class="form-label">Nom & Prénom</label>
                    <input type="text" name="name" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="password" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirmation</label>
                    <input type="password" name="password_confirmation" class="form-input" required>
                </div>

                <button type="submit" class="btn btn-primary">
                    S'inscrire
                </button>
            </form>

            <div class="login-divider">
                <span>ou continuer avec</span>
            </div>

            {{-- BOUTON GOOGLE (placeholder) --}}
            <button class="btn btn-secondary" disabled>
                Google (bientôt)
            </button>

        </div>

        <p class="login-footer">
            Déjà un compte ?
            <a href="{{ route('login') }}">Se connecter</a>
        </p>
    </div>
</div>

@endsection
