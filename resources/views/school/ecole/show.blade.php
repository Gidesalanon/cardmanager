@extends('layouts.ecole')

@section('title', 'Mon école')

@section('content')

<div class="page-header">
    <h4 class="page-title">Informations de l’école</h4>
</div>

<div class="card">
    <div class="card-body">

       @if ($ecole)
        <table class="table table-striped">
            <tr>
                <th>Nom</th>
                <td>{{ $ecole->nom_ecole }}</td>
            </tr>
            <tr>
                <th>Adresse</th>
                <td>{{ $ecole->adresse_ecole }}</td>
            </tr>
            <tr>
                <th>Téléphone</th>
                <td>{{ $ecole->telephone }}</td>
            </tr>
        </table>
        @else
            <div class="alert alert-warning">
                <strong>Aucune école enregistrée.</strong><br>
                Veuillez compléter les informations de votre établissement.
            </div>

            <a href="{{ route('school.ecole.create') }}" class="btn btn-primary">
                Créer mon école
            </a>
        @endif

    </div>
</div>

@endsection
