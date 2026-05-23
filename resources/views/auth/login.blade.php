@extends('layouts.auth')

@section('title', 'Connexion')

@section('content')

<style>
    .password-container {
        position: relative;
        display: flex;
        align-items: center;
    }
    .password-toggle {
        position: absolute;
        right: 14px;
        background: none;
        border: none;
        cursor: pointer;
        color: #6b7280;
        display: flex;
        align-items: center;
        padding: 0;
        transition: color 0.2s;
    }
    .password-toggle:hover { color: #c9a84c; }
    .form-input-password { padding-right: 45px !important; }

    .back-home-btn {
        position: fixed;
        top: 18px; left: 20px;
        display: inline-flex; align-items: center; gap: 7px;
        padding: 7px 15px;
        background: rgba(201,168,76,0.1);
        border: 1px solid rgba(201,168,76,0.3);
        border-radius: 8px;
        color: #c9a84c !important;
        text-decoration: none !important;
        font-size: 0.83rem; font-weight: 600;
        transition: all 0.2s; z-index: 999;
    }
    .back-home-btn:hover {
        background: rgba(201,168,76,0.2) !important;
        color: #f0d080 !important;
        border-color: rgba(201,168,76,0.55);
    }

    /* Organisation du formulaire */
    .login-card {
        padding: 36px 32px !important;
    }
    .login-header {
        text-align: center;
        margin-bottom: 28px;
    }
    .login-logo {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-bottom: 16px;
    }
    .login-logo .logo-icon {
        width: 42px; height: 42px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 10px;
        overflow: hidden;
        background: linear-gradient(135deg, #c9a84c, #f0d080) !important;
    }
    .login-title { font-size: 1.4rem; font-weight: 800; margin-bottom: 5px; }
    .login-subtitle { font-size: 0.85rem; opacity: 0.6; }

    .form-group { margin-bottom: 16px; }
    .form-label {
        display: block;
        font-size: 0.83rem; font-weight: 600;
        margin-bottom: 6px; opacity: 0.8;
    }
    .form-input {
        width: 100%;
        padding: 11px 14px;
        border-radius: 9px;
        font-size: 0.93rem;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .remember-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        font-size: 0.83rem;
    }
    .remember-label {
        display: flex; align-items: center; gap: 7px;
        cursor: pointer; font-weight: 500;
    }

    .btn.btn-primary {
        width: 100%;
        padding: 12px;
        border-radius: 10px;
        font-size: 0.97rem;
        font-weight: 700;
        margin-bottom: 16px;
        display: flex; align-items: center; justify-content: center; gap: 8px;
    }

    .form-links {
        text-align: center;
        font-size: 0.83rem;
        line-height: 1.9;
    }

    .login-divider {
        display: flex; align-items: center; gap: 12px;
        margin: 18px 0; font-size: 0.8rem;
    }
    .login-divider::before,
    .login-divider::after {
        content: ''; flex: 1; height: 1px;
        background: currentColor; opacity: 0.15;
    }

    .btn.btn-secondary {
        width: 100%; padding: 11px;
        border-radius: 9px; font-size: 0.88rem; font-weight: 500;
        display: flex; align-items: center; justify-content: center; gap: 8px;
        text-decoration: none; margin-bottom: 0;
        transition: all 0.2s;
    }

    .login-footer {
        text-align: center;
        font-size: 0.82rem;
        margin-top: 18px;
        opacity: 0.7;
    }
</style>

<a href="{{ route('home') }}" class="back-home-btn">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
        <polyline points="9 22 9 12 15 12 15 22"/>
    </svg>
    Accueil
</a>

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
                        <img src="{{ asset('assets/web/images/logo-3.png') }}" alt="Logo"
                             style="width:100%; height:100%; object-fit:contain;">
                    </div>
                    <span>{{ config('app.name') }}</span>
                </div>
                <h1 class="login-title">Connexion</h1>
                <p class="login-subtitle">Accédez à votre espace</p>
            </div>

            @if (session('status'))
                <div class="alert alert-success" style="padding:10px 14px; margin-bottom:16px; border-radius:8px; font-size:0.85rem;">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger" style="padding:10px 14px; margin-bottom:16px; border-radius:8px; font-size:0.85rem;">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="login-form">
                @csrf

                <div class="form-group">
                    <label class="form-label">Adresse email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="form-input" required autofocus autocomplete="email">
                </div>

                <div class="form-group">
                    <label class="form-label">Mot de passe</label>
                    <div class="password-container">
                        <input id="password" type="password" name="password"
                               class="form-input form-input-password" required autocomplete="current-password">
                        <button type="button" class="password-toggle" onclick="togglePasswordVisibility()">
                            <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg id="eye-off-icon" style="display:none;" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="remember-row">
                    <label class="remember-label">
                        <input type="checkbox" name="remember">
                        Se souvenir de moi
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}">Mot de passe oublié ?</a>
                    @endif
                </div>

                <button type="submit" class="btn btn-primary">
                    Se connecter
                </button>

                <div class="form-links">
                    Pas de compte ? <a href="{{ route('register') }}">Créer un compte</a>
                </div>
            </form>

        </div>

        <div class="login-divider">
            <span>ou continuer avec</span>
        </div>

        <a href="{{ route('google.login') }}" class="btn btn-secondary">
            <svg viewBox="0 0 24 24" width="18" height="18">
                <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Continuer avec Google
        </a>

        <p class="login-footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}
        </p>
    </div>
</div>

<script>
    function togglePasswordVisibility() {
        const input = document.getElementById('password');
        const eye = document.getElementById('eye-icon');
        const eyeOff = document.getElementById('eye-off-icon');
        if (input.type === 'password') {
            input.type = 'text';
            eye.style.display = 'none';
            eyeOff.style.display = 'block';
        } else {
            input.type = 'password';
            eye.style.display = 'block';
            eyeOff.style.display = 'none';
        }
    }
</script>

@endsection