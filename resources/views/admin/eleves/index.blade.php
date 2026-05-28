@extends('layouts.app')

@section('content')

<style>
.circle-btn {
    width: 35px; height: 35px; border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    border: none; cursor: pointer; font-size: 14px; color: #fff;
    transition: transform 0.2s;
}
.circle-btn:hover { transform: scale(1.1); }
.circle-edit   { background: #c9a84c; }
.circle-delete { background: #dc2626; }
.circle-pdf    { background: #2563eb; }
.circle-img    { background: #059669; }

.filter-bar {
    padding: 18px 20px;
    border-bottom: 1px solid var(--border-color);
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 1fr auto;
    gap: 14px;
    align-items: end;
}
.filter-bar .form-label { font-size: 0.78rem; margin-bottom: 5px; font-weight: 600; }

.active-filters {
    padding: 10px 20px;
    background: rgba(201,168,76,0.07);
    border-left: 3px solid #c9a84c;
    font-size: 0.82rem;
    color: var(--text-secondary);
}

.pagination-bar {
    padding: 16px 20px;
    display: flex; justify-content: center; align-items: center; gap: 10px;
}
.page-info {
    padding: 7px 16px;
    background: var(--bg-surface);
    border-radius: 6px;
    font-size: 0.83rem;
    color: var(--text-secondary);
}

.num-table-badge {
    display: inline-block;
    background: rgba(201,168,76,0.12);
    color: #92700a;
    border: 1px solid rgba(201,168,76,0.3);
    border-radius: 5px;
    padding: 2px 7px;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.5px;
}

/* Header actions — tout sur une seule ligne */
.header-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

@media (max-width: 900px) {
    .filter-bar { grid-template-columns: 1fr 1fr; }
    .header-actions { flex-wrap: wrap; }
}
@media (max-width: 600px) {
    .filter-bar { grid-template-columns: 1fr; }
}
</style>

<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">Élèves</h3>
            <p class="card-subtitle">Gestion des élèves — Année {{ $activeYear->label ?? '' }}</p>
        </div>

        {{-- Toute la ligne d'actions : Nouvel élève + select école + bouton générer --}}
        <div class="header-actions">

            <a href="{{ route('admin.students.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Nouvel élève
            </a>

            <form method="GET" action="{{ route('admin.students.export.ecole.cards') }}"
                  style="display:flex; align-items:center; gap:8px;"
                  onsubmit="return validerGenerationMassive(this)">
                <select name="ecole_id" id="select-ecole-massive"
                        class="form-input" style="min-width:180px; height:38px;">
                    <option value="">-- Choisir une école --</option>
                    @foreach($ecoles as $ecole)
                        <option value="{{ $ecole->id }}">{{ $ecole->nom_ecole }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn"
                        style="background:linear-gradient(135deg,#7c3aed,#a855f7);
                               color:#fff; display:flex; align-items:center;
                               gap:6px; white-space:nowrap; height:38px;">
                    <i class="fa-solid fa-layer-group"></i> Générer les cartes
                </button>
            </form>

        </div>
    </div>

    {{-- Filtres --}}
    <form method="GET" action="{{ route('admin.students.index') }}" class="filter-bar">
        <div>
            <label class="form-label">École</label>
            <select name="ecole_id" class="form-input" style="width:100%;">
                <option value="">Toutes les écoles</option>
                @foreach($ecoles as $ecole)
                    <option value="{{ $ecole->id }}"
                        {{ $filters['ecole_id'] == $ecole->id ? 'selected' : '' }}>
                        {{ $ecole->nom_ecole }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Classe</label>
            <select name="classe_id" class="form-input" style="width:100%;">
                <option value="">Toutes les classes</option>
                @foreach($classes as $classe)
                    <option value="{{ $classe->id }}"
                        {{ $filters['classe_id'] == $classe->id ? 'selected' : '' }}>
                        {{ $classe->nom }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Nom de l'élève</label>
            <input type="text" name="nom" class="form-input" style="width:100%;"
                   placeholder="Rechercher par nom..." value="{{ $filters['nom'] ?? '' }}">
        </div>
        <div>
            <label class="form-label">Sexe</label>
            <select name="sexe" class="form-input" style="width:100%;">
                <option value="">Tous</option>
                <option value="M" {{ ($filters['sexe'] ?? '') == 'M' ? 'selected' : '' }}>Masculin</option>
                <option value="F" {{ ($filters['sexe'] ?? '') == 'F' ? 'selected' : '' }}>Féminin</option>
            </select>
        </div>
        <div style="display:flex; gap:8px; align-items:center;">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-filter"></i> Filtrer
            </button>
            <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">
                <i class="fa-solid fa-rotate-right"></i>
            </a>
        </div>
    </form>

    {{-- Filtres actifs --}}
    @if(array_filter($filters))
    <div class="active-filters">
        @if($filters['ecole_id']) <strong>École :</strong> {{ $ecoles->find($filters['ecole_id'])->nom_ecole ?? '' }} &nbsp;|&nbsp; @endif
        @if($filters['classe_id']) <strong>Classe :</strong> {{ $classes->find($filters['classe_id'])->nom ?? '' }} &nbsp;|&nbsp; @endif
        @if($filters['nom']) <strong>Nom :</strong> "{{ $filters['nom'] }}" &nbsp;|&nbsp; @endif
        @if($filters['sexe']) <strong>Sexe :</strong> {{ $filters['sexe'] == 'M' ? 'Masculin' : 'Féminin' }} &nbsp;|&nbsp; @endif
        <strong>{{ $eleves->total() }} résultat{{ $eleves->total() > 1 ? 's' : '' }}</strong>
    </div>
    @endif

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Matricule</th>
                    <th>N° Table</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Sexe</th>
                    <th>Naissance</th>
                    <th>Téléphone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($eleves as $eleve)
                <tr>
                    <td>
                        @if($eleve->photo)
                            <img src="{{ asset('storage/'.$eleve->photo) }}" width="40" height="40"
                                 style="object-fit:cover; border-radius:6px;">
                        @else
                            <div style="width:40px; height:40px; border-radius:6px;
                                        background:linear-gradient(135deg,#c9a84c,#f0d080);
                                        display:flex; align-items:center; justify-content:center;
                                        color:#0a0a0a; font-weight:700; font-size:0.9rem;">
                                {{ strtoupper(substr($eleve->nom, 0, 1)) }}
                            </div>
                        @endif
                    </td>
                    <td>{{ $eleve->matricule_edumaster ?? '—' }}</td>
                    <td>
                        @if($eleve->numero_table)
                            <span class="num-table-badge">{{ $eleve->numero_table }}</span>
                        @else
                            <span style="color:var(--text-secondary); font-size:0.8rem;">—</span>
                        @endif
                    </td>
                    <td><strong>{{ $eleve->nom }}</strong></td>
                    <td>{{ $eleve->prenom }}</td>
                    <td>
                        <span class="badge {{ $eleve->sexe == 'F' ? 'badge-orange' : 'badge-blue' }}">
                            {{ $eleve->sexe == 'F' ? 'F' : 'M' }}
                        </span>
                    </td>
                    <td>{{ $eleve->date_naissance
                        ? \Carbon\Carbon::parse($eleve->date_naissance)->format('d/m/Y')
                        : '—' }}</td>
                    <td>{{ $eleve->telephone_tuteur }}</td>
                    <td>
                        <div style="display:flex; gap:6px; flex-wrap:wrap;">
                            <a href="{{ route('admin.students.edit', $eleve) }}"
                               class="circle-btn circle-edit" title="Modifier">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <form id="delete-form-{{ $eleve->id }}"
                                  action="{{ route('admin.students.destroy', $eleve) }}"
                                  method="POST" style="display:none;">
                                @csrf
                                @method('DELETE')
                            </form>
                            <button type="button"
                                    onclick="confirmDelete({{ $eleve->id }}, '{{ addslashes($eleve->nom . ' ' . $eleve->prenom) }}')"
                                    class="circle-btn circle-delete" title="Supprimer">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                            <a href="{{ route('admin.students.export.card.pdf', $eleve) }}"
                               class="circle-btn circle-pdf" title="Exporter PDF">
                                <i class="fa-solid fa-download"></i>
                            </a>
                            <a href="{{ route('admin.students.export.card.image', $eleve) }}"
                               class="circle-btn circle-img" title="Exporter Image"
                               onclick="event.preventDefault(); alert('Fonctionnalité bientôt disponible.');">
                                <i class="fa-solid fa-file-image"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center; padding:2rem; color:var(--text-secondary);">
                        Aucun élève trouvé.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($eleves->hasPages())
    <div class="pagination-bar">
        @if($eleves->onFirstPage())
            <button class="btn btn-secondary" disabled>← Précédent</button>
        @else
            <a href="{{ $eleves->previousPageUrl() }}" class="btn btn-secondary">← Précédent</a>
        @endif
        <span class="page-info">
            Page {{ $eleves->currentPage() }} / {{ $eleves->lastPage() }}
            ({{ $eleves->total() }} élève{{ $eleves->total() > 1 ? 's' : '' }})
        </span>
        @if($eleves->hasMorePages())
            <a href="{{ $eleves->nextPageUrl() }}" class="btn btn-secondary">Suivant →</a>
        @else
            <button class="btn btn-secondary" disabled>Suivant →</button>
        @endif
    </div>
    @endif

</div>

@endsection

@push('scripts')
<script>
function validerGenerationMassive(form) {
    const select = document.getElementById('select-ecole-massive');
    if (!select.value) {
        Swal.fire({ icon: 'warning', title: 'École non sélectionnée',
            text: 'Veuillez sélectionner une école avant de générer les cartes.' });
        return false;
    }
    Swal.fire({ title: 'Génération en cours...', icon: 'info',
        showConfirmButton: false, timer: 2000, timerProgressBar: true });
    return true;
}

function confirmDelete(id, name) {
    Swal.fire({
        title: 'Supprimer l\'élève ?',
        text: "Vous allez supprimer " + name + ". Cette action est irréversible.",
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#dc2626', cancelButtonColor: '#6b7280',
        confirmButtonText: 'Oui, supprimer !', cancelButtonText: 'Annuler',
        reverseButtons: true
    }).then(r => { if (r.isConfirmed) document.getElementById('delete-form-' + id).submit(); });
}

document.addEventListener('DOMContentLoaded', function () {
    @if(session('success'))
        Swal.fire({ icon: 'success', title: 'Succès', text: "{{ session('success') }}",
            timer: 3000, showConfirmButton: false, toast: true, position: 'top-end' });
    @endif
    @if(session('error'))
        Swal.fire({ icon: 'error', title: 'Erreur', text: "{{ session('error') }}" });
    @endif
});
</script>
@endpush