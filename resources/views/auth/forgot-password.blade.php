@extends('layouts.auth')

@section('title', 'Mot de passe oublié')

@section('content')

<style>
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
    .form-group { margin-bottom: 16px; }
    .form-label { display: block; font-size: 0.83rem; font-weight: 600; margin-bottom: 6px; opacity: 0.8; }
    .form-input { width: 100%; padding: 11px 14px; border-radius: 9px; font-size: 0.93rem; }
    .btn.btn-primary {
        width: 100%; padding: 12px; border-radius: 10px;
        font-size: 0.97rem; font-weight: 700; margin-bottom: 16px;
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
                <h1 class="login-title">Mot de passe oublié</h1>
                <p class="login-subtitle">Nous vous enverrons un lien de réinitialisation</p>
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

            <form method="POST" action="{{ route('password.email') }}" class="login-form">
                @csrf
                <div class="form-group">
                    <label class="form-label">Adresse email</label>
                    <input type="email" name="email" class="form-input"
                           value="{{ old('email') }}" required autofocus>
                </div>
                <button type="submit" class="btn btn-primary">
                    Envoyer le lien
                </button>
            </form>

            <p class="login-footer">
                <a href="{{ route('login') }}">← Retour à la connexion</a>
            </p>

        </div>
    </div>
</div>

@endsection