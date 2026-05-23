@extends('layouts.app')

@section('content')
<style>
    [x-cloak] { display: none !important; }

    .loader {
        border: 3px solid #f3f3f3;
        border-top: 3px solid #2563eb;
        border-radius: 50%;
        width: 18px; height: 18px;
        animation: spin 0.7s linear infinite;
    }
    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

    .modal-overlay {
        position: fixed; top: 0; left: 0;
        width: 100%; height: 100%;
        background: rgba(0,0,0,0.5);
        display: flex; align-items: center; justify-content: center;
        z-index: 10000; backdrop-filter: blur(4px);
    }
    .modal-content {
        background: white; padding: 30px; border-radius: 12px;
        width: 90%; max-width: 450px; text-align: center;
        box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
        animation: modalPop 0.3s ease-out;
    }
    @keyframes modalPop { from { opacity:0; transform:scale(0.9); } to { opacity:1; transform:scale(1); } }

    .modal-icon {
        width: 60px; height: 60px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 20px;
    }
    .modal-icon.error   { background: #fee2e2; color: #dc2626; }
    .modal-icon.success { background: #dcfce7; color: #16a34a; }

    .btn-close-modal {
        margin-top: 10px; background: #111827; color: white;
        padding: 12px 25px; border-radius: 6px; border: none;
        cursor: pointer; font-weight: 600; width: 100%;
    }
    .btn-close-modal:hover { background: #1f2937; }

    .import-btn {
        display: inline-flex; align-items: center; justify-content: center;
        gap: 8px; padding: 10px 20px; font-weight: 600;
        border-radius: 6px; border: none; cursor: pointer; transition: all 0.2s;
    }
    .btn-analyse { background: #2563eb; color: #fff; }
    .btn-analyse:hover { background: #1d4ed8; }
    .btn-save { background: #16a34a; color: #fff; }
    .btn-save:hover { background: #15803d; }

    .circle-btn {
        width: 34px; height: 34px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        cursor: pointer; transition: all 0.2s; border: none; color: #fff;
    }
    .btn-add    { background: #2563eb; }
    .btn-delete { background: #dc2626; }

    .photo-box {
        width: 38px; height: 38px;
        border: 1px solid #d1d5db; border-radius: 4px;
        overflow: hidden; display: flex; align-items: center;
        justify-content: center; cursor: pointer; background: #f9fafb;
    }
    .photo-box img { width: 100%; height: 100%; object-fit: cover; }

    .toast {
        position: fixed; top: 20px; right: 20px;
        background: #111827; color: #fff;
        padding: 12px 18px; border-radius: 6px;
        font-size: 14px; z-index: 9999;
    }

    .progress-bar { height: 4px; background: #16a34a; transition: width 0.3s; }

    select.form-input {
        appearance: auto !important;
        -webkit-appearance: menulist !important;
        padding: 0 10px !important;
        height: 38px !important;
        background-color: white !important;
        color: black !important;
        font-size: 14px !important;
    }
</style>

<div x-data="adminImport()" class="settings-grid">

    <!-- TOAST -->
    <div x-show="toast.show" x-transition class="toast"
         :style="toast.type === 'error' ? 'background:#dc2626' : 'background:#16a34a'"
         x-text="toast.message"></div>

    <!-- IMPORT CARD -->
    <div class="card" style="margin-bottom:25px;">
        <div class="card-header">
            <div>
                <h3 class="card-title">Importer des élèves</h3>
                <p class="card-subtitle">Import Excel — Admin</p>
            </div>
            <a href="{{ route('admin.students.index') }}" class="btn btn-light">
                <i class="fa-solid fa-arrow-left"></i> Retour
            </a>
        </div>

        <div style="padding:20px;">
            <input type="file" class="form-input" style="margin-bottom:15px;"
                   @change="file = $event.target.files[0]">

            <button type="button" class="import-btn btn-analyse" @click="upload" :disabled="loading">
                <template x-if="!loading">
                    <span style="display:flex;align-items:center;gap:8px;">
                        <i class="fa-solid fa-magnifying-glass"></i> Analyser le document
                    </span>
                </template>
                <template x-if="loading">
                    <span style="display:flex;align-items:center;gap:8px;">
                        <div class="loader"></div> Analyse en cours...
                    </span>
                </template>
            </button>

            <div x-show="loading" style="margin-top:10px; width:100%; background:#e5e7eb;">
                <div class="progress-bar" :style="'width:' + progress + '%'"></div>
            </div>
        </div>
    </div>

    <!-- REGLES -->
    <div class="card" style="margin-bottom:25px; background:#f9fafb; border-left:5px solid #2563eb;">
        <div style="padding:20px;">
            <h3 style="margin-bottom:15px;">Règles importantes</h3>
            <ul style="margin:0 0 20px 0; padding-left:18px; line-height:1.7; color:#4b5563;">
                <li>Le fichier doit être au format Excel (.xlsx, .xls ou .csv).</li>
                <li>Chaque ligne doit au moins avoir un <strong>Nom</strong>.</li>
                <li>Les dates doivent être au format Excel valide (JJ/MM/AAAA).</li>
                <li>Chaque élève devra avoir une photo avant l'enregistrement.</li>
            </ul>
            <a href="{{ route('modele.eleve.download') }}" class="import-btn"
               style="background:#fff; border:1px solid #d1d5db; color:#374151; text-decoration:none;">
                <i class="fa-solid fa-download"></i> Télécharger le canevas
            </a>
        </div>
    </div>

    <!-- PREVIEW TABLE -->
    <div x-show="students.length > 0" x-cloak class="card" style="grid-column:1/-1;">
        <div style="padding:20px; border-bottom:1px solid #e5e7eb;">

            <div style="display:flex; gap:15px; flex-wrap:wrap; align-items:flex-end;">

                {{-- Sélection école --}}
                <div style="flex:1; min-width:220px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600;">École *</label>
                    <select x-model="ecole_id" class="form-input" style="width:100%;">
                        <option value="">-- Choisir l'école --</option>
                        @foreach($ecoles as $ecole)
                            <option value="{{ $ecole->id }}">{{ $ecole->nom_ecole }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Sélection classe --}}
                <div style="flex:1; min-width:220px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600;">Classe *</label>
                    <select x-model="classe_id" @change="checkSerie()" class="form-input" style="width:100%;">
                        <option value="">-- Choisir la classe --</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" data-nom="{{ $c->nom }}">{{ $c->nom }}</option>
                        @endforeach
                    </select>
                </div>

                {{--
                    Sélection série — visible UNIQUEMENT pour 2nde, 1ère, Tle.
                    La 3e, 4e, 5e, 6e ont des séries en base (A/B/C…) pour
                    distinguer les classes d'un même établissement, mais la
                    série ne s'affiche PAS sur leur carte et n'est pas
                    sélectionnable ici.
                --}}
                <div x-show="showSerie" x-cloak style="flex:1; min-width:180px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600;">Série *</label>
                    <select x-model="serie" class="form-input" style="width:100%;">
                        <option value="">-- Choisir la série --</option>
                        @foreach(\App\Models\Serie::orderBy('nom')->get() as $serie)
                            <option value="{{ $serie->nom }}">{{ $serie->nom }}</option>
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
                        <th style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(s,i) in students" :key="i">
                        <tr>
                            <td>
                                <label class="photo-box">
                                    <img x-show="s.photo" :src="s.photo">
                                    <input type="file" hidden accept="image/*" @change="previewPhoto($event, i)">
                                </label>
                            </td>
                            <td><input class="form-input" x-model="s.matricule"></td>
                            <td><input class="form-input" x-model="s.nom"></td>
                            <td><input class="form-input" x-model="s.prenom"></td>
                            <td>
                                <select class="form-input" x-model="s.sexe" style="min-width:60px;">
                                    <option value="M">M</option>
                                    <option value="F">F</option>
                                </select>
                            </td>
                            <td><input type="date" class="form-input" x-model="s.date_naissance"></td>
                            <td><input class="form-input" x-model="s.lieu_naissance"></td>
                            <td><input class="form-input" x-model="s.telephone_tuteur"></td>
                            <td style="text-align:center;">
                                <div style="display:flex; gap:5px; justify-content:center;">
                                    <button type="button" class="circle-btn btn-add" @click="addAfter(i)" title="Ajouter après">
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                    <button type="button" class="circle-btn btn-delete" @click="remove(i)" title="Supprimer">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div style="padding:20px; border-top:1px solid #e5e7eb; text-align:right;">
            <button type="button" class="import-btn btn-save" @click="saveAll">
                <i class="fa-solid fa-floppy-disk"></i> Enregistrer tout
            </button>
        </div>
    </div>

    <!-- MODAL STATUT -->
    <template x-if="statusModal.show">
        <div class="modal-overlay" @click.self="statusModal.show = false">
            <div class="modal-content">
                <div class="modal-icon" :class="statusModal.type">
                    <template x-if="statusModal.type === 'error'">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                            <line x1="12" y1="9" x2="12" y2="13"></line>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                    </template>
                    <template x-if="statusModal.type === 'success'">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </template>
                </div>
                <h3 style="margin-bottom:10px; font-size:1.2rem; color:#111827;" x-text="statusModal.title"></h3>
                <p style="color:#4b5563; line-height:1.5; margin-bottom:20px;" x-text="statusModal.message"></p>
                <button class="btn-close-modal"
                        @click="statusModal.callback ? statusModal.callback() : statusModal.show = false"
                        x-text="statusModal.type === 'success' ? 'Continuer' : 'J\'ai compris'">
                </button>
            </div>
        </div>
    </template>

</div>

<script>
function adminImport() {
    return {
        file: null,
        students: [],
        ecole_id: '',
        classe_id: '',
        serie: '',
        showSerie: false,
        loading: false,
        progress: 0,
        toast: { show: false, message: '', type: 'success' },
        statusModal: { show: false, title: '', message: '', type: 'error', callback: null },

        notify(msg, type = 'success') {
            this.toast.message = msg;
            this.toast.type    = type;
            this.toast.show    = true;
            setTimeout(() => this.toast.show = false, 4000);
        },

        showStatus(title, message, type = 'error', callback = null) {
            this.statusModal.title    = title;
            this.statusModal.message  = message;
            this.statusModal.type     = type;
            this.statusModal.callback = callback;
            this.statusModal.show     = true;
        },

        checkSerie() {
            let select = document.querySelector('select[x-model="classe_id"]');
            if (!select || !select.selectedIndex) { this.showSerie = false; this.serie = ''; return; }
            let nom = select.options[select.selectedIndex].getAttribute('data-nom') ?? '';

            /*
             * La série est obligatoire UNIQUEMENT pour 2nde, 1ère et Tle.
             * Les classes 6e, 5e, 4e, 3e ont aussi des série_id en base
             * (pour distinguer les classes A/B/C d'un établissement),
             * mais ce choix de série n'est pas exposé à l'utilisateur ici :
             * il est géré lors de l'affectation à la classe côté admin.
             */
            const avecSerie = /^(2nde|1ère|Tle|Terminale)$/i.test(nom.trim());
            this.showSerie = avecSerie;
            if (!avecSerie) this.serie = '';
        },

        async upload() {
            if (!this.file) { this.notify('Choisir un fichier', 'error'); return; }
            this.loading  = true;
            this.progress = 10;
            this.students = [];

            const fd = new FormData();
            fd.append('document', this.file);

            try {
                const r = await fetch("{{ route('admin.students.import.preview') }}", {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: fd
                });
                const d = await r.json();

                if (!r.ok) {
                    this.showStatus(d.error || 'Erreur', d.details || '', 'error');
                    this.loading = false; this.progress = 0;
                    return;
                }

                this.students = (d.students ?? []).map(s => ({
                    ...s,
                    sexe: s.sexe ? s.sexe.toString().toUpperCase().substring(0, 1) : 'M'
                }));

                this.progress = 100;
                this.notify('Analyse terminée — ' + this.students.length + ' élève(s) détecté(s)', 'success');

            } catch (e) {
                this.showStatus('Erreur technique', 'Connexion au serveur impossible.', 'error');
            } finally {
                setTimeout(() => { this.loading = false; this.progress = 0; }, 500);
            }
        },

        addAfter(i) {
            this.students.splice(i + 1, 0, {
                photo: null, matricule: '', nom: '', prenom: '',
                sexe: 'M', date_naissance: '', lieu_naissance: '', telephone_tuteur: ''
            });
        },

        remove(i) { this.students.splice(i, 1); },

        validateStudents() {
            if (!this.students.length) return false;
            for (let s of this.students) {
                if (!s.nom || !s.prenom || !s.date_naissance) {
                    this.notify('Nom, Prénom et Date sont obligatoires pour chaque élève', 'error');
                    return false;
                }
            }
            return true;
        },

        async saveAll() {
            if (!this.ecole_id)  { this.notify('Choisir une école', 'error'); return; }
            if (!this.classe_id) { this.notify('Choisir une classe', 'error'); return; }

            // Série obligatoire uniquement pour 2nde, 1ère, Tle
            if (this.showSerie && !this.serie) {
                this.notify('Choisir une série pour cette classe', 'error');
                return;
            }

            if (!this.validateStudents()) return;

            try {
                const r = await fetch("{{ route('admin.students.import.storeAll') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        ecole_id:  this.ecole_id,
                        classe_id: this.classe_id,
                        serie:     this.serie,
                        students:  this.students
                    })
                });
                const d = await r.json();

                if (r.ok && d.success) {
                    this.showStatus(
                        'Enregistrement réussi !',
                        'Les élèves ont été ajoutés avec succès. Vous allez être redirigé vers la liste.',
                        'success',
                        () => window.location.href = "{{ route('admin.students.index') }}"
                    );
                } else {
                    this.showStatus('Erreur', d.message || 'Erreur lors de la sauvegarde.', 'error');
                }
            } catch (e) {
                this.showStatus('Erreur serveur', 'Impossible de sauvegarder les données.', 'error');
            }
        },

        previewPhoto(e, i) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (ev) => { this.students[i].photo = ev.target.result; };
            reader.readAsDataURL(file);
        }
    }
}
</script>

@endsection
