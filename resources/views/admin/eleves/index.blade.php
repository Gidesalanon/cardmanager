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
}
.circle-edit{ background:#f59e0b; }
.circle-edit:hover{ background:#d97706; }

.circle-delete{ background:#dc2626; }
.circle-delete:hover{ background:#b91c1c; }

.toast{
    position:fixed;
    top:20px;
    right:20px;
    padding:12px 18px;
    border-radius:6px;
    font-size:14px;
    color:#fff;
    z-index:9999;
}
.toast.success{ background:#16a34a; }
.toast.error{ background:#dc2626; }
</style>

<div x-data="studentIndex()" x-init="init()" class="card">

    {{-- TOAST --}}
    <div x-show="toast.show"
         x-transition
         class="toast"
         :class="toast.type"
         x-text="toast.message">
    </div>

    <div class="card-header">
        <div>
            <h3 class="card-title">Élèves</h3>
            <p class="card-subtitle">
                Gestion des élèves - Année scolaire {{ $activeYear->label ?? '' }}
            </p>
        </div>
        <a href="{{ route('admin.students.create') }}" class="btn btn-primary">
            Nouvel élève
        </a>

    </div>

    <div style="padding:15px; border-bottom:1px solid #eee;">
        <a href="{{ route('admin.students.index') }}"
           class="btn {{ !$selectedClasse ? 'btn-primary' : 'btn-light' }}">
            Toutes les classes
        </a>

        @foreach($classes as $classe)
            <a href="{{ route('admin.students.index', ['classe_id' => $classe->id]) }}"
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

                        <td style="display:flex; gap:8px;">

                            <a href="{{ route('admin.students.edit',$eleve) }}"
                               class="circle-btn circle-edit"
                               title="Modifier">
                                ✏
                            </a>

                            <form action="{{ route('admin.students.destroy',$eleve) }}"
                                  method="POST"
                                  @submit.prevent="confirmDelete($event)">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        class="circle-btn circle-delete"
                                        title="Supprimer">
                                    🗑
                                </button>
                            </form>

                            <a href="{{ route('admin.students.export.card.pdf',$eleve) }}"
                                class="circle-btn"
                                style="background:#2563eb;"
                                title="Exporter PDF">
                                📄
                            </a>

                            <a href="{{ route('admin.students.export.card.image',$eleve) }}"
                                class="circle-btn"
                                style="background:#059669;"
                                title="Exporter Image">
                                🖼
                            </a>


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

<script>
function studentIndex(){
    return{

        toast:{show:false,message:'',type:'success'},

        notify(msg,type='success'){
            this.toast.message=msg;
            this.toast.type=type;
            this.toast.show=true;
            setTimeout(()=>this.toast.show=false,4000);
        },

        confirmDelete(e){
            if(confirm('Voulez-vous vraiment supprimer cet élève ?')){
                e.target.closest('form').submit();
            }
        },

        init(){
            @if(session('success'))
                this.notify("{{ session('success') }}",'success');
            @endif

            @if(session('error'))
                this.notify("{{ session('error') }}",'error');
            @endif
        }
    }
}
</script>

@endsection
