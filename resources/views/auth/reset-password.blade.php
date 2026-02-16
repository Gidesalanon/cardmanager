@extends('layouts.auth')

@section('title', 'Nouveau mot de passe')

@section('content')
<div class="login-page">
    <div class="login-container">
        <div class="login-card">

            <div class="login-header">
                <h1 class="login-title">Nouveau mot de passe</h1>
            </div>

            <form method="POST" action="{{ route('password.update') }}" class="login-form">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <input type="hidden" name="email" value="{{ request('email') }}">

                <div class="form-group">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="password" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirmation</label>
                    <input type="password" name="password_confirmation" class="form-input" required>
                </div>

                <button type="submit" class="btn btn-primary">
                    Réinitialiser
                </button>
            </form>

        </div>
    </div>
</div>
@endsection
