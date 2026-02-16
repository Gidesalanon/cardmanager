@extends('layouts.admin')

@section('content')
<div class="page-header">
    <h1 class="greeting">Modifier l'année scolaire</h1>
    <p class="greeting-sub">{{ $schoolYear->name }}</p>
</div>

<div class="settings-grid">
    <!-- COLONNE GAUCHE -->
    <div>
        <section class="settings-section">
            <div class="card">
                <form method="POST"
                      action="{{ route('admin.school-years.update', $schoolYear) }}">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label class="form-label">Nom</label>
                        <input type="text"
                               name="label"
                               class="form-input"
                               value="{{ $schoolYear->label }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Date de début</label>
                        <input type="date"
                               name="start_date"
                               class="form-input"
                               value="{{ $schoolYear->start_date->format('Y-m-d') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Date de fin</label>
                        <input type="date"
                               name="end_date"
                               class="form-input"
                               value="{{ $schoolYear->end_date->format('Y-m-d') }}">
                    </div>

                    <button class="btn btn-primary">Enregistrer</button>
                </form>
            </div>
        </section>
    </div>

    <!-- COLONNE DROITE : TOGGLE -->
    <div>
        <section class="settings-section">
            <div class="card">
                <h2 class="settings-title">Statut</h2>
                <p class="settings-desc">Activation de l'année scolaire</p>

                <div class="settings-row">
                    <div class="settings-row-info">
                        <div class="settings-row-label">Année active</div>
                        <div class="settings-row-desc">
                            Une seule année active à la fois
                        </div>
                    </div>

                    <label class="toggle">
                        <input type="checkbox"
                               {{ $schoolYear->is_active ? 'checked' : '' }}
                               onchange="document.getElementById('toggle-form').submit()">
                        <span class="toggle-slider"></span>
                    </label>
                </div>

                <form id="toggle-form"
                      method="POST"
                      action="{{ route('admin.school-years.toggle', $schoolYear) }}">
                    @csrf
                </form>
            </div>
        </section>
    </div>
</div>
@endsection
