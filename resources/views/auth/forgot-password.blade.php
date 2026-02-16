@extends('layouts.auth')

@section('title', 'Mot de passe oublié')

@section('content')
<div class="login-page">
    <div class="login-container">
        <div class="login-card">

            <div class="login-header">
                <h1 class="login-title">Mot de passe oublié</h1>
                <p class="login-subtitle">Nous vous enverrons un lien</p>
            </div>

            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="login-form">
                @csrf

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" required>
                </div>

                <button type="submit" class="btn btn-primary">
                    Envoyer le lien
                </button>
            </form>

            <p class="login-footer">
                <a href="{{ route('login') }}">Retour à la connexion</a>
            </p>

        </div>
    </div>
</div>
@endsection
