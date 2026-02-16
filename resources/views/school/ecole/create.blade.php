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

                <form method="POST"
                      action="{{ route('school.ecole.store') }}"
                      enctype="multipart/form-data">
                    @csrf

                    {{-- =========================
                         INFORMATIONS ÉCOLE
                    ========================== --}}
                    <h2 class="settings-title">Informations de l’école</h2>

                    <div class="form-group">
                        <label class="form-label">Nom de l’école</label>
                        <input type="text"
                               name="ecole[nom]"
                               class="form-input"
                               required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Numéro d’autorisation</label>
                        <input type="text"
                            name="ecole[numero_autorisation]"
                            class="form-input"
                            required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Téléphone</label>
                        <input type="text"
                            name="ecole[telephone]"
                            class="form-input"
                            required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Adresse</label>
                        <input type="text"
                            name="ecole[adresse]"
                            class="form-input"
                            required>
                    </div>

                    {{-- =========================
                         INFORMATIONS DIRECTEUR
                    ========================== --}}
                    <h2 class="settings-title mt-6">Informations du directeur</h2>

                    <div class="form-group">
                        <label class="form-label">Nom</label>
                        <input type="text"
                               name="directeur[nom]"
                               class="form-input"
                               required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Prénom</label>
                        <input type="text"
                               name="directeur[prenom]"
                               class="form-input"
                               required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Sexe</label>
                        <select name="directeur[sexe]" class="form-input">
                            <option value="">— Choisir —</option>
                            <option value="M">Masculin</option>
                            <option value="F">Féminin</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Téléphone</label>
                        <input type="text"
                               name="directeur[telephone]"
                               class="form-input">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email"
                               name="directeur[email]"
                               class="form-input">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Signature (image)</label>
                        <input type="file"
                               name="directeur[signature]"
                               class="form-input">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Cachet (image)</label>
                        <input type="file"
                               name="directeur[cachet]"
                               class="form-input">
                    </div>

                    <button class="btn btn-primary mt-4">
                        Enregistrer mon école
                    </button>
                </form>

            </div>
        </section>
    </div>

    {{-- COLONNE DROITE : INFO / RÈGLE --}}
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
