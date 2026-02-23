@extends('layouts.app_school')

@section('content')

<style>
    /* Conteneur de la grille */
    .form-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr); /* 3 colonnes égales */
        gap: 20px; /* Espace entre les champs */
        padding: 10px;
    }

    /* Pour que le bouton prenne une ligne complète à la fin ou soit aligné à droite */
    .form-actions {
        grid-column: span 3; /* Le bouton s'étale sur les 3 colonnes */
        display: flex;
        justify-content: flex-end;
        margin-top: 10px;
    }

    /* Responsivité : 2 colonnes sur tablette, 1 sur mobile */
    @media (max-width: 992px) {
        .form-grid { grid-template-columns: repeat(2, 1fr); }
        .form-actions { grid-column: span 2; }
    }
    @media (max-width: 768px) {
        .form-grid { grid-template-columns: 1fr; }
        .form-actions { grid-column: span 1; }
    }
</style>

<div class="page-header">
    <h1 class="greeting">Modifier l'élève</h1>
    <p class="greeting-sub">{{ $eleve->nom }} {{ $eleve->prenom }}</p>
</div>

<div class="settings-grid">
    <div style="grid-column: span 2;"> {{-- On élargit la section pour laisser de la place aux 3 colonnes --}}
        <section class="settings-section">
            <div class="card">
                <form method="POST"
                      action="{{ route('school.students.update',$eleve) }}"
                      enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="form-grid">
                        {{-- LIGNE 1 --}}
                        {{-- CLASSE --}}
                        <div class="form-group">
                            <label class="form-label">Classe</label>
                            <select name="classe_id" id="classe_id" class="form-input" required>
                                <option value="">Sélectionnez une classe</option>
                                @foreach($classes as $classe)
                                    <option value="{{ $classe->id }}" 
                                        {{ $eleve->classe_id == $classe->id ? 'selected' : '' }}
                                        data-nom="{{ $classe->nom }}">
                                        {{ $classe->nom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- SERIE (S'affichera dynamiquement) --}}
                        <div class="form-group" id="serie-group" style="display:none;">
                            <label class="form-label">Série</label>
                            <select name="serie" id="serie" class="form-input">
                                <option value="">Sélectionnez une série</option>
                                @foreach(\App\Models\Serie::orderBy('nom')->get() as $serie)
                                    <option value="{{ $serie->nom }}" 
                                        {{ old('serie', $eleve->serie) == $serie->nom ? 'selected' : '' }}>
                                        {{ $serie->nom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- MATRICULE --}}
                        <div class="form-group">
                            <label class="form-label">Matricule</label>
                            <input type="text" name="matricule_edumaster" class="form-input" 
                                   value="{{ old('matricule_edumaster', $eleve->matricule_edumaster) }}" required>
                        </div>

                        {{-- LIGNE 2 --}}
                        {{-- NOM --}}
                        <div class="form-group">
                            <label class="form-label">Nom</label>
                            <input type="text" name="nom" class="form-input"
                                   value="{{ old('nom',$eleve->nom) }}" required>
                        </div>

                        {{-- PRENOM --}}
                        <div class="form-group">
                            <label class="form-label">Prénom</label>
                            <input type="text" name="prenom" class="form-input"
                                   value="{{ old('prenom',$eleve->prenom) }}" required>
                        </div>

                        {{-- SEXE --}}
                        <div class="form-group">
                            <label class="form-label">Sexe</label>
                            <select name="sexe" class="form-input" required>
                                <option value="">Sélectionnez</option>
                                <option value="M" {{ $eleve->sexe=='M'?'selected':'' }}>Masculin</option>
                                <option value="F" {{ $eleve->sexe=='F'?'selected':'' }}>Féminin</option>
                            </select>
                        </div>

                        {{-- LIGNE 3 --}}
                        {{-- DATE NAISSANCE --}}
                        <div class="form-group">
                            <label class="form-label">Date de naissance</label>
                            <input type="date" name="date_naissance"
                                   class="form-input"
                                   value="{{ old('date_naissance',$eleve->date_naissance?->format('Y-m-d')) }}" required>
                        </div>

                        {{-- LIEU NAISSANCE --}}
                        <div class="form-group">
                            <label class="form-label">Lieu de naissance</label>
                            <input type="text" name="lieu_naissance"
                                   class="form-input"
                                   value="{{ old('lieu_naissance',$eleve->lieu_naissance) }}" required>
                        </div>

                        {{-- TELEPHONE --}}
                        <div class="form-group">
                            <label class="form-label">Téléphone du tuteur</label>
                            <input type="text" name="telephone_tuteur"
                                   class="form-input"
                                   value="{{ old('telephone_tuteur',$eleve->telephone_tuteur) }}" required>
                        </div>

                        {{-- LIGNE 4 --}}
                        {{-- PHOTO --}}
                        <div class="form-group">
                            <label class="form-label">Photo</label>
                            @if($eleve->photo)
                                <img src="{{ asset('storage/'.$eleve->photo) }}"
                                     width="80" style="margin-bottom:5px; border-radius:4px;">
                            @endif
                            <input type="file" name="photo" class="form-input" accept="image/*">
                        </div>

                        {{-- BOUTON ACTIONS --}}
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary" style="min-width: 200px;">
                                Enregistrer les modifications
                            </button>
                            <a href="{{ route('school.students.index') }}"
                               class="btn btn-light" style="margin-left: 10px;">
                                Retour
                            </a>
                        </div>
                    </div> {{-- Fin form-grid --}}
                </form>
            </div>
        </section>
    </div>
</div>

{{-- SCRIPT SERIE --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const classeSelect = document.getElementById('classe_id');
    const serieGroup   = document.getElementById('serie-group');
    const serieSelect  = document.getElementById('serie');

    // Initialisation au chargement
    let selectedOption = classeSelect.options[classeSelect.selectedIndex];
    let nom = selectedOption.getAttribute('data-nom');
    
    if (nom) {
        let lower = nom.toLowerCase();
        if (
            lower.includes('2nde') ||
            lower.includes('1ère') ||
            lower.includes('1ere') ||
            lower.includes('tle') ||
            lower.includes('terminale')
        ) {
            serieGroup.style.display = 'block';
        }
    }

    classeSelect.addEventListener('change', function () {
        selectedOption = this.options[this.selectedIndex];
        nom = selectedOption.getAttribute('data-nom');

        if (!nom) {
            serieGroup.style.display = 'none';
            serieSelect.value = '';
            return;
        }

        let lower = nom.toLowerCase();
        // Détection des classes du secondaire pour afficher le champ Série
        if (
            lower.includes('2nde') ||
            lower.includes('1ère') ||
            lower.includes('1ere') ||
            lower.includes('tle') ||
            lower.includes('terminale')
        ) {
            serieGroup.style.display = 'block';
        } else {
            serieGroup.style.display = 'none';
            serieSelect.value = '';
        }
    });
});
</script>
@endpush

@endsection
