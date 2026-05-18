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
.circle-edit   { background: #f59e0b; }
.circle-delete { background: #dc2626; }
</style>

<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">Écoles</h3>
            <p class="card-subtitle">Gestion de toutes les écoles enregistrées</p>
        </div>
        <a href="{{ route('admin.ecoles.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Nouvelle école
        </a>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nom de l'école</th>
                    <th>N° Autorisation</th>
                    <th>Téléphone</th>
                    <th>Adresse</th>
                    <th>Directeur</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ecoles as $ecole)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $ecole->nom_ecole }}</td>
                        <td>{{ $ecole->numero_autorisation }}</td>
                        <td>{{ $ecole->telephone ?? '-' }}</td>
                        <td>{{ $ecole->adresse_ecole ?? '-' }}</td>
                        <td>
                            @if($ecole->directeur)
                                {{ $ecole->directeur->prenom }}
                                {{ strtoupper($ecole->directeur->nom) }}
                            @else
                                <span style="color:#999;">—</span>
                            @endif
                        </td>
                        <td style="display:flex; gap:8px;">
                            <a href="{{ route('admin.ecoles.edit', $ecole) }}"
                               class="circle-btn circle-edit" title="Modifier">
                                <i class="fa-solid fa-pen"></i>
                            </a>

                            <form id="delete-ecole-{{ $ecole->id }}"
                                  action="{{ route('admin.ecoles.destroy', $ecole) }}"
                                  method="POST" style="display:none;">
                                @csrf
                                @method('DELETE')
                            </form>

                            <button type="button"
                                    onclick="confirmDeleteEcole({{ $ecole->id }}, '{{ addslashes($ecole->nom_ecole) }}')"
                                    class="circle-btn circle-delete" title="Supprimer">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center;">Aucune école enregistrée.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script>
function confirmDeleteEcole(id, name) {
    Swal.fire({
        title: 'Êtes-vous sûr ?',
        text: "Supprimer l'école " + name + " supprimera aussi son directeur et tous ses élèves !",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Oui, supprimer !',
        cancelButtonText: 'Annuler',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-ecole-' + id).submit();
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
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
            text: "{{ session('error') }}"
        });
    @endif
});
</script>
@endpush