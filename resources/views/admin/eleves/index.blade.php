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

    {{-- Filtres classes --}}
    <div style="padding:15px; border-bottom:1px solid #eee;">
        <a href="{{ route('admin.students.index') }}" class="btn {{ !$selectedClasse ? 'btn-primary' : 'btn-light' }}">Toutes les classes</a>
        @foreach($classes as $classe)
            <a href="{{ route('admin.students.index', ['classe_id' => $classe->id]) }}" class="btn {{ $selectedClasse == $classe->id ? 'btn-primary' : 'btn-light' }}">{{ $classe->nom }}</a>
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