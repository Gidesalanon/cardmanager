@extends('layouts.app')

@section('content')

   

<div class="page-header">
    <h1 class="greeting">Modifier l'élève</h1>
    <p class="greeting-sub">{{ $eleve->nom }} {{ $eleve->prenom }}</p>
</div>

<div class="settings-grid">
    <div>
        <section class="settings-section">
            <div class="card">

                <form method="POST"
                      action="{{ route('admin.students.update',$eleve) }}"
                      enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label class="form-label">École</label>
                        <select name="ecole_id" class="form-input" required>
                            @foreach($ecoles as $ecole)
                                <option value="{{ $ecole->id }}" 
                                    {{ $eleve->ecole_id == $ecole->id ? 'selected' : '' }}>
                                    {{ $ecole->nom_ecole }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Classe</label>
                        <select name="classe_id" class="form-input" required>
                            @foreach($classes as $classe)
                                <option value="{{ $classe->id }}" 
                                    {{ $eleve->classe_id == $classe->id ? 'selected' : '' }}>
                                    {{ $classe->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nom</label>
                        <input type="text" name="nom" class="form-input"
                               value="{{ old('nom',$eleve->nom) }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Prénom</label>
                        <input type="text" name="prenom" class="form-input"
                               value="{{ old('prenom',$eleve->prenom) }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Sexe</label>
                        <select name="sexe" class="form-input" required>
                            <option value="M" {{ $eleve->sexe=='M'?'selected':'' }}>Masculin</option>
                            <option value="F" {{ $eleve->sexe=='F'?'selected':'' }}>Féminin</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Date de naissance</label>
                        <input type="date" name="date_naissance"
                               class="form-input"
                               value="{{ old('date_naissance',$eleve->date_naissance?->format('Y-m-d')) }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Lieu de naissance</label>
                        <input type="text" name="lieu_naissance"
                               class="form-input"
                               value="{{ old('lieu_naissance',$eleve->lieu_naissance) }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Téléphone tuteur</label>
                        <input type="text" name="telephone_tuteur"
                               class="form-input"
                               value="{{ old('telephone_tuteur',$eleve->telephone_tuteur) }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Photo actuelle</label>
                        @if($eleve->photo)
                            <img src="{{ asset('storage/'.$eleve->photo) }}"
                                 width="120" style="margin-bottom:10px;">
                        @endif
                        <input type="file" name="photo" class="form-input">
                    </div>

                    <div style="display:flex; gap:10px; margin-top:20px;">
                        <button class="btn btn-primary">
                            Enregistrer les modifications
                        </button>

                        <a href="{{ route('admin.students.index') }}"
                           class="btn btn-light">
                            Retour
                        </a>
                    </div>

                </form>

            </div>
        </section>

    </div>

    <div class="settings-grid">
   

    
    </div>
@endsection
