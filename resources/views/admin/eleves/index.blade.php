@extends('layouts.app')

@section('content')

<style>
.circle-btn{
    width:35px;
    height:35px;
    border-radius:50%;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    border:none;
    cursor:pointer;
    font-size:14px;
    color:#fff;
    transition: transform 0.2s;
}

/* Section des filtres */
.filter-section {
    background: #1f2937; 
}

/* Couleur des labels (École, Classe, etc.) */
.filter-label {
    color: #e5e7eb; 
}

/* Si tu veux que ça reste blanc en mode clair, utilise ceci à la place : */
[data-theme='dark'] .filter-section { background: #1f2937; }
[data-theme='dark'] .filter-label { color: #e5e7eb; }

/* Style pour les inputs dans les filtres pour éviter qu'ils soient trop clairs */
.form-input {
    background-color: #374151;
    border: 1px solid #4b5563;
    color: white;
    padding: 8px;
    border-radius: 6px;
}
.circle-btn:hover { transform: scale(1.1); }
.circle-edit{ background:#f59e0b; }
.circle-delete{ background:#dc2626; }
</style>

<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">Élèves</h3>
            <p class="card-subtitle">Gestion des élèves - Année scolaire {{ $activeYear->label ?? '' }}</p>
        </div>
        <a href="{{ route('admin.students.create') }}" class="btn btn-primary">Nouvel élève</a>
    </div>

{{-- Formulaire de filtres --}}
<div class="filter-section" style="padding:20px; border-bottom:1px solid rgba(255,255,255,0.1);">
    <form method="GET" action="{{ route('admin.students.index') }}" style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr auto; gap: 15px; align-items: end;">
        
        {{-- Filtre par école --}}
        <div>
            <label class="filter-label" style="display:block; margin-bottom:5px; font-weight:600;">École</label>
            <select name="ecole_id" class="form-input" style="width:100%;">
                <option value="">Toutes les écoles</option>
                @foreach($ecoles as $ecole)
                    <option value="{{ $ecole->id }}" {{ $filters['ecole_id'] == $ecole->id ? 'selected' : '' }}>
                        {{ $ecole->nom_ecole }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Filtre par classe --}}
        <div>
            <label class="filter-label" style="display:block; margin-bottom:5px; font-weight:600;">Classe</label>
            <select name="classe_id" class="form-input" style="width:100%;">
                <option value="">Toutes les classes</option>
                @foreach($classes as $classe)
                    <option value="{{ $classe->id }}" {{ $filters['classe_id'] == $classe->id ? 'selected' : '' }}>
                        {{ $classe->nom }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Filtre par nom --}}
        <div>
            <label class="filter-label" style="display:block; margin-bottom:5px; font-weight:600;">Nom de l'élève</label>
            <input type="text" name="nom" class="form-input" style="width:100%;" 
                   placeholder="Rechercher par nom..." value="{{ $filters['nom'] ?? '' }}">
        </div>

        {{-- Filtre par sexe --}}
        <div>
            <label class="filter-label" style="display:block; margin-bottom:5px; font-weight:600;">Sexe</label>
            <select name="sexe" class="form-input" style="width:100%;">
                <option value="">Tous</option>
                <option value="M" {{ ($filters['sexe'] ?? '') == 'M' ? 'selected' : '' }}>Masculin</option>
                <option value="F" {{ ($filters['sexe'] ?? '') == 'F' ? 'selected' : '' }}>Féminin</option>
            </select>
        </div>

        {{-- Boutons d'action --}}
        <div style="display: flex; gap: 10px; align-items: center;">
            <button type="submit" class="btn btn-primary">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: 5px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                </svg>
                Filtrer
            </button>
            <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: 5px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Réinitialiser
            </a>
        </div>
    </form>
</div>

    {{-- Résumé des filtres actifs --}}
    @if(array_filter($filters))
    <div style="padding:10px 20px; background:#e0f2fe; border-left:4px solid #0ea5e9; margin-bottom:10px;">
        <small style="color:#0c4a6e; font-weight:600;">
        
            @if($filters['ecole_id']) École: {{ $ecoles->find($filters['ecole_id'])->nom_ecole ?? '' }} @endif
            @if($filters['classe_id']) Classe: {{ $classes->find($filters['classe_id'])->nom ?? '' }} @endif
            @if($filters['nom']) Nom: "{{ $filters['nom'] }}" @endif
            @if($filters['sexe']) Sexe: {{ $filters['sexe'] == 'M' ? 'Masculin' : 'Féminin' }} @endif
            ({{ $eleves->total() }} résultat{{ $eleves->total() > 1 ? 's' : '' }})
        </small>
    </div>
    @endif

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Matricule</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Sexe</th>
                    <th>Date de naissance</th>
                    <th>Téléphone parent</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($eleves as $eleve)
                    <tr>
                        <td>
                            @if($eleve->photo)
                                <img src="{{ asset('storage/'.$eleve->photo) }}" width="40" height="40" style="object-fit:cover;border-radius:4px;">
                            @else - @endif
                        </td>
                        <td>{{ $eleve->matricule_edumaster }}</td>
                        <td>{{ $eleve->nom }}</td>
                        <td>{{ $eleve->prenom }}</td>
                        <td>{{ $eleve->sexe }}</td>
                        <td>{{ $eleve->date_naissance ? \Carbon\Carbon::parse($eleve->date_naissance)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $eleve->telephone_tuteur }}</td>

                        <td>
                            <div style="display:flex; gap:8px;">
                                <a href="{{ route('admin.students.edit',$eleve) }}" class="circle-btn circle-edit" title="Modifier"><i class="fa-solid fa-pen"></i></a>

                                {{-- Formulaire avec ID unique --}}
                                <form id="delete-form-{{ $eleve->id }}" action="{{ route('admin.students.destroy',$eleve) }}" method="POST" style="display:none;">
                                    @csrf
                                    @method('DELETE')
                                </form>

                                {{-- Bouton appelant la fonction SweetAlert --}}
                                <button type="button" 
                                        onclick="confirmDelete({{ $eleve->id }}, '{{ addslashes($eleve->nom . ' ' . $eleve->prenom) }}')" 
                                        class="circle-btn circle-delete" 
                                        title="Supprimer">
                                    <i class="fa-solid fa-trash"></i>
                                </button>

                                <a href="{{ route('admin.students.export.card.pdf',$eleve) }}" class="circle-btn" style="background:#2563eb;" title="Exporter PDF"> <i class="fa-solid fa-download"></i></a>
                                <a href="{{ route('admin.students.export.card.image',$eleve) }}" class="circle-btn" style="background:#059669;" title="Exporter Image" onclick="event.preventDefault(); alert('⚠️ Cette fonctionnalité n\'est pas encore disponible.');"> <i class="fa-solid fa-file-image"></i></a>
                            </div>
                            
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" style="text-align:center;">Aucun élève trouvé.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($eleves->hasPages())
    <div style="padding: 20px; display: flex; justify-content: center; align-items: center; gap: 10px;">
        {{-- Bouton précédent --}}
        @if($eleves->onFirstPage())
            <button class="btn btn-light" disabled>
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Précédent
            </button>
        @else
            <a href="{{ $eleves->previousPageUrl() }}" class="btn btn-light">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Précédent
            </a>
        @endif

        {{-- Informations de pagination --}}
        <span style="padding: 8px 16px; background: #f3f4f6; border-radius: 6px; font-size: 14px; color: #6b7280;">
            Page {{ $eleves->currentPage() }} sur {{ $eleves->lastPage() }}
            ({{ $eleves->total() }} élève{{ $eleves->total() > 1 ? 's' : '' }})
        </span>

        {{-- Bouton suivant --}}
        @if($eleves->hasMorePages())
            <a href="{{ $eleves->nextPageUrl() }}" class="btn btn-light">
                Suivant
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        @else
            <button class="btn btn-light" disabled>
                Suivant
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        @endif
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
    // 1. Fonction de suppression
    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Êtes-vous sûr ?',
            text: "Vous allez supprimer l'élève " + name + ". Cette action est irréversible !",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626', // Rouge (delete)
            cancelButtonColor: '#6b7280', // Gris (cancel)
            confirmButtonText: 'Oui, supprimer !',
            cancelButtonText: 'Annuler',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        })
    }

    // 2. Gestion des messages Flash (Success/Error) avec SweetAlert
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Succès !',
                text: "{{ session('success') }}",
                timer: 3000,
                showConfirmButton: false
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: "{{ session('error') }}",
            });
        @endif
    });
</script>
@endpush