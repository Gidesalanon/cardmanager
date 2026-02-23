@extends('layouts.ecole')

@section('content')
<style>
    [x-cloak] { display: none !important; }

    /* ================= LOADER ================= */
    .loader {
        border: 3px solid #f3f3f3;
        border-top: 3px solid #2563eb;
        border-radius: 50%;
        width: 18px;
        height: 18px;
        animation: spin 0.7s linear infinite;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* ================= MODAL ERROR ================= */
    .modal-overlay {
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex; align-items: center; justify-content: center;
        z-index: 10000;
        backdrop-filter: blur(4px);
    }
    .modal-content {
        background: white;
        padding: 30px;
        border-radius: 12px;
        width: 90%;
        max-width: 450px;
        text-align: center;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        animation: modalPop 0.3s ease-out;
    }
    @keyframes modalPop {
        from { opacity: 0; transform: scale(0.9); }
        to { opacity: 1; transform: scale(1); }
    }
    .modal-icon {
        width: 60px; height: 60px;
        background: #fee2e2;
        color: #dc2626;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 30px;
        margin: 0 auto 20px;
    }
    .btn-close-modal {
        margin-top: 20px;
        background: #111827;
        color: white;
        padding: 10px 25px;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        font-weight: 600;
        width: 100%;
    }

    /* ================= BUTTONS ================= */
    .import-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        font-weight: 600;
        border-radius: 6px;
        border: none;
        cursor: pointer;
    }
    .btn-analyse { background: #2563eb; color: #fff; }
    .btn-analyse:hover { background: #1d4ed8; }
    .btn-save { background: #16a34a; color: #fff; }
    .btn-save:hover { background: #15803d; }

    .circle-btn {
        width: 34px; height: 34px;
        border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        cursor: pointer; font-size: 15px; font-weight: bold; color: #fff;
        transition: all 0.2s ease; border:none;
    }
    .btn-add { background: #2563eb; }
    .btn-add:hover { background: #1d4ed8; transform: scale(1.05); }
    .btn-delete { background: #dc2626; }
    .btn-delete:hover { background: #b91c1c; transform: scale(1.05); }

    .section-spacing { margin-bottom: 25px; }

    /* ================= PHOTO & TABLE ================= */
    .photo-box {
        width: 38px; height: 38px;
        border: 1px solid #d1d5db; border-radius: 4px;
        overflow: hidden; display: flex; align-items: center; justify-content: center;
        cursor: pointer; background: #f9fafb;
    }
    .photo-box img { width: 100%; height: 100%; object-fit: cover; }

    .fade-in { animation: fadeIn 0.4s ease-in-out; }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .toast {
        position: fixed; top: 20px; right: 20px;
        background: #111827; color: #fff; padding: 12px 18px;
        border-radius: 6px; font-size: 14px; z-index: 9999;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    .toast.error { background: #dc2626; }
    .toast.success { background: #16a34a; }

    .progress-bar { height: 4px; background: #16a34a; transition: width 0.3s; }
</style>

<div x-data="studentImport()" class="settings-grid">

    <!-- TOAST -->
    <div x-show="toast.show" x-transition class="toast" :class="toast.type" x-text="toast.message"></div>

    <!-- IMPORT CARD -->
    <div class="card section-spacing">
        <h2 style="margin-bottom:15px;">Importer un document</h2>

        <input type="file" class="form-input" style="margin-bottom:15px;" @change="file = $event.target.files[0]">

        <button type="button" class="import-btn btn-analyse" @click="upload" :disabled="loading">
            <template x-if="!loading">
                <span>📊 Analyser le document</span>
            </template>
            <template x-if="loading">
                <span style="display:flex; align-items:center; gap:8px;">
                    <div class="loader"></div> Analyse...
                </span>
            </template>
        </button>

        <div x-show="loading" style="margin-top:10px; width:100%; background:#e5e7eb;">
            <div class="progress-bar" :style="'width:' + progress + '%'"></div>
        </div>
    </div>

    <!-- REGLES -->
    <div class="card section-spacing" style="background:#f9fafb; border-left:5px solid #2563eb;">
        <h3 style="margin-bottom:10px;">📌 Règles importantes</h3>
        <ul style="margin:0; padding-left:18px; line-height:1.7;">
            <li>Le fichier doit être au format Excel (.xlsx, .xls ou .csv).</li>
            <li>Chaque ligne doit au moins avoir un <strong>Nom</strong>.</li>
            <li>Les dates doivent être au format Excel valide.</li>
            <li>Chaque élève devra avoir une photo avant l’enregistrement.</li>
        </ul>
    </div>
    <div style="text-align: center;">
        <p style="margin-bottom: 8px; font-size: 13px; color: #6b7280;">Besoin d'aide pour le format ?</p>
        <a href="{{ route('school.eleves.import.canvas') }}" class="import-btn" style="background: #ffffff; border: 1px solid #d1d5db; color: #374151; text-decoration: none;">
            📥 Télécharger le canevas (.xlsx)
        </a>

    <!-- PREVIEW TABLE -->
    <div x-show="students.length > 0" x-cloak class="card mt-6 fade-in" style="grid-column:1/-1">
        <div class="section-spacing">
            <div style="display:flex; gap:15px; flex-wrap:wrap;">
                <div style="flex:1; min-width:220px;">
                    <select x-model="classe_id" @change="checkSerie()" class="form-input">
                        <option value="">Choisir la classe</option>
                        @foreach ($classes as $c)
                            <option value="{{ $c->id }}" data-nom="{{ $c->nom }}">{{ $c->nom }}</option>
                        @endforeach
                    </select>
                </div>

                <div x-show="showSerie" x-cloak style="flex:1; min-width:180px;">
                    <select x-model="serie" class="form-input">
                        <option value="">Choisir la série</option>
                        @foreach (\App\Models\Serie::orderBy('nom')->get() as $serie)
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
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(s,i) in students" :key="i">
                        <tr>
                            <td>
                                <label class="photo-box">
                                    <img x-show="s.photo" :src="s.photo">
                                    <input type="file" hidden accept="image/*" @change="previewPhoto($event,i)">
                                </label>
                            </td>
                            <td><input class="form-input" x-model="s.matricule"></td>
                            <td><input class="form-input" x-model="s.nom"></td>
                            <td><input class="form-input" x-model="s.prenom"></td>
                            <td>
                                <select class="form-input" x-model="s.sexe">
                                    <option value="M">M</option>
                                    <option value="F">F</option>
                                </select>
                            </td>
                            <td><input type="date" class="form-input" x-model="s.date_naissance"></td>
                            <td><input class="form-input" x-model="s.lieu_naissance"></td>
                            <td><input class="form-input" x-model="s.telephone_tuteur"></td>
                            <td style="white-space:nowrap;">
                                <button type="button" class="circle-btn btn-add" @click="addAfter(i)">+</button>
                                <button type="button" class="circle-btn btn-delete" @click="remove(i)">🗑</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div style="border-top:1px solid #e5e7eb; padding-top:25px; text-align:right;">
            <button type="button" class="import-btn btn-save" @click="saveAll">💾 Enregistrer tout</button>
        </div>
    </div>

    <!-- MODAL D'ERREUR CENTRE -->
    <template x-if="errorModal.show">
        <div class="modal-overlay" @click.self="errorModal.show = false">
            <div class="modal-content">
                <div class="modal-icon">⚠️</div>
                <h3 style="margin-bottom:10px; font-size:1.2rem; color:#111827;" x-text="errorModal.title"></h3>
                <p style="color:#4b5563; line-height:1.5;" x-text="errorModal.message"></p>
                <button class="btn-close-modal" @click="errorModal.show = false">J'ai compris</button>
            </div>
        </div>
    </template>

</div>

<script>
    function studentImport() {
        return {
            file: null,
            students: [],
            classe_id: '',
            serie: '',
            showSerie: false,
            loading: false,
            progress: 0,
            toast: { show: false, message: '', type: 'success' },
            errorModal: { show: false, title: '', message: '' },

            notify(msg, type = 'success') {
                this.toast.message = msg;
                this.toast.type = type;
                this.toast.show = true;
                setTimeout(() => this.toast.show = false, 4000);
            },

            showError(title, message) {
                this.errorModal.title = title;
                this.errorModal.message = message;
                this.errorModal.show = true;
            },

            checkSerie() {
                let select = document.querySelector('select[x-model="classe_id"]');
                if(!select.selectedIndex) return;
                let nom = select.options[select.selectedIndex].getAttribute('data-nom').toLowerCase();
                this.showSerie = (nom.includes('2nde') || nom.includes('seconde') || nom.includes('1ère') || nom.includes('tle'));
                if(!this.showSerie) this.serie = '';
            },

            async upload() {
                if (!this.file) { this.notify('Choisir un fichier', 'error'); return; }
                this.loading = true; this.progress = 10; this.students = [];
                const fd = new FormData(); fd.append('document', this.file);

                try {
                    const r = await fetch("{{ route('school.students.import.preview') }}", {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: fd
                    });
                    const d = await r.json();
                    if (!r.ok) {
                        this.showError(d.error || 'Erreur', d.details);
                        this.loading = false; this.progress = 0; return;
                    }
                    this.students = d.students ?? [];
                    this.progress = 100;
                    this.notify('Analyse terminée', 'success');
                } catch (e) {
                    this.showError('Erreur technique', 'Connexion au serveur impossible.');
                } finally {
                    setTimeout(() => { this.loading = false; this.progress = 0; }, 500);
                }
            },

            addAfter(i) {
                this.students.splice(i + 1, 0, { photo: null, matricule: '', nom: '', prenom: '', sexe: 'M', date_naissance: '', lieu_naissance: '', telephone_tuteur: '' });
            },

            remove(i) { this.students.splice(i, 1); },

            validateStudents() {
                if (!this.students.length) return false;
                for (let s of this.students) {
                    if (!s.nom || !s.prenom || !s.date_naissance) {
                        this.notify('Nom, Prénom et Date sont obligatoires', 'error');
                        return false;
                    }
                }
                return true;
            },

            async saveAll() {
                if (!this.classe_id) { this.notify('Choisir une classe', 'error'); return; }
                if (!this.validateStudents()) return;

                try {
                    const r = await fetch("{{ route('school.students.import.storeAll') }}", {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                        body: JSON.stringify({ classe_id: this.classe_id, serie: this.serie, students: this.students })
                    });
                    const d = await r.json();
                    if (r.ok && d.success) {
                        this.notify('Enregistrement réussi', 'success');
                        setTimeout(() => window.location.href = "{{ route('school.students.index') }}", 1000);
                    } else {
                        this.notify(d.message || 'Erreur lors de la sauvegarde', 'error');
                    }
                } catch (e) { this.notify('Erreur serveur', 'error'); }
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