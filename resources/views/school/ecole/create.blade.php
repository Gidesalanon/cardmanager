@extends('layouts.ecole')

@section('content')
    <div class="page-header">
        <h1 class="greeting">Créer mon école</h1>
        <p class="greeting-sub">Informations de l’établissement et du directeur</p>
    </div>

    <div class="settings-grid">

        {{-- COLONNE GAUCHE : FORMULAIRE --}}
        <div>
            <section class="settings-section">
                <div class="card">

                    <form method="POST" action="{{ route('school.ecole.store') }}" enctype="multipart/form-data">
                        @csrf

                        {{-- Message erreur session (ex: école déjà existante) --}}
                        @if (session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        {{-- =========================
                         INFORMATIONS ÉCOLE
                    ========================== --}}
                        <h2 class="settings-title">Informations de l’école</h2>

                        <div class="form-group">
                            <label class="form-label">Nom de l’école</label>
                            <input type="text" name="ecole[nom]" value="{{ old('ecole.nom') }}"
                                class="form-input @error('ecole.nom') is-invalid @enderror">

                            @error('ecole.nom')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Numéro d’autorisation</label>
                            <input type="text" name="ecole[numero_autorisation]"
                                value="{{ old('ecole.numero_autorisation') }}"
                                class="form-input @error('ecole.numero_autorisation') is-invalid @enderror">

                            @error('ecole.numero_autorisation')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Téléphone</label>
                            <input type="text" name="ecole[telephone]" value="{{ old('ecole.telephone') }}"
                                class="form-input @error('ecole.telephone') is-invalid @enderror">

                            @error('ecole.telephone')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Adresse</label>
                            <input type="text" name="ecole[adresse]" value="{{ old('ecole.adresse') }}"
                                class="form-input @error('ecole.adresse') is-invalid @enderror">

                            @error('ecole.adresse')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- =========================
                         INFORMATIONS DIRECTEUR
                    ========================== --}}
                        <h2 class="settings-title mt-6">Informations du directeur</h2>

                        <div class="form-group">
                            <label class="form-label">Nom</label>
                            <input type="text" name="directeur[nom]" value="{{ old('directeur.nom') }}"
                                class="form-input @error('directeur.nom') is-invalid @enderror">

                            @error('directeur.nom')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Prénom</label>
                            <input type="text" name="directeur[prenom]" value="{{ old('directeur.prenom') }}"
                                class="form-input @error('directeur.prenom') is-invalid @enderror">

                            @error('directeur.prenom')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Sexe</label>
                            <select name="directeur[sexe]" class="form-input @error('directeur.sexe') is-invalid @enderror">
                                <option value="">— Choisir —</option>
                                <option value="M" {{ old('directeur.sexe') == 'M' ? 'selected' : '' }}>Masculin
                                </option>
                                <option value="F" {{ old('directeur.sexe') == 'F' ? 'selected' : '' }}>Féminin
                                </option>
                            </select>

                            @error('directeur.sexe')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Téléphone</label>
                            <input type="text" name="directeur[telephone]" value="{{ old('directeur.telephone') }}"
                                class="form-input @error('directeur.telephone') is-invalid @enderror">

                            @error('directeur.telephone')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="directeur[email]" value="{{ old('directeur.email') }}"
                                class="form-input @error('directeur.email') is-invalid @enderror">

                            @error('directeur.email')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Signature (image)</label>
                            <input type="file" name="directeur[signature]"
                                class="form-input @error('directeur.signature') is-invalid @enderror">

                            @error('directeur.signature')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Cachet (image)</label>
                            <input type="file" name="directeur[cachet]"
                                class="form-input @error('directeur.cachet') is-invalid @enderror">

                            @error('directeur.cachet')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <button class="btn btn-primary mt-4">
                            Enregistrer mon école
                        </button>
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
                        <strong>qu’une seule école</strong>.
                    </p>

                    <div class="settings-row">
                        <div class="settings-row-info">
                            <div class="settings-row-label">École unique</div>
                            <div class="settings-row-desc">
                                Cette action est définitive
                            </div>
                        </div>
                        <span class="badge badge-green">Activé</span>
                    </div>
                </div>
            </section>
        </div>

    </div>
@endsection
