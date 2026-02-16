@extends('layouts.admin')

@section('content')
<div class="page-header">
    <h1 class="greeting">Nouvelle année scolaire</h1>
    <p class="greeting-sub">Création d'une année scolaire</p>
</div>

<div class="settings-grid">
    <div>
        <section class="settings-section">
            <div class="card">
                <form method="POST" action="{{ route('admin.school-years.store') }}">
                    @csrf

                    <div class="form-group">
                        <label class="form-label">Nom</label>
                        <input type="text" name="label" class="form-input" placeholder="2024-2025" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Date de début</label>
                        <input type="date" name="start_date" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Date de fin</label>
                        <input type="date" name="end_date" class="form-input" required>
                    </div>

                    <button class="btn btn-primary">Créer</button>
                </form>
            </div>
        </section>
    </div>
</div>
@endsection
