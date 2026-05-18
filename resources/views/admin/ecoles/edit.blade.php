@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1 class="greeting">Modifier l'école</h1>
    <p class="greeting-sub">{{ $ecole->nom_ecole }}</p>
</div>

<div style="max-width: 600px; margin: 0 auto;">
    <div>
        <section class="settings-section">
            <div class="card">
                <form method="POST" action="{{ route('admin.ecoles.update', $ecole) }}"
                      enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <h3 class="settings-title">Informations de l'école</h3>

                    <div class="form-group">
                        <label class="form-label">Nom de l'école *</label>
                        <input type="text" name="ecole[nom]" class="form-input"
                               value="{{ old('ecole.nom', $ecole->nom_ecole) }}" required>
                        @error('ecole.nom')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">N° Autorisation *</label>
                        <input type="text" name="ecole[numero_autorisation]" class="form-input"
                               value="{{ old('ecole.numero_autorisation', $ecole->numero_autorisation) }}" required>
                        @error('ecole.numero_autorisation')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="ecole[telephone]" class="form-input"
                               value="{{ old('ecole.telephone', $ecole->telephone) }}">
                        @error('ecole.telephone')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Adresse</label>
                        <input type="text" name="ecole[adresse]" class="form-input"
                               value="{{ old('ecole.adresse', $ecole->adresse_ecole) }}">
                        @error('ecole.adresse')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <hr style="margin: 20px 0; border-color: rgba(255,255,255,0.1);">

                    <h3 class="settings-title">Informations du directeur</h3>

                    <div class="form-group">
                        <label class="form-label">Nom *</label>
                        <input type="text" name="directeur[nom]" class="form-input"
                               value="{{ old('directeur.nom', $ecole->directeur->nom ?? '') }}" required>
                        @error('directeur.nom')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Prénom *</label>
                        <input type="text" name="directeur[prenom]" class="form-input"
                               value="{{ old('directeur.prenom', $ecole->directeur->prenom ?? '') }}" required>
                        @error('directeur.prenom')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Sexe</label>
                        <select name="directeur[sexe]" class="form-input">
                            <option value="">— Choisir —</option>
                            <option value="M" {{ old('directeur.sexe', $ecole->directeur->sexe ?? '') == 'M' ? 'selected' : '' }}>Masculin</option>
                            <option value="F" {{ old('directeur.sexe', $ecole->directeur->sexe ?? '') == 'F' ? 'selected' : '' }}>Féminin</option>
                        </select>
                        @error('directeur.sexe')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="directeur[telephone]" class="form-input"
                               value="{{ old('directeur.telephone', $ecole->directeur->telephone ?? '') }}">
                        @error('directeur.telephone')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="directeur[email]" class="form-input"
                               value="{{ old('directeur.email', $ecole->directeur->email ?? '') }}">
                        @error('directeur.email')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            Signature <small style="opacity:0.6;">(laisser vide pour garder l'actuelle)</small>
                        </label>
                        @if($ecole->directeur?->signature)
                            <img src="{{ asset('storage/'.$ecole->directeur->signature) }}"
                                 style="height:45px; display:block; margin-bottom:8px; border-radius:4px;">
                        @endif
                        <input type="file" name="directeur[signature]" class="form-input"
                               accept="image/png,image/jpg,image/jpeg">
                        @error('directeur.signature')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            Cachet <small style="opacity:0.6;">(laisser vide pour garder l'actuel)</small>
                        </label>
                        @if($ecole->directeur?->cachet)
                            <img src="{{ asset('storage/'.$ecole->directeur->cachet) }}"
                                 style="height:45px; display:block; margin-bottom:8px; border-radius:4px;">
                        @endif
                        <input type="file" name="directeur[cachet]" class="form-input"
                               accept="image/png,image/jpg,image/jpeg">
                        @error('directeur.cachet')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div style="display:flex; gap:10px; margin-top:10px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-save"></i> Mettre à jour
                        </button>
                        <a href="{{ route('admin.ecoles.index') }}" class="btn btn-secondary">
                            Annuler
                        </a>
                    </div>

                </form>
            </div>
        </section>
    </div>
</div>
@endsection