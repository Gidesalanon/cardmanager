@extends('layouts.ecole')

@section('content')

<style>
[x-cloak] { display: none !important; }

/* ================= LOADER ================= */
.loader {
    border:3px solid #f3f3f3;
    border-top:3px solid #2563eb;
    border-radius:50%;
    width:18px;
    height:18px;
    animation:spin 0.7s linear infinite;
}
@keyframes spin {
    0%{ transform:rotate(0deg); }
    100%{ transform:rotate(360deg); }
}

/* ================= BUTTONS ================= */
.import-btn{
    display:inline-flex;
    align-items:center;
    gap:8px;
    padding:10px 20px;
    font-weight:600;
    border-radius:6px;
    border:none;
    cursor:pointer;
}

.btn-analyse{
    background:#2563eb;
    color:#fff;
}
.btn-analyse:hover{ background:#1d4ed8; }

.btn-save{
    background:#16a34a;
    color:#fff;
}
.btn-save:hover{ background:#15803d; }

/* ✅ CIRCLE BUTTONS (REMIS PROPREMENT) */
.circle-btn{
    width:34px;
    height:34px;
    border-radius:50%;
    border:none;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    cursor:pointer;
    font-size:15px;
    font-weight:bold;
    color:#fff;
    transition:all 0.2s ease;
}

.btn-add{
    background:#2563eb;
}
.btn-add:hover{
    background:#1d4ed8;
    transform:scale(1.05);
}

.btn-delete{
    background:#dc2626;
}
.btn-delete:hover{
    background:#b91c1c;
    transform:scale(1.05);
}

.section-spacing{ margin-bottom:25px; }

/* ================= PHOTO ================= */
.photo-box{
    width:38px;
    height:38px;
    border:1px solid #d1d5db;
    border-radius:4px;
    overflow:hidden;
    display:flex;
    align-items:center;
    justify-content:center;
    cursor:pointer;
    background:#f9fafb;
}
.photo-box img{
    width:100%;
    height:100%;
    object-fit:cover;
}

/* ================= TABLE ANIMATION ================= */
.fade-in{
    animation:fadeIn 0.4s ease-in-out;
}
@keyframes fadeIn{
    from{opacity:0; transform:translateY(10px);}
    to{opacity:1; transform:translateY(0);}
}

/* ================= TOAST ================= */
.toast{
    position:fixed;
    top:20px;
    right:20px;
    background:#111827;
    color:#fff;
    padding:12px 18px;
    border-radius:6px;
    font-size:14px;
    z-index:9999;
    box-shadow:0 4px 10px rgba(0,0,0,0.15);
}
.toast.error{ background:#dc2626; }
.toast.success{ background:#16a34a; }

.progress-bar{
    height:4px;
    background:#16a34a;
    transition:width 0.3s;
}
</style>

<div x-data="studentImport()" class="settings-grid">

<!-- TOAST -->
<div x-show="toast.show"
     x-transition
     class="toast"
     :class="toast.type"
     x-text="toast.message">
</div>

<!-- IMPORT CARD -->
<div class="card section-spacing">
    <h2 style="margin-bottom:15px;">Importer un document</h2>

    <input type="file"
           class="form-input"
           style="margin-bottom:15px;"
           @change="file = $event.target.files[0]">

    <button type="button"
            class="import-btn btn-analyse"
            @click="upload"
            :disabled="loading">

        <template x-if="!loading">
            <span>📊 Analyser le document</span>
        </template>

        <template x-if="loading">
            <span style="display:flex; align-items:center; gap:8px;">
                <div class="loader"></div>
                Analyse...
            </span>
        </template>

    </button>

    <div x-show="loading" style="margin-top:10px; width:100%; background:#e5e7eb;">
        <div class="progress-bar" :style="'width:'+progress+'%'"></div>
    </div>
</div>

<!-- REGLES IMPORTANTES -->
<div class="card section-spacing" style="background:#f9fafb; border-left:5px solid #2563eb;">
    <h3 style="margin-bottom:10px;">📌 Règles importantes</h3>
    <ul style="margin:0; padding-left:18px; line-height:1.7;">
        <li>Le fichier doit être au format Excel (.xlsx, .xls ou .csv).</li>
        <li>L’ordre des colonnes doit être respecté.</li>
        <li>La colonne Matricule ne doit pas contenir de doublons.</li>
        <li>Les dates doivent être au format Excel valide.</li>
        <li>Chaque élève devra avoir une photo avant l’enregistrement.</li>
    </ul>
</div>

<!-- PREVIEW -->
<div x-show="students.length > 0"
     x-cloak
     class="card mt-6 fade-in"
     style="grid-column:1/-1">

    <div class="section-spacing">
        <div style="display:flex; gap:15px; flex-wrap:wrap;">

            <div style="flex:1; min-width:220px;">
                <select x-model="classe_id"
                        @change="checkSerie()"
                        class="form-input">
                    <option value="">Choisir la classe</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}"
                                data-nom="{{ $c->nom }}">
                            {{ $c->nom }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div x-show="showSerie"
                 x-cloak
                 style="flex:1; min-width:180px;">
                <select x-model="serie"
                        class="form-input">
                    <option value="">Choisir la série</option>
                    @foreach(\App\Models\Serie::orderBy('nom')->get() as $serie)
                        <option value="{{ $serie->nom }}">
                            {{ $serie->nom }}
                        </option>
                    @endforeach
                </select>
            </div>

        </div>
    </div>

    <div style="overflow-x:auto;">
        <table style="min-width:1000px;">
            <thead>
            <tr>
                <th>Photo</th>
                <th>Matricule</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Sexe</th>
                <th>Date naissance</th>
                <th>Lieu naissance</th>
                <th>Téléphone parent</th>
                <th>Actions</th>
            </tr>
            </thead>

            <tbody>
            <template x-for="(s,i) in students" :key="i">
                <tr>
                    <td>
                        <label class="photo-box">
                            <img x-show="s.photo" :src="s.photo">
                            <input type="file" hidden accept="image/*"
                                   @change="previewPhoto($event,i)">
                        </label>
                    </td>

                    <td><input class="form-input" x-model="s.matricule"></td>
                    <td><input class="form-input" x-model="s.nom"></td>
                    <td><input class="form-input" x-model="s.prenom"></td>

                    <td>
                        <select class="form-input" x-model="s.sexe">
                            <option value="">Choisir</option>
                            <option value="M">M</option>
                            <option value="F">F</option>
                        </select>
                    </td>

                    <td>
                        <input type="date"
                               class="form-input"
                               x-model="s.date_naissance">
                    </td>

                    <td>
                        <input class="form-input"
                               x-model="s.lieu_naissance">
                    </td>

                    <td>
                        <input class="form-input"
                               x-model="s.telephone_tuteur">
                    </td>

                    <td style="white-space:nowrap; text-align:center;">
                        <button type="button"
                                class="circle-btn btn-add"
                                @click="addAfter(i)">
                            +
                        </button>

                        <button type="button"
                                class="circle-btn btn-delete"
                                @click="remove(i)">
                            🗑
                        </button>
                    </td>
                </tr>
            </template>
            </tbody>
        </table>
    </div>

    <div style="border-top:1px solid #e5e7eb; padding-top:25px; text-align:right;">
        <button type="button"
                class="import-btn btn-save"
                @click="saveAll">
            💾 Enregistrer tous les élèves
        </button>
    </div>

</div>
</div>

<script>
function studentImport(){
    return{
        file:null,
        students:[],
        classe_id:'',
        serie:'',
        showSerie:false,
        loading:false,
        progress:0,
        toast:{show:false,message:'',type:'success'},

        notify(msg,type='success'){
            this.toast.message=msg;
            this.toast.type=type;
            this.toast.show=true;
            setTimeout(()=>this.toast.show=false,4000);
        },

        checkSerie(){
            let select=document.querySelector('select[x-model="classe_id"]');
            let selected=select.options[select.selectedIndex];
            let nom=selected.getAttribute('data-nom');
            if(!nom){ this.showSerie=false; return; }
            let lower=nom.toLowerCase();
            if(lower.includes('2nde')||lower.includes('seconde')||
               lower.includes('1ère')||lower.includes('première')||
               lower.includes('tle')||lower.includes('terminale')){
                this.showSerie=true;
            }else{
                this.showSerie=false;
                this.serie='';
            }
        },

        async upload(){
            if(!this.file){ this.notify('Choisir un fichier','error'); return; }

            this.loading=true;
            this.progress=10

            const fd=new FormData();
            fd.append('document',this.file);

            console.log('📤 Envoi du fichier:', this.file.name);

            const r=await fetch("{{ route('school.students.import.preview') }}",{
                method:'POST',
                headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'},
                body:fd
            });

            this.progress=70

            console.log('📥 Réponse brute:', r);
            console.log('📥 Status:', r.status);

            const d=await r.json();
            console.log('📥 Données JSON:', d);
            console.log('📥 Nombre d\'étudiants reçus:', d.students?.length || 0);
            
            this.students=d.students??[];
            console.log('📥 Students array:', this.students);

            this.progress=100;
            setTimeout(()=>{ this.loading=false; this.progress=0; },500);

            this.notify('Analyse terminée','success');
        },

        addAfter(i){
            this.students.splice(i+1,0,{
                photo:null,
                matricule:'',
                nom:'',
                prenom:'',
                sexe:'',
                date_naissance:'',
                lieu_naissance:'',
                telephone_tuteur:''
            });
        },

        remove(i){
            this.students.splice(i,1);
        },

        validateStudents(){
            if(this.students.length === 0){
                this.notify('Aucun élève à enregistrer','error');
                return false;
            }

            for(let i=0;i<this.students.length;i++){
                let s=this.students[i];
                if(s.photo === undefined || s.matricule === undefined || s.nom === undefined || s.prenom === undefined ||
                   s.sexe === undefined || s.date_naissance === undefined || s.lieu_naissance === undefined){
                    this.notify('Tous les champs sont obligatoires (ligne '+(i+1)+')','error');
                    return false;
                }
            }

            // Vérifie doublons de matricule
            let matricules = this.students.map(s => s.matricule ? s.matricule.trim() : '');
            let duplicates = matricules.filter((item, index) => matricules.indexOf(item) !== index && item !== '');
            if(duplicates.length){
                this.notify('Doublon de matricule détecté','error');
                return false;
            }

            return true;
        },

        async saveAll(){
            if(!this.classe_id){ this.notify('Choisir une classe','error'); return; }
            if(this.showSerie && !this.serie){ this.notify('Choisir la série','error'); return; }
            if(!this.validateStudents()) return;

            try {
                const response = await fetch("{{ route('school.students.import.storeAll') }}",{
                    method:'POST',
                    headers:{
                        'X-CSRF-TOKEN':'{{ csrf_token() }}',
                        'Content-Type':'application/json'
                    },
                    body:JSON.stringify({
                        classe_id:this.classe_id,
                        serie:this.serie,
                        students:this.students
                    })
                });

                const data = await response.json();

                if(response.ok && data.success){
                    this.notify('Enregistrement réussi','success');
                    setTimeout(()=>{
                        window.location.href="{{ route('school.students.index') }}";
                    },1000);
                } else {
                    this.notify(data.message || 'Erreur lors de l\'enregistrement','error');
                }
            } catch(e){
                this.notify('Erreur serveur. Vérifiez les données.','error');
            }
        },

        previewPhoto(e,i){
            const file=e.target.files[0];
            if(!file) return;
            const reader=new FileReader();
            reader.onload=(ev)=>{
                this.students[i].photo=ev.target.result;
            };
            reader.readAsDataURL(file);
        }
    }
}
</script>

@endsection
