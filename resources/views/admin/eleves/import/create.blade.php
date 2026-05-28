@extends('layouts.app')

@section('content')
<style>
    [x-cloak] { display: none !important; }

    .loader {
        border: 3px solid rgba(201,168,76,0.2);
        border-top: 3px solid #c9a84c;
        border-radius: 50%;
        width: 18px; height: 18px;
        animation: spin 0.7s linear infinite;
    }
    @keyframes spin { 100% { transform: rotate(360deg); } }

    .modal-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.6);
        display: flex; align-items: center; justify-content: center;
        z-index: 10000; backdrop-filter: blur(4px);
    }
    .modal-content {
        background: var(--bg-primary);
        border: 1px solid rgba(201,168,76,0.25);
        padding: 35px; border-radius: 16px;
        width: 90%; max-width: 460px; text-align: center;
        box-shadow: 0 25px 50px rgba(0,0,0,0.4);
        animation: modalPop 0.3s ease-out;
    }
    @keyframes modalPop {
        from { opacity: 0; transform: scale(0.9); }
        to   { opacity: 1; transform: scale(1); }
    }
    .modal-icon {
        width: 64px; height: 64px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 20px;
    }
    .modal-icon.error   { background: rgba(220,38,38,0.1);  color: #dc2626; }
    .modal-icon.success { background: rgba(201,168,76,0.1); color: #c9a84c; }
    .modal-title { font-size: 1.15rem; font-weight: 800; color: var(--text-primary); margin-bottom: 10px; }
    .modal-msg   { color: var(--text-secondary); line-height: 1.6; font-size: 0.9rem; margin-bottom: 22px; }

    .btn-close-modal {
        width: 100%; padding: 12px;
        background: linear-gradient(135deg, #c9a84c, #f0d080);
        color: #0a0a0a; border: none; border-radius: 10px;
        font-weight: 700; font-size: 0.95rem; cursor: pointer; transition: all 0.2s;
    }
    .btn-close-modal:hover { box-shadow: 0 6px 20px rgba(201,168,76,0.4); transform: translateY(-1px); }

    .import-btn {
        display: inline-flex; align-items: center; justify-content: center;
        gap: 8px; padding: 11px 22px;
        font-weight: 600; border-radius: 8px;
        border: none; cursor: pointer; transition: all 0.2s; font-size: 0.9rem;
    }
    .btn-analyse {
        background: linear-gradient(135deg, #c9a84c, #f0d080); color: #0a0a0a;
    }
    .btn-analyse:hover { box-shadow: 0 6px 20px rgba(201,168,76,0.4); transform: translateY(-1px); }
    .btn-save { background: linear-gradient(135deg, #16a34a, #22c55e); color: #fff; }
    .btn-save:hover { box-shadow: 0 6px 20px rgba(22,163,74,0.35); transform: translateY(-1px); }
    .btn-canvas {
        background: var(--bg-surface); border: 1px solid var(--border-color);
        color: var(--text-primary); text-decoration: none;
    }
    .btn-canvas:hover { border-color: #c9a84c; color: #c9a84c; }

    .circle-btn {
        width: 34px; height: 34px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        cursor: pointer; transition: all 0.2s; border: none; color: #fff;
    }
    .circle-btn:hover { transform: scale(1.1); }
    .btn-add    { background: #c9a84c; }
    .btn-delete { background: #dc2626; }

    .photo-box {
        width: 40px; height: 40px;
        border: 1.5px solid var(--border-color);
        border-radius: 6px; overflow: hidden;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; background: var(--bg-surface); transition: border-color 0.2s;
    }
    .photo-box:hover { border-color: #c9a84c; }
    .photo-box img { width: 100%; height: 100%; object-fit: cover; }
    .photo-placeholder { font-size: 0.7rem; color: var(--text-secondary); }

    .toast {
        position: fixed; top: 20px; right: 20px;
        padding: 12px 20px; border-radius: 8px;
        font-size: 0.88rem; font-weight: 600;
        z-index: 9999; box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    .progress-wrap {
        margin-top: 10px; height: 4px;
        background: var(--bg-surface); border-radius: 2px; overflow: hidden;
    }
    .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #c9a84c, #f0d080);
        transition: width 0.3s; border-radius: 2px;
    }

    select.form-input {
        appearance: auto !important;
        -webkit-appearance: menulist !important;
        height: 38px !important; font-size: 0.9rem !important;
    }
    .rules-card { border-left: 4px solid #c9a84c; }
    .rules-card ul {
        margin: 0 0 20px 0; padding-left: 18px;
        line-height: 1.8; color: var(--text-secondary); font-size: 0.88rem;
    }
    .rules-card ul li strong { color: var(--text-primary); }
</style>

<div class="page-header">
    <h1 class="greeting">Import d'élèves</h1>
    <p class="greeting-sub">Importez des élèves depuis un fichier Excel — Admin</p>
</div>

<div x-data="adminImport()">

    {{-- TOAST --}}
    <div x-show="toast.show" x-transition class="toast"
         :style="toast.type === 'error'
             ? 'background:#dc2626; color:#fff;'
             : 'background:linear-gradient(135deg,#c9a84c,#f0d080); color:#0a0a0a;'"
         x-text="toast.message"></div>

    <div class="settings-grid">

        {{-- IMPORT CARD --}}
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">Importer un document</div>
                    <div class="card-subtitle">Formats : .xlsx, .xls, .csv</div>
                </div>
                <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">
                    <i class="fa-solid fa-arrow-left"></i> Retour
                </a>
            </div>
            <div class="form-group">
                <label class="form-label">Fichier Excel</label>
                <input type="file" class="form-input" accept=".xlsx,.xls,.csv"
                       @change="file = $event.target.files[0]">
            </div>
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
            <div x-show="loading" class="progress-wrap">
                <div class="progress-bar" :style="'width:' + progress + '%'"></div>
            </div>
        </div>

        {{-- RÈGLES --}}
        <div class="card rules-card">
            <div class="card-header">
                <div>
                    <div class="card-title">Règles importantes</div>
                    <div class="card-subtitle">Lisez avant d'importer</div>
                </div>
            </div>
            <ul>
                <li>Le fichier doit être au format <strong>Excel</strong> (.xlsx, .xls ou .csv).</li>
                <li>Chaque ligne doit au moins avoir un <strong>Nom</strong>.</li>
                <li>Les dates doivent être au format Excel valide <strong>(JJ/MM/AAAA)</strong>.</li>
                <li>Si une colonne <strong>N° Table</strong> existe, elle sera importée automatiquement.</li>
                <li>Le <strong>N° EducMaster</strong> est optionnel — laissez vide si absent.</li>
                <li>Si le numéro du tuteur est absent, le numéro de l'école sera utilisé.</li>
            </ul>
            <a href="{{ route('modele.eleve.download') }}" class="import-btn btn-canvas">
                <i class="fa-solid fa-download"></i> Télécharger le canevas
            </a>
        </div>

    </div>

    {{-- TABLE PREVIEW --}}
    <div x-show="students.length > 0" x-cloak style="margin-top: 1.5rem;">
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">Aperçu — <span x-text="students.length"></span> élève(s) détecté(s)</div>
                    <div class="card-subtitle">Vérifiez et corrigez avant d'enregistrer</div>
                </div>
            </div>

            {{-- École + Classe + Série --}}
            <div style="display:flex; gap:15px; flex-wrap:wrap; margin-bottom:20px;">
                <div style="flex:1; min-width:220px;">
                    <label class="form-label">École <span style="color:#c9a84c;">*</span></label>
                    <select x-model="ecole_id" class="form-input" style="width:100%;">
                        <option value="">-- Choisir l'école --</option>
                        @foreach($ecoles as $ecole)
                            <option value="{{ $ecole->id }}">{{ $ecole->nom_ecole }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="flex:1; min-width:220px;">
                    <label class="form-label">Classe <span style="color:#c9a84c;">*</span></label>
                    <select x-model="classe_id" @change="checkSerie()" class="form-input" style="width:100%;">
                        <option value="">-- Choisir la classe --</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" data-nom="{{ $c->nom }}">{{ $c->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div x-show="showSerie" x-cloak style="flex:1; min-width:180px;">
                    <label class="form-label">Série <span style="color:#c9a84c;">*</span></label>
                    <select x-model="serie" class="form-input" style="width:100%;">
                        <option value="">-- Choisir la série --</option>
                        @foreach(\App\Models\Serie::orderBy('nom')->get() as $serie)
                            <option value="{{ $serie->nom }}">{{ $serie->nom }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="overflow-x:auto;">
                <table style="min-width:1100px;">
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>N° EducMaster</th>
                            <th>N° Table</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Sexe</th>
                            <th>Date naissance</th>
                            <th>Lieu naissance</th>
                            <th>Tél. tuteur</th>
                            <th style="text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(s, i) in students" :key="i">
                            <tr>
                                <td>
                                    <label class="photo-box" :title="s.photo ? '' : 'Cliquer pour ajouter'">
                                        <img x-show="s.photo" :src="s.photo">
                                        <span x-show="!s.photo" class="photo-placeholder">+</span>
                                        <input type="file" hidden accept="image/*" @change="previewPhoto($event, i)">
                                    </label>
                                </td>
                                <td>
                                    <input class="form-input" x-model="s.matricule"
                                           placeholder="Optionnel" style="width:120px;">
                                </td>
                                <td>
                                    <input class="form-input" x-model="s.numero_table"
                                           placeholder="Optionnel" style="width:100px;">
                                </td>
                                <td><input class="form-input" x-model="s.nom" style="min-width:100px;"></td>
                                <td><input class="form-input" x-model="s.prenom" style="min-width:120px;"></td>
                                <td>
                                    <select class="form-input" x-model="s.sexe" style="min-width:65px;">
                                        <option value="M">M</option>
                                        <option value="F">F</option>
                                    </select>
                                </td>
                                <td><input type="date" class="form-input" x-model="s.date_naissance"></td>
                                <td><input class="form-input" x-model="s.lieu_naissance" style="min-width:100px;"></td>
                                <td><input class="form-input" x-model="s.telephone_tuteur" style="min-width:110px;"></td>
                                <td style="text-align:center;">
                                    <div style="display:flex; gap:5px; justify-content:center;">
                                        <button type="button" class="circle-btn btn-add"
                                                title="Ajouter après" @click="addAfter(i)">
                                            <i class="fa-solid fa-plus"></i>
                                        </button>
                                        <button type="button" class="circle-btn btn-delete"
                                                title="Supprimer" @click="remove(i)">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div style="border-top:1px solid var(--border-color); padding-top:20px;
                        display:flex; justify-content:space-between; align-items:center;
                        margin-top:10px; flex-wrap:wrap; gap:10px;">
                <span style="font-size:0.85rem; color:var(--text-secondary);">
                    <span x-text="students.length"></span> élève(s) à enregistrer
                </span>
                <button type="button" class="import-btn btn-save" @click="saveAll">
                    <i class="fa-solid fa-floppy-disk"></i> Enregistrer tout
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL STATUT --}}
    <template x-if="statusModal.show">
        <div class="modal-overlay" @click.self="statusModal.show = false">
            <div class="modal-content">
                <div class="modal-icon" :class="statusModal.type">
                    <template x-if="statusModal.type === 'error'">
                        <svg width="30" height="30" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                            <line x1="12" y1="9" x2="12" y2="13"/>
                            <line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                    </template>
                    <template x-if="statusModal.type === 'success'">
                        <svg width="30" height="30" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                    </template>
                </div>
                <div class="modal-title" x-text="statusModal.title"></div>
                <div class="modal-msg" x-text="statusModal.message"></div>
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
            this.toast = { show: true, message: msg, type };
            setTimeout(() => this.toast.show = false, 4000);
        },

        showStatus(title, message, type = 'error', callback = null) {
            this.statusModal = { show: true, title, message, type, callback };
        },

        checkSerie() {
            const select = document.querySelector('select[x-model="classe_id"]');
            if (!select || !select.selectedIndex) { this.showSerie = false; this.serie = ''; return; }
            const nom = select.options[select.selectedIndex].getAttribute('data-nom') ?? '';
            const avecSerie = /^(2nde|1ère|Tle|Terminale)$/i.test(nom.trim());
            this.showSerie = avecSerie;
            if (!avecSerie) this.serie = '';
        },

        async upload() {
            if (!this.file) { this.notify('Choisissez un fichier', 'error'); return; }
            this.loading = true; this.progress = 10; this.students = [];
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
                    return;
                }
                this.students = (d.students ?? []).map(s => ({
                    ...s,
                    sexe: s.sexe ? s.sexe.toString().toUpperCase().substring(0, 1) : 'M',
                    numero_table: s.numero_table ?? ''
                }));
                this.progress = 100;
                this.notify(`${this.students.length} élève(s) détecté(s)`, 'success');
            } catch(e) {
                this.showStatus('Erreur technique', 'Connexion au serveur impossible.', 'error');
            } finally {
                setTimeout(() => { this.loading = false; this.progress = 0; }, 500);
            }
        },

        addAfter(i) {
            this.students.splice(i + 1, 0, {
                photo: null, matricule: '', numero_table: '', nom: '', prenom: '',
                sexe: 'M', date_naissance: '', lieu_naissance: '', telephone_tuteur: ''
            });
        },

        remove(i) { this.students.splice(i, 1); },

        validateStudents() {
            for (let s of this.students) {
                if (!s.nom || !s.prenom || !s.date_naissance) {
                    this.notify('Nom, Prénom et Date sont obligatoires pour chaque élève', 'error');
                    return false;
                }
            }
            return true;
        },

        async saveAll() {
            if (!this.ecole_id)  { this.notify('Choisissez une école', 'error'); return; }
            if (!this.classe_id) { this.notify('Choisissez une classe', 'error'); return; }
            if (this.showSerie && !this.serie) { this.notify('Choisissez une série', 'error'); return; }
            if (!this.students.length) { this.notify('Aucun élève à enregistrer', 'error'); return; }
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
                        `${d.stats?.successCount ?? this.students.length} élève(s) ajouté(s) avec succès.`,
                        'success',
                        () => window.location.href = "{{ route('admin.students.index') }}"
                    );
                } else {
                    this.showStatus('Erreur', d.message || 'Erreur lors de la sauvegarde.', 'error');
                }
            } catch(e) {
                this.showStatus('Erreur serveur', 'Impossible de sauvegarder les données.', 'error');
            }
        },

        previewPhoto(e, i) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = ev => { this.students[i].photo = ev.target.result; };
            reader.readAsDataURL(file);
        }
    }
}
</script>

@endsection