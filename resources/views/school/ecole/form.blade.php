@php
    $isEdit = isset($ecole);
@endphp

<form
    method="POST"
    action="{{ $isEdit ? route('school.ecole.update') : route('school.ecole.store') }}"
>
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    {{-- ================= ECOLE ================= --}}
    <div class="card mb-4">
        <div class="card-header">
            <strong>Informations de l’école</strong>
        </div>

        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Nom de l’école</label>
                <input type="text" name="nom"
                       class="form-control"
                       value="{{ old('nom', $ecole->nom ?? '') }}"
                       required>
            </div>

            <div class="mb-3">
                <label class="form-label">Téléphone</label>
                <input type="text" name="telephone"
                       class="form-control"
                       value="{{ old('telephone', $ecole->telephone ?? '') }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Adresse</label>
                <input type="text" name="adresse"
                       class="form-control"
                       value="{{ old('adresse', $ecole->adresse ?? '') }}">
            </div>
        </div>
    </div>

    {{-- ================= DIRECTEUR ================= --}}
    <div class="card mb-4">
        <div class="card-header">
            <strong>Informations du directeur</strong>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nom</label>
                    <input type="text" name="directeur_nom"
                           class="form-control"
                           value="{{ old('directeur_nom', $ecole->directeur->nom ?? '') }}"
                           required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Prénom</label>
                    <input type="text" name="directeur_prenom"
                           class="form-control"
                           value="{{ old('directeur_prenom', $ecole->directeur->prenom ?? '') }}"
                           required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Téléphone</label>
                <input type="text" name="directeur_telephone"
                       class="form-control"
                       value="{{ old('directeur_telephone', $ecole->directeur->telephone ?? '') }}"
                       required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="directeur_email"
                       class="form-control"
                       value="{{ old('directeur_email', $ecole->directeur->email ?? '') }}"
                       required>
            </div>
        </div>
    </div>

    <div class="text-end">
        <button class="btn btn-primary">
            {{ $isEdit ? 'Mettre à jour' : 'Enregistrer' }}
        </button>
    </div>
</form>
