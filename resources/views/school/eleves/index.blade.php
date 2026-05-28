@extends('layouts.app_school')

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

.filter-chips {
    padding: 14px 20px;
    border-bottom: 1px solid var(--border-color);
    display: flex; flex-wrap: wrap; gap: 8px; align-items: center;
}
.chip {
    padding: 6px 14px; border-radius: 100px;
    font-size: 0.82rem; font-weight: 600; cursor: pointer;
    text-decoration: none; transition: all 0.2s;
    border: 1px solid var(--border-color);
    color: var(--text-secondary);
    background: var(--bg-surface);
}
.chip.active, .chip:hover {
    background: linear-gradient(135deg, #c9a84c, #f0d080);
    color: #0a0a0a; border-color: #c9a84c;
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
</style>

<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">Mes élèves</h3>
            <p class="card-subtitle">Année scolaire {{ $activeYear->label ?? '' }}</p>
        </div>
        <a href="{{ route('school.students.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Nouvel élève
        </a>
    </div>

    {{-- Filtre par classe --}}
    <div class="filter-chips">
        <a href="{{ route('school.students.index') }}"
           class="chip {{ !$selectedClasse ? 'active' : '' }}">
            Toutes les classes
        </a>
        @foreach($classes as $classe)
            <a href="{{ route('school.students.index', ['classe_id' => $classe->id]) }}"
               class="chip {{ $selectedClasse == $classe->id ? 'active' : '' }}">
                {{ $classe->nom }}
            </a>
        @endforeach
    </div>

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
                    <th>Téléphone parent</th>
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
                    <td>{{ $eleve->date_naissance ? \Carbon\Carbon::parse($eleve->date_naissance)->format('d/m/Y') : '—' }}</td>
                    <td>{{ $eleve->telephone_tuteur }}</td>
                    <td>
                        <div style="display:flex; gap:6px;">
                            <a href="{{ route('school.students.edit', $eleve) }}"
                               class="circle-btn circle-edit" title="Modifier">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <form id="delete-form-{{ $eleve->id }}"
                                  action="{{ route('school.students.destroy', $eleve) }}"
                                  method="POST" style="display:none;">
                                @csrf
                                @method('DELETE')
                            </form>
                            <button type="button"
                                    onclick="confirmDelete({{ $eleve->id }}, '{{ addslashes($eleve->nom . ' ' . $eleve->prenom) }}')"
                                    class="circle-btn circle-delete" title="Supprimer">
                                <i class="fa-solid fa-trash"></i>
                            </button>
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
</div>

@endsection

@push('scripts')
<script>
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
        Swal.fire({ icon: 'success', title: 'Opération réussie',
            text: "{{ session('success') }}", timer: 3000,
            showConfirmButton: false, toast: true, position: 'top-end' });
    @endif
    @if(session('error'))
        Swal.fire({ icon: 'error', title: 'Erreur', text: "{{ session('error') }}" });
    @endif
});
</script>
@endpush