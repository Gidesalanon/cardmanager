<div class="row g-3">

    <div class="col-md-6">
        <label>Nom</label>
        <input type="text" name="nom" class="form-control"
               value="{{ old('nom', $eleve->nom ?? '') }}" required>
    </div>

    <div class="col-md-6">
        <label>Prénom</label>
        <input type="text" name="prenom" class="form-control"
               value="{{ old('prenom', $eleve->prenom ?? '') }}" required>
    </div>

    <div class="col-md-4">
        <label>Sexe</label>
        <select name="sexe" class="form-select" required>
            <option value="">--</option>
            <option value="M" @selected(old('sexe', $eleve->sexe ?? '') == 'M')>Masculin</option>
            <option value="F" @selected(old('sexe', $eleve->sexe ?? '') == 'F')>Féminin</option>
        </select>
    </div>

    <div class="col-md-4">
        <label>Date de naissance</label>
        <input type="date" name="date_naissance" class="form-control"
               value="{{ old('date_naissance', $eleve->date_naissance ?? '') }}">
    </div>

    <div class="col-md-4">
        <label>Numéro de table</label>
        <input type="text" name="numero_table" class="form-control"
               value="{{ old('numero_table', $eleve->numero_table ?? '') }}">
    </div>

    <div class="col-md-12">
        <label>Classe</label>
        <select name="partition_id" class="form-select" required>
            <option value="">-- Choisir une classe</option>
            @foreach($partitions as $partition)
                <option value="{{ $partition->id }}"
                    @selected(old('partition_id', $eleve->partition_id ?? '') == $partition->id)>
                    {{ $partition->classe->nom }}
                </option>
            @endforeach
        </select>
    </div>

</div>
