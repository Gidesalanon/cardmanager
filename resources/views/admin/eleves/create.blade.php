@extends('layouts.app')

@section('content')
    <div class="page-header">
        <h1 class="greeting">Nouvel élève</h1>
        <p class="greeting-sub">Enregistrement d'un élève</p>
    </div>

    <div class="settings-grid">
        <div>
            <section class="settings-section">
                <div class="card">

                    <form method="POST" action="{{ route('admin.students.store') }}" enctype="multipart/form-data">
                        @csrf

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

                        {{-- PHOTO --}}
                        <div class="form-group">
                            <label class="form-label">Photo</label>
                            <input type="file" name="photo" class="form-input" accept="image/*" required>
                        </div>

                        <button class="btn btn-primary">
                            Enregistrer l'élève
                        </button>

                    </form>

                </div>
            </section>
        </div>
    </div>

    {{-- SCRIPT SERIE --}}
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
