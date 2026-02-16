@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">Années scolaires</h3>
            <p class="card-subtitle">Gestion des années scolaires</p>
        </div>
        <a href="{{ route('admin.school-years.create') }}" class="btn btn-primary">
            Nouvelle année
        </a>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Année</th>
                    <th>Période</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($schoolYears as $year)
                <tr>
                    <td>
                        <div style="font-weight: 500;">{{ $year->label }}</div>
                    </td>
                    <td>
                        {{ $year->start_date->format('d/m/Y') }}
                        →
                        {{ $year->end_date->format('d/m/Y') }}
                    </td>
                    <td>
                        @if($year->is_active)
                            <span class="badge badge-green">Active</span>
                        @else
                            <span class="badge badge-red">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.school-years.edit', $year) }}"
                           class="btn btn-ghost">
                            Modifier
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
