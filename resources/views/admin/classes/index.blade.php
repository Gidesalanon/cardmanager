@extends('layouts.admin')

@section('title', 'Classes disponibles')

@section('content')

<div class="page-header">
    <h4 class="page-title">Classes disponibles</h4>
</div>

<div class="card">
    <div class="card-body">

        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Section</th>
                        <th>Classe</th>
                        <th>Série</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($classes as $classe)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <span class="badge badge-info">
                                    {{ ucfirst($classe->section->nom) }}
                                </span>
                            </td>
                            <td>{{ $classe->nom }}</td>
                            <td>
                                @if($classe->serie)
                                    <span class="badge badge-secondary">
                                        {{ $classe->serie->nom }}
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                Aucune classe disponible
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>

@endsection
