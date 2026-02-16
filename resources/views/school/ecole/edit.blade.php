@extends('layouts.ecole')

@section('content')
<div class="page-header">
    <h1 class="greeting">Mon établissement</h1>
    <p class="greeting-sub">Informations de votre école</p>
</div>

<div class="settings-grid">
    <!-- COLONNE GAUCHE : ÉCOLE -->
    <div>
        <section class="settings-section">
            <div class="card">
                <h2 class="settings-title">Informations de l’école</h2>

                <form method="POST"
                      action="{{ route('school.ecole.update') }}"
                      enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label class="form-label">Nom de l’école</label>
                        <input type="text"
                               name="nom_ecole"
                               class="form-input"
                               value="{{ old('nom_ecole', $ecole->nom_ecole ?? '') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Numéro d’autorisation</label>
                        <input type="text"
                            name="numero_autorisation"
                            class="form-input"
                            value="{{ old('numero_autorisation', $ecole->numero_autorisation ?? '') }}"
                            readonly>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Téléphone</label>
                        <input type="text"
                               name="telephone"
                               class="form-input"
                               value="{{ old('telephone', $ecole->telephone ?? '') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Adresse</label>
                        <input type="text"
                               name="adresse_ecole"
                               class="form-input"
                               value="{{ old('adresse_ecole', $ecole->adresse_ecole ?? '') }}">
                    </div>

                    <button class="btn btn-primary">Enregistrer</button>
                </form>
            </div>
        </section>
    </div>

    <!-- COLONNE DROITE : DIRECTEUR -->
    <div>
        <section class="settings-section">
            <div class="card">
                <h2 class="settings-title">Directeur</h2>

                <form method="POST"
                      action="{{ route('school.ecole.update') }}"
                      enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label class="form-label">Nom</label>
                        <input type="text"
                               name="directeur_nom"
                               class="form-input"
                               value="{{ old('directeur_nom', $ecole->directeur->nom ?? '') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Prénom</label>
                        <input type="text"
                               name="directeur_prenom"
                               class="form-input"
                               value="{{ old('directeur_prenom', $ecole->directeur->prenom ?? '') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Sexe</label>
                        <select name="directeur_sexe" class="form-input">
                            <option value="">-- Choisir --</option>
                            <option value="M" @selected(($ecole->directeur->sexe ?? '') === 'M')>Masculin</option>
                            <option value="F" @selected(($ecole->directeur->sexe ?? '') === 'F')>Féminin</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Signature (image)</label>
                        <input type="file" name="signature" class="form-input">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Cachet (image)</label>
                        <input type="file" name="cachet" class="form-input">
                    </div>

                    <button class="btn btn-primary">Enregistrer</button>
                </form>
            </div>
        </section>
    </div>
</div>
@endsection
