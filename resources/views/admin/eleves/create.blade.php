@extends('layouts.app')

@section('content')
    <style>
        /* Conteneur principal pour élargir la zone de saisie */
        .form-container-wide {
            max-width: 100%;
            /* Permet d'occuper plus d'espace pour les 3 colonnes */
        }

        /* Grille du formulaire */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            /* 3 colonnes égales */
            gap: 20px;
            /* Espace entre les champs */
            align-items: end;
            /* Aligne le bas des champs */
        }

        /* Gestion du bouton pour qu'il soit sur sa propre ligne à la fin */
        .form-actions {
            grid-column: span 3;
            /* Prend toute la largeur des 3 colonnes */
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Ajustement pour les petits écrans (Tablettes et Mobiles) */
        @media (max-width: 1024px) {
            .form-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .form-actions {
                grid-column: span 2;
            }
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-actions {
                grid-column: span 1;
            }
        }
    </style>

    <div class="page-header">
        <h1 class="greeting">Nouvel élève</h1>
        <p class="greeting-sub">Enregistrement d'un élève dans la base globale</p>
    </div>

    <div class="settings-grid">
        <div class="form-container-wide" style="grid-column: 1 / -1;">
            <section class="settings-section">
                <div class="card">
                    <form method="POST" action="{{ route('admin.students.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="form-grid">

                            {{-- --- LIGNE 1 --- --}}

                            {{-- ECOLE --}}
                            <div class="form-group">
                                <label class="form-label">École</label>
                                <select name="ecole_id" class="form-input" required>
                                    <option value="">Sélectionnez une école</option>

                                    @foreach ($ecoles as $ecole)
                                        <option value="{{ $ecole->id }}">
                                            {{ $ecole->nom_ecole }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- CLASSE --}}
                            <div class="form-group">
                                <label class="form-label">Classe</label>
                                <select name="classe_id" id="classe_id" class="form-input" required>
                                    <option value="">Sélectionnez une classe</option>
                                    @foreach ($classes as $classe)
                                        <option value="{{ $classe->id }}" data-nom="{{ $classe->nom }}">
                                            {{ $classe->nom }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- SERIE (apparait seulement pour 2nde / 1ère / Tle) --}}
                            <div class="form-group" id="serie-group" style="display:none;">
                                <label class="form-label">Série</label>
                                <select name="serie" id="serie" class="form-input">
                                    <option value="">Sélectionnez une série</option>
                                    @foreach (\App\Models\Serie::orderBy('nom')->get() as $serie)
                                        <option value="{{ $serie->nom }}">
                                            {{ $serie->nom }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- MATRICULE --}}
                            <div class="form-group">
                                <label class="form-label">Matricule</label>
                                <input type="text" name="matricule_edumaster" class="form-input" required>
                            </div>

                            {{-- --- LIGNE 2 --- --}}

                            {{-- NOM --}}
                            <div class="form-group">
                                <label class="form-label">Nom</label>
                                <input type="text" name="nom" class="form-input" required>
                            </div>

                            {{-- PRENOM --}}
                            <div class="form-group">
                                <label class="form-label">Prénom</label>
                                <input type="text" name="prenom" class="form-input" required>
                            </div>

                            {{-- SEXE --}}
                            <div class="form-group">
                                <label class="form-label">Sexe</label>
                                <select name="sexe" class="form-input" required>
                                    <option value="">Sélectionnez</option>
                                    <option value="M">Masculin</option>
                                    <option value="F">Féminin</option>
                                </select>
                            </div>

                            {{-- --- LIGNE 3 --- --}}

                            {{-- DATE NAISSANCE --}}
                            <div class="form-group">
                                <label class="form-label">Date de naissance</label>
                                <input type="date" name="date_naissance" class="form-input" required>
                            </div>

                            {{-- LIEU NAISSANCE --}}
                            <div class="form-group">
                                <label class="form-label">Lieu de naissance</label>
                                <input type="text" name="lieu_naissance" class="form-input" required>
                            </div>

                            {{-- TELEPHONE --}}
                            <div class="form-group">
                                <label class="form-label">Téléphone du tuteur</label>
                                <input type="text" name="telephone_tuteur" class="form-input" required>
                            </div>

                            {{-- --- LIGNE 4 --- --}}

                            {{-- PHOTO --}}
                            <div class="form-group">
                                <label class="form-label">Photo de l'élève</label>
                                <input type="file" name="photo" class="form-input" accept="image/*" required>
                            </div>

                            {{-- BOUTON D'ACTION --}}
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary" style="min-width: 200px;">
                                    Enregistrer l'élève
                                </button>
                            </div>

                        </div> {{-- Fin form-grid --}}
                    </form>
                </div>
            </section>
        </div>
    </div>

    {{-- SCRIPT SERIE DYNAMIQUE --}}
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const classeSelect = document.getElementById('classe_id');
                const serieGroup = document.getElementById('serie-group');
                const serieSelect = document.getElementById('serie');

                classeSelect.addEventListener('change', function() {
                    let selectedOption = this.options[this.selectedIndex];
                    let nom = selectedOption.getAttribute('data-nom');

                    if (!nom) {
                        serieGroup.style.display = 'none';
                        serieSelect.value = '';
                        return;
                    }

                    let lower = nom.toLowerCase();

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
