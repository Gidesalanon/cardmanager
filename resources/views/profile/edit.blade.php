@extends('layouts.ecole')

@section('content')
    <div class="profile-page-container">
        {{-- NOTIFICATION FLOTTANTE A DROITE --}}
        @if (session('status') === 'profile-updated')
            <div id="toast-success" class="toast-notification">
                <div class="toast-icon">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="3">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <div class="toast-content">
                    <span class="toast-title">Succès !</span>
                    <p class="toast-message">Votre profil a été mis à jour.</p>
                </div>
                <button onclick="document.getElementById('toast-success').remove()" class="toast-close">&times;</button>
            </div>
            <script>
                setTimeout(() => {
                    const toast = document.getElementById('toast-success');
                    if (toast) {
                        toast.style.animation = 'fadeOutRight 0.5s ease forwards';
                        setTimeout(() => toast.remove(), 500);
                    }
                }, 4000);
            </script>
        @endif

        <div class="profile-header">
            <h1>Paramètres du Profil</h1>
            <p>Gérez vos informations personnelles et la sécurité de votre compte</p>
        </div>

        <div class="profile-card">
            <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('patch')

                <!-- SECTION AVATAR -->
                <div class="avatar-upload-section">
                    <div class="avatar-wrapper">
                        <img id="preview"
                            src="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) . '&background=3b82f6&color=fff' }}"
                            alt="Avatar">
                        <label for="avatar" class="upload-badge" title="Changer la photo">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path
                                    d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z" />
                                <circle cx="12" cy="13" r="4" />
                            </svg>
                            <input type="file" name="avatar" id="avatar" hidden accept="image/*">
                        </label>
                    </div>
                    <div class="avatar-info">
                        <h3>Photo de profil</h3>
                        <p>JPG, PNG ou GIF. Max 2Mo.</p>
                    </div>
                </div>

                <div class="form-grid">
                    <!-- INFOS DE BASE (LECTURE SEULE) -->
                    <div class="form-section">
                        <h4 class="section-title"><svg viewBox="0 0 24 24" width="18" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg> Informations générales</h4>

                        <div class="input-group">
                            <label>Nom et Prénoms</label>
                            <input type="text" name="name" value="{{ $user->name }}" readonly
                                style="cursor: not-allowed; opacity: 0.7; background: var(--p-bg);">
                        </div>

                        <div class="input-group">
                            <label>Adresse Email</label>
                            <input type="email" name="email" value="{{ $user->email }}" readonly
                                style="cursor: not-allowed; opacity: 0.7; background: var(--p-bg);">
                        </div>
                    </div>

                    <!-- SECURITE (AVEC OEIL) -->
                    <div class="form-section">
                        <h4 class="section-title"><svg viewBox="0 0 24 24" width="18" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                            </svg> Sécurité</h4>

                        <div class="input-group" style="position: relative;">
                            <label>Nouveau mot de passe</label>
                            <input type="password" name="password" id="pass1" placeholder="••••••••"
                                style="padding-right: 45px;">
                            <button type="button" onclick="togglePass('pass1')"
                                style="position: absolute; right: 12px; top: 38px; background: none; border: none; cursor: pointer; color: var(--p-text-muted);">
                                <svg id="eye-pass1" viewBox="0 0 24 24" width="20" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                            </button>
                        </div>

                        <div class="input-group" style="position: relative;">
                            <label>Confirmer le mot de passe</label>
                            <input type="password" name="password_confirmation" id="pass2" placeholder="••••••••"
                                style="padding-right: 45px;">
                            <button type="button" onclick="togglePass('pass2')"
                                style="position: absolute; right: 12px; top: 38px; background: none; border: none; cursor: pointer; color: var(--p-text-muted);">
                                <svg id="eye-pass2" viewBox="0 0 24 24" width="20" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-save">
                        <span class="btn-text">Enregistrer les modifications</span>
                        <svg viewBox="0 0 24 24" width="20" fill="none" stroke="currentColor"
                            stroke-width="2.5">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        /* CSS POUR LA NOTIFICATION FLOTTANTE */
        .toast-notification {
            position: fixed;
            top: 30px;
            right: 30px;
            background: #10b981;
            color: white;
            padding: 16px 24px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
            z-index: 9999;
            min-width: 300px;
            animation: slideInRight 0.5s ease-out forwards;
        }

        .toast-icon {
            background: rgba(255, 255, 255, 0.2);
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .toast-title {
            font-weight: 800;
            font-size: 15px;
            display: block;
        }

        .toast-message {
            font-size: 13px;
            margin: 0;
            opacity: 0.9;
        }

        .toast-close {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            margin-left: auto;
            opacity: 0.7;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(120%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes fadeOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(120%);
                opacity: 0;
            }
        }

        /* ... (VOTRE CSS HABITUEL CI-DESSOUS) ... */
        :root {
            --p-bg: #f8fafc;
            --p-card: #ffffff;
            --p-text: #1e293b;
            --p-text-muted: #64748b;
            --p-border: #e2e8f0;
            --p-input-bg: #ffffff;
            --p-primary: #3b82f6;
            --p-primary-hover: #2563eb;
            --p-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        }

        [data-theme='carbon'],
        .carbon {
            --p-bg: #0f172a;
            --p-card: #1e293b;
            --p-text: #f1f5f9;
            --p-text-muted: #94a3b8;
            --p-border: #334155;
            --p-input-bg: #0f172a;
            --p-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
        }

        .profile-page-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 20px;
            font-family: 'Inter', sans-serif;
        }

        .profile-header h1 {
            font-size: 28px;
            font-weight: 800;
            color: var(--p-text);
            margin-bottom: 8px;
        }

        .profile-card {
            background: var(--p-card);
            border: 1px solid var(--p-border);
            border-radius: 20px;
            padding: 40px;
            box-shadow: var(--p-shadow);
        }

        .avatar-upload-section {
            display: flex;
            align-items: center;
            gap: 24px;
            margin-bottom: 40px;
            padding-bottom: 32px;
            border-bottom: 1px solid var(--p-border);
        }

        .avatar-wrapper {
            position: relative;
            width: 100px;
            height: 100px;
        }

        .avatar-wrapper img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--p-primary);
            padding: 3px;
            background: var(--p-card);
        }

        .upload-badge {
            position: absolute;
            bottom: 0;
            right: 0;
            background: var(--p-primary);
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 3px solid var(--p-card);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        .input-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--p-text-muted);
            margin-bottom: 8px;
        }

        .input-group input {
            width: 100%;
            padding: 12px 16px;
            background: var(--p-input-bg);
            border: 1.5px solid var(--p-border);
            border-radius: 12px;
            color: var(--p-text);
        }

        .btn-save {
            background: var(--p-primary);
            color: white;
            border: none;
            padding: 14px 32px;
            border-radius: 14px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 12px;
        }
    </style>

    <script>
        // Preview image
        document.getElementById('avatar').onchange = evt => {
            const [file] = document.getElementById('avatar').files
            if (file) {
                document.getElementById('preview').src = URL.createObjectURL(file)
            }
        }

        // Toggle Password Visibility
        function togglePass(id) {
            const input = document.getElementById(id);
            const icon = document.getElementById('eye-' + id);
            if (input.type === "password") {
                input.type = "text";
                icon.style.stroke = "var(--p-primary)";
            } else {
                input.type = "password";
                icon.style.stroke = "currentColor";
            }
        }
    </script>
@endsection
