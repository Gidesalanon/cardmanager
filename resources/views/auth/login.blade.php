@extends('layouts.auth')

@section('title', 'Connexion')

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
    <h1 class="login-title">Connexion</h1>
    <p class="login-subtitle">Accédez à votre espace</p>
</div>

            {{-- MESSAGE DE SUCCÈS (Vérification email) --}}
            @if (session('status'))
                <div class="alert alert-success" style="background-color: #d4edda; color: #155724; border-color: #c3e6cb; padding: 0.75rem 1.25rem; margin-bottom: 1rem; border: 1px solid transparent; border-radius: 0.25rem;">
                    {{ session('status') }}
                </div>
            @endif

            {{-- ERREURS --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif
            

            <form method="POST" action="{{ route('login') }}" class="login-form">
                @csrf

                <div class="form-group">
                    <label class="form-label">Adresse email</label>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="form-input"
                        required
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">Mot de passe</label>
                    <input
                        type="password"
                        name="password"
                        class="form-input"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="remember">
                        Se souvenir de moi
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">
                    Se connecter
                </button>
                <div class="form-footer">
                    <p class="login-footer">
                        Pas de compte ?
                        <a href="{{ route('register') }}">Créer un compte</a><br>
                        <a href="{{ route('password.request') }}">Mot de passe oublié ?</a>
                    </p>
                </div>
            </form>

        </div>

        <div class="login-divider">
            <span>ou continuer avec</span>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1.5rem;">
            <a href="{{ route('google.login') }}" class="btn btn-secondary">
                <svg viewBox="0 0 24 24" width="18" height="18">
                    <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                Google
            </a>
        </div>

        <p class="login-footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}
        </p>
    </div>
</div>

@endsection
