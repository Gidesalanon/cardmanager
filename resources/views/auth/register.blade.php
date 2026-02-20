@extends('layouts.auth')

@section('title', 'Inscription')

@section('content')
<style>
    /* Style pour le conteneur du mot de passe */
    .password-container {
        position: relative;
        display: flex;
        align-items: center;
    }

    .password-toggle {
        position: absolute;
        right: 15px;
        background: none;
        border: none;
        cursor: pointer;
        color: #6b7280;
        display: flex;
        align-items: center;
        padding: 0;
        z-index: 10;
    }

    .password-toggle:hover {
        color: #374151;
    }

    /* Ajustement de l'input pour ne pas que le texte passe sous l'icône */
    .form-input-password {
        padding-right: 45px !important;
    }
    
    .hidden {
        display: none;
    }
</style>

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
                    <input type="text" name="name" class="form-input" value="{{ old('name') }}" required autofocus>
                </div>

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" value="{{ old('email') }}" required>
                </div>

                <!-- Champ Mot de passe -->
                <div class="form-group">
                    <label class="form-label">Mot de passe</label>
                    <div class="password-container">
                        <input 
                            id="password" 
                            type="password" 
                            name="password" 
                            class="form-input form-input-password" 
                            required
                        >
                        <button type="button" class="password-toggle" onclick="toggleVisibility('password', 'eye-reg', 'eye-off-reg')">
                            <svg id="eye-reg" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg id="eye-off-reg" class="hidden" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Champ Confirmation Mot de passe -->
                <div class="form-group">
                    <label class="form-label">Confirmation</label>
                    <div class="password-container">
                        <input 
                            id="password_confirmation" 
                            type="password" 
                            name="password_confirmation" 
                            class="form-input form-input-password" 
                            required
                        >
                        <button type="button" class="password-toggle" onclick="toggleVisibility('password_confirmation', 'eye-conf', 'eye-off-conf')">
                            <svg id="eye-conf" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg id="eye-off-conf" class="hidden" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    S'inscrire
                </button>
            </form>

            <div class="login-divider">
                <span>ou continuer avec</span>
            </div>

            <div style="display: grid; grid-template-columns: 1fr; gap: 0.75rem; margin-bottom: 1.5rem;">
                <a href="{{ route('google.login') }}" class="btn btn-secondary">
                    <svg viewBox="0 0 24 24" width="18" height="18" style="margin-right: 8px;">
                        <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    S'inscrire avec Google
                </a>
            </div>

        </div>

        <p class="login-footer">
            Déjà un compte ?
            <a href="{{ route('login') }}">Se connecter</a>
        </p>
    </div>
</div>

<script>
    /**
     * Fonction universelle pour basculer la visibilité du mot de passe
     * @param inputId ID de l'input à basculer
     * @param eyeId ID de l'icône oeil ouvert
     * @param eyeOffId ID de l'icône oeil fermé
     */
    function toggleVisibility(inputId, eyeId, eyeOffId) {
        const input = document.getElementById(inputId);
        const eye = document.getElementById(eyeId);
        const eyeOff = document.getElementById(eyeOffId);

        if (input.type === 'password') {
            input.type = 'text';
            eye.classList.add('hidden');
            eyeOff.classList.remove('hidden');
        } else {
            input.type = 'password';
            eye.classList.remove('hidden');
            eyeOff.classList.add('hidden');
        }
    }
</script>

@endsection