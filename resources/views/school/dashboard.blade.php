@extends('layouts.ecole')

@section('title', 'Dashboard école')

@section('content')

<div class="page-header">
    <h1 class="greeting">
        
    </h1>
    <p class="greeting-sub">
        Tableau de bord analytique de votre établissement
    </p>
</div>

{{-- ================= STATS ================= --}}
<div class="stats-grid">

    <div class="stat-card">
        <div class="stat-label">Total Élèves</div>
        <div class="stat-value">0</div>
        <div class="stat-change positive">
            Effectif global inscrit
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Total Classes</div>
        <div class="stat-value">0</div>
        <div class="stat-change positive">
            Classes actives
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Filles</div>
        <div class="stat-value">0</div>
        <div class="stat-change positive">
            0
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Garçons</div>
        <div class="stat-value">0</div>
        <div class="stat-change negative">
            0
        </div>
    </div>

</div>

{{-- ================= TOP CLASSES ================= --}}
<div class="card" style="margin-top:2rem;">
    <div class="card-header">
        <div>
            <h3 class="card-title">Classes les plus remplies</h3>
            <p class="card-subtitle">Top 5 des effectifs</p>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Classe</th>
                    <th>Effectif</th>
                    <th>% du total</th>
                </tr>
            </thead>
            <tbody>
               
                <tr>
                    <td>0</td>
                    <td>0</td>
                    <td>
                        0
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@endsection
