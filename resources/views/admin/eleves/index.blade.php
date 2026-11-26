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
.circle-edit   { background: #c9a84c; }
.circle-delete { background: #dc2626; }
.circle-pdf    { background: #2563eb; }

.filter-bar {
    padding: 18px 20px;
    border-bottom: 1px solid var(--border-color);
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 1fr auto;
    gap: 14px;
    align-items: end;
}
.filter-bar .form-label { font-size: 0.78rem; margin-bottom: 5px; font-weight: 600; }
.filter-bar .filter-extra { grid-column: 1 / -1; display: flex; gap: 14px; flex-wrap: wrap; align-items: end; }
.filter-bar .filter-extra > div { min-width: 180px; }
.bulk-hint { padding: 8px 20px; font-size: 0.78rem; color: var(--text-secondary); }
.bulk-bar { padding: 10px 20px; display: none; gap: 14px; align-items: center; flex-wrap: wrap; background: rgba(201,168,76,0.12); border-left: 3px solid #c9a84c; font-size: 0.85rem; }
.bulk-bar a { color: inherit; text-decoration: underline; cursor: pointer; }
.row-check, #check-all { width: 16px; height: 16px; cursor: pointer; }

.active-filters {
    padding: 10px 20px;
    background: rgba(201,168,76,0.07);
    border-left: 3px solid #c9a84c;
    font-size: 0.82rem;
    color: var(--text-secondary);
}

.pagination-bar {
    padding: 16px 20px;
    display: flex; justify-content: center; align-items: center; gap: 10px;
}
.page-info {
    padding: 7px 16px;
    background: var(--bg-surface);
    border-radius: 6px;
    font-size: 0.83rem;
    color: var(--text-secondary);
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

.header-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

/* Select école dans le header — largeur auto selon contenu */
.select-ecole-massive {
    height: 38px;
    min-width: 160px;
    max-width: 260px;
    width: auto;
    font-size: 0.78rem !important; /* police réduite pour que le nom tienne */
    text-overflow: ellipsis;
    overflow: hidden;
    white-space: nowrap;
}

@media (max-width: 900px) {
    .filter-bar { grid-template-columns: 1fr 1fr; }
    .header-actions { flex-wrap: wrap; }
    .select-ecole-massive { min-width: 140px; max-width: 100%; }
}
@media (max-width: 600px) {
    .filter-bar { grid-template-columns: 1fr; }
}
</style>

<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">Élèves</h3>
            <p class="card-subtitle">Gestion des élèves — Année {{ $activeYear->label ?? '' }}</p>
        </div>

        <div class="header-actions">

            <a href="{{ route('admin.students.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Nouvel élève
            </a>

            <form method="GET" action="{{ route('admin.students.export.class.cards') }}"
                  style="display:flex; align-items:center; gap:8px;"
                  onsubmit="return validerGenerationMassive(this)">
                <select name="ecole_id" id="select-ecole-massive"
                        class="form-input select-ecole-massive">
                    <option value="">-- École --</option>
                    @foreach($ecoles as $ecole)
                        <option value="{{ $ecole->id }}">{{ $ecole->nom_ecole }}</option>
                    @endforeach
                </select>
                <select name="classe_id" id="select-classe-massive"
                        class="form-input select-ecole-massive" disabled>
                    <option value="">-- Classe --</option>
                </select>
                <button type="submit" class="btn"
                        style="background:linear-gradient(135deg,#7c3aed,#a855f7);
                               color:#fff; display:flex; align-items:center;
                               gap:6px; white-space:nowrap; height:38px;">
                    <i class="fa-solid fa-layer-group"></i> Générer les cartes
                </button>
            </form>

        </div>
    </div>

    {{-- Filtres --}}
    <form method="GET" action="{{ route('admin.students.index') }}" class="filter-bar">
        <div>
            <label class="form-label">École</label>
            <select name="ecole_id" class="form-input" style="width:100%;">
                <option value="">Toutes les écoles</option>
                @foreach($ecoles as $ecole)
                    <option value="{{ $ecole->id }}"
                        {{ $filters['ecole_id'] == $ecole->id ? 'selected' : '' }}>
                        {{ $ecole->nom_ecole }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Classe</label>
            <select name="classe_id" class="form-input" style="width:100%;">
                <option value="">Toutes les classes</option>
                @foreach($classes as $classe)
                    <option value="{{ $classe->id }}"
                        {{ $filters['classe_id'] == $classe->id ? 'selected' : '' }}>
                        {{ $classe->nom }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Nom de l'élève</label>
            <input type="text" name="nom" class="form-input" style="width:100%;"
                   placeholder="Nom, prénom ou matricule..." value="{{ $filters['nom'] ?? '' }}">
        </div>
        <div>
            <label class="form-label">Sexe</label>
            <select name="sexe" class="form-input" style="width:100%;">
                <option value="">Tous</option>
                <option value="M" {{ ($filters['sexe'] ?? '') == 'M' ? 'selected' : '' }}>Masculin</option>
                <option value="F" {{ ($filters['sexe'] ?? '') == 'F' ? 'selected' : '' }}>Féminin</option>
            </select>
        </div>
        <div style="display:flex; gap:8px; align-items:center;">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-filter"></i> Filtrer
            </button>
            <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">
                <i class="fa-solid fa-rotate-right"></i>
            </a>
        </div>

        <div class="filter-extra">
            <div>
                <label class="form-label">Division</label>
                <select name="division" class="form-input" style="width:100%;">
                    <option value="">Toutes</option>
                    <option value="__none__" {{ ($filters['division'] ?? '') === '__none__' ? 'selected' : '' }}>Sans division</option>
                    @foreach($divisions as $div)
                        <option value="{{ $div }}" {{ ($filters['division'] ?? '') === $div ? 'selected' : '' }}>{{ $div }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Élèves par page</label>
                <select name="per_page" class="form-input" style="width:100%;">
                    @foreach([15, 30, 50] as $n)
                        <option value="{{ $n }}" {{ $perPage == $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>

    {{-- Filtres actifs --}}
    @if(array_filter($filters))
    <div class="active-filters">
        @if($filters['ecole_id']) <strong>École :</strong> {{ $ecoles->find($filters['ecole_id'])->nom_ecole ?? '' }} &nbsp;|&nbsp; @endif
        @if($filters['classe_id']) <strong>Classe :</strong> {{ $classes->find($filters['classe_id'])->nom ?? '' }} &nbsp;|&nbsp; @endif
        @if($filters['nom']) <strong>Nom :</strong> "{{ $filters['nom'] }}" &nbsp;|&nbsp; @endif
        @if($filters['sexe']) <strong>Sexe :</strong> {{ $filters['sexe'] == 'M' ? 'Masculin' : 'Féminin' }} &nbsp;|&nbsp; @endif
        @if($filters['division']) <strong>Division :</strong> {{ $filters['division'] === '__none__' ? 'Sans division' : $filters['division'] }} &nbsp;|&nbsp; @endif
        <strong>{{ $eleves->total() }} résultat{{ $eleves->total() > 1 ? 's' : '' }}</strong>
    </div>
    @endif

    @if($filters['ecole_id'])
    <div id="bulk-bar" class="bulk-bar">
        <span id="bulk-count"></span>
        <button type="button" id="bulk-open" class="btn btn-primary">
            <i class="fa-solid fa-pen-to-square"></i> Modifier la sélection
        </button>
        <button type="button" id="bulk-delete" class="btn" style="background:#b3261e; color:#fff; border:none;">
            <i class="fa-solid fa-trash"></i> Supprimer la sélection
        </button>
        <a href="#" id="bulk-select-all" style="display:none;"></a>
        <a href="#" id="bulk-clear">Tout désélectionner</a>
    </div>
    @else
    <div class="bulk-hint">Choisissez une école pour pouvoir modifier plusieurs élèves à la fois (division, classe).</div>
    @endif

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    @if($filters['ecole_id'])
                    <th><input type="checkbox" id="check-all" title="Tout cocher sur cette page"></th>
                    @endif
                    <th>Photo</th>
                    <th>Matricule</th>
                    <th>N° Table</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Classe</th>
                    <th>Sexe</th>
                    <th>Naissance</th>
                    <th>Téléphone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($eleves as $eleve)
                <tr>
                    @if($filters['ecole_id'])
                    <td><input type="checkbox" class="row-check" value="{{ $eleve->id }}"></td>
                    @endif
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
                    <td>{{ $eleve->classe_libelle }}</td>
                    <td>
                        <span class="badge {{ $eleve->sexe == 'F' ? 'badge-orange' : 'badge-blue' }}">
                            {{ $eleve->sexe == 'F' ? 'F' : 'M' }}
                        </span>
                    </td>
                    <td>{{ $eleve->date_naissance
                        ? \Carbon\Carbon::parse($eleve->date_naissance)->format('d/m/Y')
                        : '—' }}</td>
                    <td>{{ $eleve->telephone_tuteur }}</td>
                    <td>
                        <div style="display:flex; gap:6px; flex-wrap:wrap;">
                            <a href="{{ route('admin.students.edit', $eleve) }}"
                               class="circle-btn circle-edit" title="Modifier">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <form id="delete-form-{{ $eleve->id }}"
                                  action="{{ route('admin.students.destroy', $eleve) }}"
                                  method="POST" style="display:none;">
                                @csrf
                                @method('DELETE')
                            </form>
                            <button type="button"
                                    onclick="confirmDelete({{ $eleve->id }}, '{{ addslashes($eleve->nom . ' ' . $eleve->prenom) }}')"
                                    class="circle-btn circle-delete" title="Supprimer">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                            <a href="{{ route('admin.students.export.card.pdf', $eleve) }}"
                               class="circle-btn circle-pdf" title="Exporter PDF">
                                <i class="fa-solid fa-download"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $filters['ecole_id'] ? 11 : 10 }}" style="text-align:center; padding:2rem; color:var(--text-secondary);">
                        Aucun élève trouvé.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($eleves->hasPages())
    <div class="pagination-bar">
        @if($eleves->onFirstPage())
            <button class="btn btn-secondary" disabled>← Précédent</button>
        @else
            <a href="{{ $eleves->previousPageUrl() }}" class="btn btn-secondary">← Précédent</a>
        @endif
        <span class="page-info">
            Page {{ $eleves->currentPage() }} / {{ $eleves->lastPage() }}
            ({{ $eleves->total() }} élève{{ $eleves->total() > 1 ? 's' : '' }})
        </span>
        @if($eleves->hasMorePages())
            <a href="{{ $eleves->nextPageUrl() }}" class="btn btn-secondary">Suivant →</a>
        @else
            <button class="btn btn-secondary" disabled>Suivant →</button>
        @endif
    </div>
    @endif

</div>

@endsection

@push('scripts')
<script>
// Classes (avec effectif) de chaque école, calculées côté serveur
const ECOLE_CLASSES = @json($ecoleClasses);

function validerGenerationMassive(form) {
    const select = document.getElementById('select-ecole-massive');
    const classeSelect = document.getElementById('select-classe-massive');
    if (!select.value) {
        Swal.fire({ icon: 'warning', title: 'École non sélectionnée',
            text: 'Veuillez sélectionner une école avant de générer les cartes.' });
        return false;
    }
    if (!classeSelect.value) {
        Swal.fire({ icon: 'warning', title: 'Classe non sélectionnée',
            text: 'Veuillez sélectionner une classe (ou « Toutes les classes »).' });
        return false;
    }
    Swal.fire({ title: 'Génération en cours...', icon: 'info',
        showConfirmButton: false, timer: 2000, timerProgressBar: true });
    return true;
}

document.addEventListener('DOMContentLoaded', function () {
    const ecoleSelect  = document.getElementById('select-ecole-massive');
    const classeSelect = document.getElementById('select-classe-massive');
    if (!ecoleSelect || !classeSelect) return;

    function addOption(value, text) {
        const opt = document.createElement('option');
        opt.value = value;
        opt.textContent = text;
        classeSelect.appendChild(opt);
    }

    ecoleSelect.addEventListener('change', function () {
        const classes = ECOLE_CLASSES[this.value] || [];
        classeSelect.innerHTML = '';

        if (!this.value) {
            addOption('', '-- Classe --');
            classeSelect.disabled = true;
            return;
        }
        if (classes.length === 0) {
            addOption('', 'Aucune classe avec élèves');
            classeSelect.disabled = true;
            return;
        }

        addOption('', '-- Classe --');

        const texte = function (o) {
            return o.label + ' — ' + o.total + (o.total > 1 ? ' élèves' : ' élève');
        };
        let lots = 0; // nombre de PDF distincts (la ligne "toute la classe" n'en est pas un)

        classes.forEach(function (niveau) {
            if (niveau.options.length === 1) {
                addOption(niveau.options[0].value, texte(niveau.options[0]));
                lots += 1;
                return;
            }
            const groupe = document.createElement('optgroup');
            groupe.label = niveau.label;
            niveau.options.forEach(function (o) {
                const opt = document.createElement('option');
                opt.value = o.value;
                opt.textContent = texte(o);
                groupe.appendChild(opt);
                if (!o.aggregate) lots += 1;
            });
            classeSelect.appendChild(groupe);
        });

        if (lots > 1) {
            addOption('all', 'Toutes les classes (ZIP)');
        }
        classeSelect.disabled = false;
    });
});

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
        Swal.fire({ icon: 'success', title: 'Succès', text: @json(session('success')),
            timer: 3000, showConfirmButton: false, toast: true, position: 'top-end' });
    @endif
    @if(session('error'))
        Swal.fire({ icon: 'error', title: 'Erreur', text: @json(session('error')) });
    @endif
});

// ====== Modification en masse (division / classe) ======
(function () {
    const bar = document.getElementById('bulk-bar');
    if (!bar) return; // actif seulement quand une école est choisie dans le filtre

    const BULK = @json($bulk);
    const ECOLE_ID = {{ (int) ($filters['ecole_id'] ?? 0) }};
    const TOTAL = {{ $eleves->total() }};
    const CSRF = '{{ csrf_token() }}';
    const ACTION = '{{ route('admin.students.bulk.update') }}';
    const URL_APERCU_SUPPR = '{{ route('admin.students.bulk.delete.preview') }}';
    const URL_EXPORT_SUPPR = '{{ route('admin.students.bulk.delete.export') }}';
    const URL_SUPPR = '{{ route('admin.students.bulk.destroy') }}';
    const AVEC_SERIE = /^(2nde|1ère|Tle|Terminale)$/i;

    const checkAll = document.getElementById('check-all');
    const countEl = document.getElementById('bulk-count');
    const selAllLink = document.getElementById('bulk-select-all');
    let modeFiltre = false;

    const rows = () => Array.from(document.querySelectorAll('.row-check'));
    const checked = () => rows().filter(function (c) { return c.checked; });
    const nombre = () => (modeFiltre ? TOTAL : checked().length);
    const pluriel = (n) => n + ' élève' + (n > 1 ? 's' : '');

    function esc(t) {
        return String(t).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function refresh() {
        const n = nombre();
        const toutePage = rows().length > 0 && checked().length === rows().length;
        bar.style.display = n > 0 ? 'flex' : 'none';
        countEl.textContent = modeFiltre
            ? pluriel(TOTAL) + ' du filtre sélectionnés'
            : pluriel(n) + ' sélectionné' + (n > 1 ? 's' : '');
        selAllLink.style.display = (!modeFiltre && toutePage && TOTAL > rows().length) ? 'inline' : 'none';
        selAllLink.textContent = 'Sélectionner les ' + TOTAL + ' élèves du filtre';
        if (checkAll) checkAll.checked = toutePage || modeFiltre;
    }

    rows().forEach(function (c) {
        c.addEventListener('change', function () { modeFiltre = false; refresh(); });
    });
    checkAll.addEventListener('change', function () {
        modeFiltre = false;
        const etat = this.checked;
        rows().forEach(function (c) { c.checked = etat; });
        refresh();
    });
    selAllLink.addEventListener('click', function (e) { e.preventDefault(); modeFiltre = true; refresh(); });
    document.getElementById('bulk-clear').addEventListener('click', function (e) {
        e.preventDefault();
        modeFiltre = false;
        rows().forEach(function (c) { c.checked = false; });
        refresh();
    });

    function formulaire() {
        const classes = '<option value="">— Ne pas changer —</option>' + BULK.classes.map(function (c) {
            return '<option value="' + c.id + '" data-nom="' + esc(c.nom) + '">' + esc(c.nom) + '</option>';
        }).join('');
        const series = '<option value="">—</option>' + BULK.series.map(function (s) {
            return '<option value="' + esc(s) + '">' + esc(s) + '</option>';
        }).join('');

        return '<div style="text-align:left; font-size:0.9rem;">'
            + '<p style="margin:0 0 12px;"><strong>' + pluriel(nombre()) + '</strong> seront modifiés.</p>'
            + '<label style="font-weight:600;">Division</label>'
            + '<select id="bk-div-action" class="swal2-select" style="width:100%; margin:4px 0 8px;">'
            + '<option value="keep">Ne pas changer</option>'
            + '<option value="set">Définir la division</option>'
            + '<option value="clear">Retirer la division</option></select>'
            + '<input id="bk-div" class="swal2-input" maxlength="12" placeholder="ex. M1, A, B" style="display:none; width:100%; margin:0 0 8px;">'
            + '<label style="font-weight:600;">Classe</label>'
            + '<select id="bk-classe" class="swal2-select" style="width:100%; margin:4px 0 8px;">' + classes + '</select>'
            + '<div id="bk-serie-wrap" style="display:none;"><label style="font-weight:600;">Série</label>'
            + '<select id="bk-serie" class="swal2-select" style="width:100%; margin:4px 0 8px;">' + series + '</select></div>'
            + '</div>';
    }

    function ouvrir() {
        if (nombre() === 0) return;

        Swal.fire({
            title: 'Modifier la sélection',
            html: formulaire(),
            showCancelButton: true,
            confirmButtonText: 'Continuer',
            cancelButtonText: 'Annuler',
            focusConfirm: false,
            didOpen: function () {
                const act = document.getElementById('bk-div-action');
                const cls = document.getElementById('bk-classe');
                act.addEventListener('change', function () {
                    document.getElementById('bk-div').style.display = act.value === 'set' ? 'block' : 'none';
                });
                cls.addEventListener('change', function () {
                    const opt = cls.options[cls.selectedIndex];
                    const avec = AVEC_SERIE.test((opt && opt.getAttribute('data-nom')) || '');
                    document.getElementById('bk-serie-wrap').style.display = avec ? 'block' : 'none';
                    if (!avec) document.getElementById('bk-serie').value = '';
                });
            },
            preConfirm: function () {
                const v = {
                    divAction: document.getElementById('bk-div-action').value,
                    division: document.getElementById('bk-div').value.trim(),
                    classe: document.getElementById('bk-classe').value,
                    serie: document.getElementById('bk-serie').value,
                    classeNom: '',
                };
                if (v.divAction === 'set' && v.division === '') {
                    Swal.showValidationMessage('Indique la division à appliquer.');
                    return false;
                }
                if (v.divAction === 'keep' && v.classe === '') {
                    Swal.showValidationMessage('Choisis au moins une modification.');
                    return false;
                }
                const sel = document.getElementById('bk-classe');
                v.classeNom = v.classe ? sel.options[sel.selectedIndex].text + (v.serie ? ' ' + v.serie : '') : '';
                return v;
            }
        }).then(function (r) {
            if (r.isConfirmed) confirmer(r.value);
        });
    }

    function confirmer(v) {
        const lignes = [];
        if (v.divAction === 'set') lignes.push('Division : <strong>' + esc(v.division.toUpperCase()) + '</strong>');
        if (v.divAction === 'clear') lignes.push('Division : <strong>aucune</strong>');
        if (v.classe) lignes.push('Classe : <strong>' + esc(v.classeNom) + '</strong>');

        Swal.fire({
            icon: 'warning',
            title: 'Confirmer la modification',
            html: '<p><strong>' + pluriel(nombre()) + '</strong></p><p>' + lignes.join('<br>')
                + '</p><p style="font-size:0.85rem;">Enregistré immédiatement pour cette école.</p>',
            showCancelButton: true,
            confirmButtonText: 'Appliquer',
            cancelButtonText: 'Annuler'
        }).then(function (r) {
            if (r.isConfirmed) envoyer(v);
        });
    }

    function envoyer(v) {
        const f = document.createElement('form');
        f.method = 'POST';
        f.action = ACTION;
        const add = function (nom, val) {
            const i = document.createElement('input');
            i.type = 'hidden';
            i.name = nom;
            i.value = (val === null || val === undefined) ? '' : val;
            f.appendChild(i);
        };
        add('_token', CSRF);
        add('ecole_id', ECOLE_ID);
        add('division_action', v.divAction);
        add('new_division', v.division);
        add('new_classe_id', v.classe);
        add('new_serie', v.serie);
        if (modeFiltre) {
            add('mode', 'filter');
            add('expected_total', TOTAL);
            Object.keys(BULK.filtres).forEach(function (k) { add(k, BULK.filtres[k]); });
        } else {
            add('mode', 'ids');
            checked().forEach(function (c) { add('ids[]', c.value); });
        }
        document.body.appendChild(f);
        f.submit();
    }

    // ----- Suppression en masse : aperçu -> liste à télécharger -> confirmation écrite -> suppression -----
    function champsSelection() {
        const c = [['_token', CSRF], ['ecole_id', ECOLE_ID]];
        if (modeFiltre) {
            c.push(['mode', 'filter'], ['expected_total', TOTAL]);
            Object.keys(BULK.filtres).forEach(function (k) { c.push([k, BULK.filtres[k]]); });
        } else {
            c.push(['mode', 'ids']);
            checked().forEach(function (x) { c.push(['ids[]', x.value]); });
        }
        return c;
    }

    function soumettre(action, champs, nouvelOnglet) {
        const f = document.createElement('form');
        f.method = 'POST';
        f.action = action;
        if (nouvelOnglet) f.target = '_blank';
        champs.forEach(function (p) {
            const i = document.createElement('input');
            i.type = 'hidden';
            i.name = p[0];
            i.value = (p[1] === null || p[1] === undefined) ? '' : p[1];
            f.appendChild(i);
        });
        document.body.appendChild(f);
        f.submit();
        if (nouvelOnglet) document.body.removeChild(f);
    }

    function apercuSuppression() {
        const corps = new URLSearchParams();
        champsSelection().forEach(function (p) { corps.append(p[0], (p[1] === null || p[1] === undefined) ? '' : p[1]); });
        return fetch(URL_APERCU_SUPPR, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: corps
        }).then(function (r) {
            return r.json().then(function (j) { return { status: r.status, j: j }; });
        });
    }

    function ouvrirSuppression() {
        if (nombre() === 0) return;

        apercuSuppression().then(function (rep) {
            const j = rep.j || {};
            if (!j.ok) {
                const message = j.message || 'La sélection n\'a pas pu être vérifiée. Rechargez la page et recommencez.';
                Swal.fire({ icon: 'error', title: 'Suppression impossible', text: message });
                return;
            }

            const lignes = j.repartition.map(function (g) {
                return '<li>' + esc(g.classe) + (g.division ? ' — division ' + esc(g.division) : '') + ' : <strong>' + g.n + '</strong></li>';
            }).join('');
            const exemples = (j.exemples || []).map(esc).join(', ');

            Swal.fire({
                icon: 'warning',
                title: 'Supprimer définitivement ?',
                html: '<div style="text-align:left; font-size:0.9rem;">'
                    + '<p style="margin:0 0 8px;"><strong>' + pluriel(j.total) + '</strong> de l\'école <strong>' + esc(j.ecole) + '</strong> seront supprimés '
                    + '<strong>définitivement</strong>, avec leur photo et leur QR code. Cette action est irréversible.</p>'
                    + '<ul style="margin:0 0 8px 18px; padding:0;">' + lignes + '</ul>'
                    + (exemples ? '<p style="margin:0 0 8px; font-size:0.82rem;">Par exemple : ' + exemples + (j.total > 5 ? '…' : '') + '</p>' : '')
                    + '<p style="margin:0 0 10px;"><a href="#" id="bd-export">Télécharger la liste de ces élèves (Excel) avant de supprimer</a></p>'
                    + '<label style="font-weight:600;">Pour confirmer, tapez <strong>SUPPRIMER</strong></label>'
                    + '<input id="bd-conf" class="swal2-input" autocomplete="off" placeholder="SUPPRIMER" style="width:100%; margin:6px 0 0;">'
                    + '</div>',
                showCancelButton: true,
                confirmButtonText: 'Supprimer définitivement',
                confirmButtonColor: '#b3261e',
                cancelButtonText: 'Annuler',
                focusCancel: true,
                didOpen: function () {
                    document.getElementById('bd-export').addEventListener('click', function (e) {
                        e.preventDefault();
                        soumettre(URL_EXPORT_SUPPR, champsSelection(), true);
                    });
                },
                preConfirm: function () {
                    const saisie = document.getElementById('bd-conf').value.trim();
                    if (saisie !== 'SUPPRIMER') {
                        Swal.showValidationMessage('Tapez exactement SUPPRIMER (en majuscules) pour confirmer.');
                        return false;
                    }
                    return saisie;
                }
            }).then(function (r) {
                if (r.isConfirmed) {
                    soumettre(URL_SUPPR, champsSelection().concat([['empreinte', j.empreinte], ['confirmation', r.value]]), false);
                }
            });
        }).catch(function () {
            Swal.fire({ icon: 'error', title: 'Erreur', text: 'Impossible de contacter le serveur. Aucun élève n\'a été supprimé.' });
        });
    }

    document.getElementById('bulk-delete').addEventListener('click', ouvrirSuppression);

    document.getElementById('bulk-open').addEventListener('click', ouvrir);
    refresh();
})();
</script>
@endpush