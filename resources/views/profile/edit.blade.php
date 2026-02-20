@extends('layouts.admin')

@section('content')
<div class="profile-modern-page">
    
    {{-- ZONE DES NOTIFICATIONS À DROITE --}}
    <div id="toast-container" class="toast-container"></div>

    @if (session('status') === 'profile-updated')
        <script>
            window.onload = () => createToast("✅ Profil mis à jour avec succès !", "#10b981");
        </script>
    @endif

    @if ($errors->any())
        <script>
            window.onload = () => createToast(" Erreur : confirmez votre mot de page d'abord", "#ef4444");
        </script>
    @endif

    <div class="profile-header">
        <h1>Mon Profil</h1>
        <p>Gérez vos informations personnelles et votre sécurité sur cette page</p>
    </div>

    <div class="profile-card-main">
        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            {{-- SECTION AVATAR --}}
            <div class="avatar-section">
                <div class="avatar-wrapper">
                    <img id="preview" src="{{ auth()->user()->avatar ? asset('storage/'.auth()->user()->avatar) : 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&background=3b82f6&color=fff' }}" alt="Avatar">
                    <label for="avatar-input" class="upload-badge" title="Changer la photo">
                        <svg viewBox="0 0 24 24" width="18" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                        <input type="file" name="avatar" id="avatar-input" hidden accept="image/*">
                    </label>
                </div>
                <div class="avatar-text">
                    <h3>Photo de profil</h3>
                    <p>Format JPG, PNG ou GIF (Max 2Mo)</p>
                </div>
            </div>

            <div class="form-grid">
                {{-- COLONNE GAUCHE : IDENTITÉ --}}
                <div class="form-col">
                    <h4 class="label-title">Identité</h4>
                    <div class="field-group">
                        <label>Nom et Prénoms</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                        @error('name') <span class="err-msg">{{ $message }}</span> @enderror
                    </div>
                    <div class="field-group">
                        <label>Adresse Email (Non modifiable)</label>
                        <input type="email" value="{{ $user->email }}" readonly class="readonly-input">
                    </div>
                </div>

                {{-- COLONNE DROITE : SÉCURITÉ --}}
                <div class="form-col">
                    <h4 class="label-title">Sécurité & Validation</h4>
                    
                    <div class="field-group highlight-field">
                        <label>Mot de passe actuel (Obligatoire pour enregistrer)</label>
                        <input type="password" name="current_password" placeholder="Confirmez votre identité" required>
                        @error('current_password') <span class="err-msg">{{ $message }}</span> @enderror
                    </div>

                    <div class="field-group">
                        <label>Nouveau mot de passe (Facultatif)</label>
                        <input type="password" name="password" placeholder="Minimum 8 caractères">
                    </div>

                    <div class="field-group">
                        <label>Confirmer le nouveau mot de passe</label>
                        <input type="password" name="password_confirmation" placeholder="••••••••">
                    </div>
                </div>
            </div>

            <button type="submit" class="save-btn-modern">
                Enregistrer les modifications
            </button>
        </form>
    </div>
</div>

<style>
/* --- DESIGN SYSTEM --- */
:root { --p-bg: #f8fafc; --p-card: #ffffff; --p-text: #0f172a; --p-muted: #64748b; --p-border: #e2e8f0; --p-primary: #3b82f6; }
[data-theme='carbon'] .profile-modern-page { --p-bg: #0f172a; --p-card: #1e293b; --p-text: #f8fafc; --p-muted: #94a3b8; --p-border: #334155; }

.profile-modern-page { padding: 40px 20px; max-width: 950px; margin: 0 auto; font-family: 'Inter', sans-serif; }
.profile-header h1 { font-size: 26px; font-weight: 900; color: var(--p-text); margin: 0; }
.profile-header p { color: var(--p-muted); margin-bottom: 30px; }

.profile-card-main { background: var(--p-card); border-radius: 24px; padding: 40px; border: 1px solid var(--p-border); box-shadow: 0 10px 25px rgba(0,0,0,0.05); }

/* AVATAR */
.avatar-section { display: flex; align-items: center; gap: 20px; margin-bottom: 35px; padding-bottom: 30px; border-bottom: 1px solid var(--p-border); }
.avatar-wrapper { position: relative; width: 100px; height: 100px; }
.avatar-wrapper img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; border: 3px solid var(--p-primary); padding: 3px; }
.upload-badge { position: absolute; bottom: 0; right: 0; background: var(--p-primary); color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 3px solid var(--p-card); transition: 0.2s; }
.upload-badge:hover { transform: scale(1.1); }

/* FORMULAIRE */
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 30px; }
.label-title { font-size: 13px; font-weight: 800; color: var(--p-primary); text-transform: uppercase; margin-bottom: 20px; letter-spacing: 0.5px; }
.field-group { margin-bottom: 18px; }
.field-group label { display: block; font-size: 13px; font-weight: 600; color: var(--p-muted); margin-bottom: 8px; }
.field-group input { width: 100%; padding: 12px 16px; border-radius: 12px; border: 1.5px solid var(--p-border); background: var(--p-card); color: var(--p-text); transition: 0.2s; }
.field-group input:focus { outline: none; border-color: var(--p-primary); box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); }

.highlight-field input { border-color: #3b82f6; background: rgba(59, 130, 246, 0.02); }
.readonly-input { background: var(--p-bg) !important; cursor: not-allowed; opacity: 0.7; }
.err-msg { color: #ef4444; font-size: 11px; margin-top: 4px; display: block; }

/* BOUTON */
.save-btn-modern { width: 100%; background: var(--p-primary); color: white; border: none; padding: 16px; border-radius: 14px; font-weight: 700; font-size: 16px; cursor: pointer; transition: 0.3s; }
.save-btn-modern:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3); }

/* TOASTS */
.toast-container { position: fixed; top: 20px; right: 20px; z-index: 9999; }
.toast-item { min-width: 280px; padding: 16px 20px; border-radius: 12px; color: white; font-weight: 600; margin-bottom: 10px; box-shadow: 0 10px 15px rgba(0,0,0,0.1); animation: slideIn 0.4s ease forwards; }
@keyframes slideIn { from { transform: translateX(120%); } to { transform: translateX(0); } }
</style>

<script>
    // Aperçu photo direct
    document.getElementById('avatar-input').onchange = e => {
        const [file] = e.target.files;
        if (file) document.getElementById('preview').src = URL.createObjectURL(file);
    }

    // Fonction Toast
    function createToast(text, color) {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = 'toast-item';
        toast.style.backgroundColor = color;
        toast.innerText = text;
        container.appendChild(toast);
        setTimeout(() => {
            toast.style.animation = "slideIn 0.4s ease reverse forwards";
            setTimeout(() => toast.remove(), 400);
        }, 4000);
    }
</script>
@endsection