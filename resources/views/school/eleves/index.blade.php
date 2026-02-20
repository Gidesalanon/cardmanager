@extends('layouts.app_school')

@section('content')

<style>
/* Style des boutons circulaires */
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
.circle-btn:hover { transform: scale(1.1); }
.circle-edit{ background:#f59e0b; }
.circle-delete{ background:#dc2626; }

/* Correction pour alignement des boutons dans la cellule */
.actions-cell {
    display: flex;
    gap: 8px;
    justify-content: flex-start;
    align-items: center;
}
</style>

<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">Élèves</h3>
            <p class="card-subtitle">
                Gestion des élèves - Année scolaire {{ $activeYear->label ?? '' }}
            </p>
        </div>
        <a href="{{ route('school.students.create') }}" class="btn btn-primary">
            Nouvel élève
        </a>
    </div>

    {{-- Filtres par classe --}}
    <div style="padding:15px; border-bottom:1px solid #eee;">
        <a href="{{ route('school.students.index') }}"
           class="btn {{ !$selectedClasse ? 'btn-primary' : 'btn-light' }}">
            Toutes les classes
        </a>

        @foreach($classes as $classe)
            <a href="{{ route('school.students.index', ['classe_id' => $classe->id]) }}"
               class="btn {{ $selectedClasse == $classe->id ? 'btn-primary' : 'btn-light' }}">
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
                                <img src="{{ asset('storage/'.$eleve->photo) }}"
                                     width="40"
                                     height="40"
                                     style="object-fit:cover;border-radius:4px;">
                            @else
                                -
                            @endif
                        </td>

                        <td>{{ $eleve->matricule_edumaster }}</td>
                        <td>{{ $eleve->nom }}</td>
                        <td>{{ $eleve->prenom }}</td>
                        <td>{{ $eleve->sexe }}</td>
                        <td>
                            {{ $eleve->date_naissance 
                                ? \Carbon\Carbon::parse($eleve->date_naissance)->format('d/m/Y') 
                                : '-' }}
                        </td>
                        <td>{{ $eleve->telephone_tuteur }}</td>

                        <td class="actions-cell">
                            <a href="{{ route('school.students.edit', $eleve) }}"
                               class="circle-btn circle-edit"
                               title="Modifier">
                                ✏
                            </a>

                            {{-- Formulaire de suppression avec ID unique --}}
                            <form id="delete-form-{{ $eleve->id }}" 
                                  action="{{ route('school.students.destroy', $eleve) }}"
                                  method="POST" 
                                  style="display:none;">
                                @csrf
                                @method('DELETE')
                            </form>

                            {{-- Bouton déclencheur SweetAlert --}}
                            <button type="button" 
                                    onclick="confirmDelete({{ $eleve->id }}, '{{ addslashes($eleve->nom . ' ' . $eleve->prenom) }}')"
                                    class="circle-btn circle-delete"
                                    title="Supprimer">
                                🗑
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align:center;">
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
    /**
     * Confirmation de suppression avec SweetAlert2
     */
    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Supprimer l\'élève ?',
            text: "Êtes-vous sûr de vouloir supprimer " + name + " ? Cette action est irréversible.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626', // Rouge
            cancelButtonColor: '#6b7280',  // Gris
            confirmButtonText: 'Oui, supprimer !',
            cancelButtonText: 'Annuler',
            reverseButtons: true, // Place "Annuler" à gauche
            focusCancel: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Soumission du formulaire correspondant
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }

    /**
     * Gestion des notifications Flash (Succès / Erreur)
     */
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Opération réussie',
                text: "{{ session('success') }}",
                timer: 3000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: "{{ session('error') }}",
                confirmButtonColor: '#dc2626'
            });
        @endif
    });
</script>
@endpush