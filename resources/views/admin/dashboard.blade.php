@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')

<div class="page-header">
    <h1 class="greeting">
        Bienvenue, {{ auth()->user()->role ?? 'Admin' }}
    </h1>
    <p class="greeting-sub">
        Vue analytique globale de la plateforme
    </p>
</div>

{{-- ================= STATS GLOBAL ================= --}}
<div class="stats-grid">

    <div class="stat-card">
        <div class="stat-label">Écoles</div>
        <div class="stat-value">{{ number_format($totalEcoles) }}</div>
        <div class="stat-change positive">
            Établissements enregistrés
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Utilisateurs</div>
        <div class="stat-value">{{ number_format($totalUsers) }}</div>
        <div class="stat-change positive">
            Comptes créés
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Élèves</div>
        <div class="stat-value">{{ number_format($totalEleves) }}</div>
        <div class="stat-change positive">
            Base totale
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Classes actives</div>
        <div class="stat-value">{{ $totalClasses }}</div>
        <div class="stat-change positive">
            Classes utilisées
        </div>
    </div>

</div>

{{-- ================= REPARTITION GENRE ================= --}}
<div class="card" style="margin-top:2rem;">
    <div class="card-header">
        <div>
            <h3 class="card-title">Répartition globale</h3>
            <p class="card-subtitle">Garçons vs Filles</p>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Filles</div>
            <div class="stat-value">{{ $filles }}</div>
            <div class="stat-change positive">
                {{ $totalEleves ? round(($filles/$totalEleves)*100,1) : 0 }}%
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Garçons</div>
            <div class="stat-value">{{ $garcons }}</div>
            <div class="stat-change negative">
                {{ $totalEleves ? round(($garcons/$totalEleves)*100,1) : 0 }}%
            </div>
        </div>
    </div>
</div>

{{-- ================= TOP ECOLES ================= --}}
<div class="card" style="margin-top:2rem;">
    <div class="card-header">
        <div>
            <h3 class="card-title">Top Écoles</h3>
            <p class="card-subtitle">Par nombre d'élèves</p>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>École</th>
                    <th>Effectif</th>
                    <th>% plateforme</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topEcoles as $ecole)
                <tr>
                    <td>{{ $ecole->nom_ecole }}</td>
                    <td>{{ $ecole->eleves_count }}</td>
                    <td>
                        {{ $totalEleves ? round(($ecole->eleves_count/$totalEleves)*100,1) : 0 }}%
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection
