<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
@page { margin:0; }

body{
    margin:0;
    font-family: Arial, sans-serif;
}

.card{
    width:8.5cm;
    height:5.5cm;
    position:relative;
    page-break-after:always;
}

.ministere{
    position:absolute;
    top:4mm;
    left:5mm;
    width:70mm;
}

.photo{
    position:absolute;
    top:25mm;
    left:6mm;
    width:22mm;
    height:28mm;
    object-fit:cover;
}

.nom{
    position:absolute;
    top:25mm;
    left:32mm;
    font-size:10pt;
}

.qr{
    position:absolute;
    top:8mm;
    left:6mm;
    width:22mm;
}

.signature{
    position:absolute;
    bottom:15mm;
    right:15mm;
    width:30mm;
}

.cachet{
    position:absolute;
    top:28mm;
    left:45mm;
    width:35mm;
    opacity:0.6;
}
</style>
</head>
<body>

@foreach($eleves as $eleve)

@php
$isPrimaire = preg_match('/^(CI|CP|CE1|CE2|CM1|CM2)/i',$eleve->classe->nom);
$ministereImage = $isPrimaire
    ? public_path('assets/card/memp.png')
    : public_path('assets/card/MESTFP.png');
@endphp

<div class="card">

    <img src="{{ $ministereImage }}" class="ministere">

    <img src="{{ public_path('storage/'.$eleve->photo) }}" class="photo">

    <div class="nom">
        <b>Nom :</b> {{ $eleve->nom }}<br>
        <b>Prénoms :</b> {{ $eleve->prenom }}<br>
        <b>Né(e) le :</b> {{ \Carbon\Carbon::parse($eleve->date_naissance)->format('d/m/Y') }}
        à {{ $eleve->lieu_naissance }}<br>
        <b>Classe :</b> {{ $eleve->classe->nom }}<br>
        <b>Adresse :</b> {{ $eleve->telephone_tuteur }}<br>
        <b>N° EducMaster :</b> {{ $eleve->matricule_edumaster }}
    </div>

    <img src="{{ public_path('storage/'.$eleve->qr_code) }}" class="qr">

    <img src="{{ public_path('storage/'.$eleve->ecole->directeur->signature) }}"
         class="signature">

    <img src="{{ public_path('storage/'.$eleve->ecole->directeur->cachet) }}"
         class="cachet">

</div>

@endforeach

</body>
</html>
