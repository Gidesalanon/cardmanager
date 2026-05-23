@extends('layouts.auth')

@section('title', 'Nouveau mot de passe')

@section('content')

<style>
    .password-container { position: relative; display: flex; align-items: center; }
    .password-toggle {
        position: absolute; right: 14px; background: none; border: none;
        cursor: pointer; color: #6b7280; display: flex; align-items: center;
        padding: 0; transition: color 0.2s;
    }
    .password-toggle:hover { color: #c9a84c; }
    .form-input-password { padding-right: 45px !important; }
    .hidden { display: none; }

    .back-home-btn {
        position: fixed; top: 18px; left: 20px;
        display: inline-flex; align-items: center; gap: 7px;
        padding: 7px 15px; background: rgba(201,168,76,0.1);
        border: 1px solid rgba(201,168,76,0.3); border-radius: 8px;
        color: #c9a84c !important; text-decoration: none !important;
        font-size: 0.83rem; font-weight: 600; transition: all 0.2s; z-index: 999;
    }
    .back-home-btn:hover { background: rgba(201,168,76,0.2) !important; color: #f0d080 !important; }

    .login-card { padding: 36px 32px !important; }
    .login-header { text-align: center; margin-bottom: 24px; }
    .login-logo {
        display: flex; align-items: center; justify-content: center;
        gap: 10px; margin-bottom: 14px;
    }
    .login-logo .logo-icon {
        width: 42px; height: 42px; border-radius: 10px; overflow: hidden;
        display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #c9a84c, #f0d080) !important;
    }
    .login-title { font-size: 1.4rem; font-weight: 800; margin-bottom: 4px; }
    .login-subtitle { font-size: 0.83rem; opacity: 0.6; }
    .form-group { margin-bottom: 14px; }
    .form-label { display: block; font-size: 0.83rem; font-weight: 600; margin-bottom: 6px; opacity: 0.8; }
    .form-input { width: 100%; padding: 11px 14px; border-radius: 9px; font-size: 0.93rem; }
    .btn.btn-primary {
        width: 100%; padding: 12px; border-radius: 10px;
        font-size: 0.97rem; font-weight: 700; margin-bottom: 16px; margin-top: 6px;
    }
    .login-footer { text-align: center; font-size: 0.83rem; margin-top: 4px; opacity: 0.7; }
</style>

<a href="{{ route('home') }}" class="back-home-btn">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
        <polyline points="9 22 9 12 15 12 15 22"/>
    </svg>
    Accueil
</a>

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
                <h1 class="login-title">Nouveau mot de passe</h1>
                <p class="login-subtitle">Choisissez un nouveau mot de passe sécurisé</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger" style="padding:10px 14px; margin-bottom:16px; border-radius:8px; font-size:0.85rem;">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.store') }}" class="login-form">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">
                <input type="hidden" name="email" value="{{ request('email') }}">

                <div class="form-group">
                    <label class="form-label">Nouveau mot de passe</label>
                    <div class="password-container">
                        <input id="password" type="password" name="password"
                               class="form-input form-input-password" required autocomplete="new-password">
                        <button type="button" class="password-toggle"
                                onclick="toggleVisibility('password','eye-p','eye-off-p')">
                            <svg id="eye-p" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg id="eye-off-p" class="hidden" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirmer le mot de passe</label>
                    <div class="password-container">
                        <input id="password_confirmation" type="password" name="password_confirmation"
                               class="form-input form-input-password" required autocomplete="new-password">
                        <button type="button" class="password-toggle"
                                onclick="toggleVisibility('password_confirmation','eye-c','eye-off-c')">
                            <svg id="eye-c" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg id="eye-off-c" class="hidden" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    Réinitialiser le mot de passe
                </button>
            </form>

            <p class="login-footer">
                <a href="{{ route('login') }}">← Retour à la connexion</a>
            </p>

        </div>
    </div>
</div>

<script>
    function toggleVisibility(inputId, eyeId, eyeOffId) {
        const input  = document.getElementById(inputId);
        const eye    = document.getElementById(eyeId);
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