@extends('layouts.app')

@section('title', 'Mon profil')

@section('content')

<div class="page-header">
    <h1 class="greeting">Mon profil</h1>
    <p class="greeting-sub">Gérez vos informations personnelles et votre mot de passe</p>
</div>

<div style="max-width: 720px; display: flex; flex-direction: column; gap: 1.5rem;">

    {{-- ===== PROFIL ===== --}}
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Informations du compte</div>
                <div class="card-subtitle">Nom affiché et photo de profil</div>
            </div>
        </div>

        @if(session('status') === 'profile-updated')
            <div class="alert alert-success" style="margin-bottom: 1.25rem;">
                ✓ Profil mis à jour avec succès.
            </div>
        @endif

        <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            <input type="hidden" name="action" value="profile">

            {{-- Avatar --}}
            <div class="form-group">
                <label class="form-label">Photo de profil</label>
                <div style="display:flex; align-items:center; gap:1rem; margin-bottom:0.5rem;">
                    <div style="width:64px; height:64px; border-radius:12px; overflow:hidden;
                                background: linear-gradient(135deg, #c9a84c, #f0d080);
                                display:flex; align-items:center; justify-content:center;
                                color:#0a0a0a; font-weight:700; font-size:1.3rem; flex-shrink:0;">
                        @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}"
                                 style="width:100%; height:100%; object-fit:cover;">
                        @else
                            {{ strtoupper(substr($user->name ?? 'A', 0, 1)) }}
                        @endif
                    </div>
                    <div>
                        <input type="file" name="avatar" id="avatar" accept="image/jpg,image/jpeg,image/png"
                               style="font-size:0.85rem;">
                        <div style="font-size:0.75rem; color:var(--text-secondary); margin-top:4px;">
                            JPG, JPEG ou PNG — max 2 Mo
                        </div>
                    </div>
                </div>
                @error('avatar')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- Nom --}}
            <div class="form-group">
                <label class="form-label">Nom complet</label>
                <input type="text" name="name" class="form-input"
                       value="{{ old('name', $user->name) }}" required>
                @error('name')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- Email (affiché mais non modifiable ici) --}}
            <div class="form-group">
                <label class="form-label">Adresse email</label>
                <input type="email" class="form-input"
                       value="{{ $user->email }}" disabled
                       style="opacity:0.6; cursor:not-allowed;">
                <div style="font-size:0.75rem; color:var(--text-secondary); margin-top:4px;">
                    L'email ne peut pas être modifié depuis cette interface.
                </div>
            </div>

            <div style="display:flex; gap:1rem;">
                <button type="submit" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                        <polyline points="17 21 17 13 7 13 7 21"/>
                        <polyline points="7 3 7 8 15 8"/>
                    </svg>
                    Enregistrer le profil
                </button>
            </div>
        </form>
    </div>

    {{-- ===== MOT DE PASSE ===== --}}
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Changer le mot de passe</div>
                <div class="card-subtitle">Utilisez un mot de passe fort d'au moins 8 caractères</div>
            </div>
        </div>

        @if(session('status') === 'password-updated')
            <div class="alert alert-success" style="margin-bottom: 1.25rem;">
                ✓ Mot de passe mis à jour avec succès.
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger" style="margin-bottom: 1.25rem;">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.profile.update') }}">
            @csrf
            @method('PATCH')
            <input type="hidden" name="action" value="password">

            <div class="form-group">
                <label class="form-label">Mot de passe actuel</label>
                <div style="position:relative; display:flex; align-items:center;">
                    <input id="current_password" type="password" name="current_password"
                           class="form-input" placeholder="Votre mot de passe actuel"
                           autocomplete="current-password"
                           style="padding-right:42px;">
                    <button type="button" onclick="togglePwd('current_password','eye1','eye1off')"
                            style="position:absolute; right:12px; background:none; border:none;
                                   cursor:pointer; color:var(--text-secondary); display:flex; align-items:center;">
                        <svg id="eye1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg id="eye1off" style="display:none;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                        </svg>
                    </button>
                </div>
                @error('current_password')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Nouveau mot de passe</label>
                <div style="position:relative; display:flex; align-items:center;">
                    <input id="new_password" type="password" name="password"
                           class="form-input" placeholder="Minimum 8 caractères"
                           autocomplete="new-password"
                           style="padding-right:42px;"
                           oninput="checkStrength(this.value)">
                    <button type="button" onclick="togglePwd('new_password','eye2','eye2off')"
                            style="position:absolute; right:12px; background:none; border:none;
                                   cursor:pointer; color:var(--text-secondary); display:flex; align-items:center;">
                        <svg id="eye2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg id="eye2off" style="display:none;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                        </svg>
                    </button>
                </div>
                {{-- Indicateur de force --}}
                <div style="margin-top:6px; height:4px; border-radius:2px; background:var(--bg-surface); overflow:hidden;">
                    <div id="strengthBar" style="height:100%; border-radius:2px; transition:all 0.3s; width:0%;"></div>
                </div>
                <div id="strengthText" style="font-size:0.75rem; margin-top:3px; font-weight:600;"></div>
                @error('password')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Confirmer le nouveau mot de passe</label>
                <div style="position:relative; display:flex; align-items:center;">
                    <input id="confirm_password" type="password" name="password_confirmation"
                           class="form-input" placeholder="Répétez le nouveau mot de passe"
                           autocomplete="new-password"
                           style="padding-right:42px;">
                    <button type="button" onclick="togglePwd('confirm_password','eye3','eye3off')"
                            style="position:absolute; right:12px; background:none; border:none;
                                   cursor:pointer; color:var(--text-secondary); display:flex; align-items:center;">
                        <svg id="eye3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg id="eye3off" style="display:none;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div style="margin-top:1.25rem; display:flex; gap:1rem;">
                <button type="submit" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    Mettre à jour le mot de passe
                </button>
            </div>
        </form>
    </div>

    {{-- ===== INFOS COMPTE ===== --}}
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Informations du compte</div>
                <div class="card-subtitle">Données non modifiables</div>
            </div>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
            <div>
                <div style="font-size:0.75rem; color:var(--text-secondary); font-weight:600;
                            text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">
                    Adresse email
                </div>
                <div style="font-size:0.9rem; font-weight:500;">{{ $user->email }}</div>
            </div>
            <div>
                <div style="font-size:0.75rem; color:var(--text-secondary); font-weight:600;
                            text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">
                    Rôle
                </div>
                <div style="font-size:0.9rem;">
                    <span class="badge badge-blue" style="background:rgba(201,168,76,0.1); color:#c9a84c;">
                        {{ ucfirst($user->role) }}
                    </span>
                </div>
            </div>
            <div>
                <div style="font-size:0.75rem; color:var(--text-secondary); font-weight:600;
                            text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">
                    Membre depuis
                </div>
                <div style="font-size:0.9rem;">{{ $user->created_at->format('d/m/Y') }}</div>
            </div>
            <div>
                <div style="font-size:0.75rem; color:var(--text-secondary); font-weight:600;
                            text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">
                    Dernière modification
                </div>
                <div style="font-size:0.9rem;">{{ $user->updated_at->format('d/m/Y à H:i') }}</div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function togglePwd(inputId, eyeId, eyeOffId) {
        const input  = document.getElementById(inputId);
        const eye    = document.getElementById(eyeId);
        const eyeOff = document.getElementById(eyeOffId);
        if (input.type === 'password') {
            input.type = 'text';
            eye.style.display    = 'none';
            eyeOff.style.display = 'block';
        } else {
            input.type = 'password';
            eye.style.display    = 'block';
            eyeOff.style.display = 'none';
        }
    }

    function checkStrength(password) {
        const bar  = document.getElementById('strengthBar');
        const text = document.getElementById('strengthText');

        let score = 0;
        if (password.length >= 8)            score++;
        if (password.length >= 12)           score++;
        if (/[A-Z]/.test(password))          score++;
        if (/[0-9]/.test(password))          score++;
        if (/[^A-Za-z0-9]/.test(password))   score++;

        const levels = [
            { width: '0%',   color: 'transparent', label: '',             css: '' },
            { width: '25%',  color: '#ef4444',      label: 'Très faible', css: 'color:#ef4444' },
            { width: '50%',  color: '#f59e0b',      label: 'Faible',      css: 'color:#f59e0b' },
            { width: '75%',  color: '#3b82f6',      label: 'Moyen',       css: 'color:#3b82f6' },
            { width: '90%',  color: '#22c55e',      label: 'Fort',        css: 'color:#22c55e' },
            { width: '100%', color: '#c9a84c',      label: '✓ Excellent', css: 'color:#c9a84c' },
        ];

        const level = levels[Math.min(score, 5)];
        bar.style.width      = password.length ? level.width : '0%';
        bar.style.background = level.color;
        text.innerHTML       = password.length
            ? `<span style="${level.css}">${level.label}</span>`
            : '';
    }
</script>
@endpush

@endsection