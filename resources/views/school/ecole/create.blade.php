@extends('layouts.ecole')

@section('content')

<style>
    .form-grid-2 {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    .form-grid-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
    }
    .form-actions {
        grid-column: 1 / -1;
        display: flex;
        justify-content: flex-end;
        margin-top: 10px;
    }
    @media (max-width: 992px) {
        .form-grid-2, .form-grid-3 { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 768px) {
        .form-grid-2, .form-grid-3 { grid-template-columns: 1fr; }
        .form-actions { grid-column: span 1; }
    }
</style>

<div class="page-header">
    <h1 class="greeting">Créer mon école</h1>
    <p class="greeting-sub">Informations de l'établissement et du directeur</p>
</div>

<div class="settings-grid">

    {{-- COLONNE GAUCHE : FORMULAIRE --}}
    <div style="grid-column: span 2;">
        <section class="settings-section">
            <div class="card">

                <form method="POST" action="{{ route('school.ecole.store') }}" enctype="multipart/form-data">
                    @csrf

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    {{-- INFORMATIONS ÉCOLE --}}
                    <h2 class="settings-title">
                        <i class="fa-solid fa-school" style="color:#2563eb; margin-right:8px;"></i>
                        Informations de l'école
                    </h2>

                    <div class="form-grid-2" style="margin-bottom:20px;">

                        <div class="form-group">
                            <label class="form-label">Nom de l'école *</label>
                            <input type="text" name="ecole[nom]" value="{{ old('ecole.nom') }}"
                                class="form-input @error('ecole.nom') is-invalid @enderror"
                                placeholder="Ex: EPP DOSSOUVIE">
                            @error('ecole.nom')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Numéro d'autorisation *</label>
                            <input type="text" name="ecole[numero_autorisation]"
                                value="{{ old('ecole.numero_autorisation') }}"
                                class="form-input @error('ecole.numero_autorisation') is-invalid @enderror"
                                placeholder="Ex: 2024-001">
                            @error('ecole.numero_autorisation')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Téléphone</label>
                            <input type="text" name="ecole[telephone]"
                                value="{{ old('ecole.telephone') }}"
                                class="form-input @error('ecole.telephone') is-invalid @enderror"
                                placeholder="Ex: 01 96 00 00 00">
                            @error('ecole.telephone')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Adresse</label>
                            <input type="text" name="ecole[adresse]"
                                value="{{ old('ecole.adresse') }}"
                                class="form-input @error('ecole.adresse') is-invalid @enderror"
                                placeholder="Ex: Cotonou, Bénin">
                            @error('ecole.adresse')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                    </div>

                    <hr style="margin: 25px 0; border-color: #e5e7eb;">

                    {{-- INFORMATIONS DIRECTEUR --}}
                    <h2 class="settings-title">
                        <i class="fa-solid fa-user-tie" style="color:#2563eb; margin-right:8px;"></i>
                        Informations du directeur
                    </h2>

                    <div class="form-grid-3" style="margin-bottom:20px;">

                        <div class="form-group">
                            <label class="form-label">Nom *</label>
                            <input type="text" name="directeur[nom]"
                                value="{{ old('directeur.nom') }}"
                                class="form-input @error('directeur.nom') is-invalid @enderror"
                                placeholder="Ex: HOUNGUE">
                            @error('directeur.nom')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Prénom *</label>
                            <input type="text" name="directeur[prenom]"
                                value="{{ old('directeur.prenom') }}"
                                class="form-input @error('directeur.prenom') is-invalid @enderror"
                                placeholder="Ex: Fatima">
                            @error('directeur.prenom')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Sexe</label>
                            <select name="directeur[sexe]"
                                class="form-input @error('directeur.sexe') is-invalid @enderror">
                                <option value="">— Choisir —</option>
                                <option value="M" {{ old('directeur.sexe') == 'M' ? 'selected' : '' }}>Masculin</option>
                                <option value="F" {{ old('directeur.sexe') == 'F' ? 'selected' : '' }}>Féminin</option>
                            </select>
                            @error('directeur.sexe')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Téléphone</label>
                            <input type="text" name="directeur[telephone]"
                                value="{{ old('directeur.telephone') }}"
                                class="form-input @error('directeur.telephone') is-invalid @enderror"
                                placeholder="Ex: 01 96 00 00 00">
                            @error('directeur.telephone')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="directeur[email]"
                                value="{{ old('directeur.email') }}"
                                class="form-input @error('directeur.email') is-invalid @enderror"
                                placeholder="Ex: directeur@ecole.bj">
                            @error('directeur.email')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Colonne vide pour aligner --}}
                        <div></div>

                        <div class="form-group">
                            <label class="form-label">
                                Signature * <small style="color:#999;">(PNG, JPG)</small>
                            </label>
                            <input type="file" name="directeur[signature]" accept="image/png,image/jpg,image/jpeg"
                                class="form-input @error('directeur.signature') is-invalid @enderror">
                            @error('directeur.signature')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                Cachet * <small style="color:#999;">(PNG, JPG)</small>
                            </label>
                            <input type="file" name="directeur[cachet]" accept="image/png,image/jpg,image/jpeg"
                                class="form-input @error('directeur.cachet') is-invalid @enderror">
                            @error('directeur.cachet')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary" style="min-width:220px;">
                                <i class="fa-solid fa-save" style="margin-right:6px;"></i>
                                Enregistrer mon école
                            </button>
                        </div>

                    </div>
                </form>

            </div>
        </section>
    </div>

    {{-- COLONNE DROITE --}}
    <div>
        <section class="settings-section">
            <div class="card">
                <h2 class="settings-title">Règle de gestion</h2>
                <p class="settings-desc">
                    Un compte directeur ne peut gérer
                    <strong>qu'une seule école</strong>.
                </p>
                <div class="settings-row">
                    <div class="settings-row-info">
                        <div class="settings-row-label">École unique</div>
                        <div class="settings-row-desc">Cette action est définitive</div>
                    </div>
                    <span class="badge badge-green">Activé</span>
                </div>
            </div>
        </section>
    </div>

</div>
@endsection