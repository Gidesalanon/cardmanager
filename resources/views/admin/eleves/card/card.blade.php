<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
@page { size: 85.6mm 54mm; margin:0; }
body{ margin:0; font-family: Arial, sans-serif; }

.card{
    width:85.6mm;
    height:54mm;
    position:relative;
}

.photo{
    position:absolute;
    top:18mm;
    left:5mm;
    width:20mm;
    height:25mm;
    object-fit:cover;
}

.info{
    position:absolute;
    top:18mm;
    left:28mm;
    font-size:10px;
}

.tricolor{
    height:3mm;
    display:flex;
}
.tricolor div{ flex:1; }
.green{ background:#008751; }
.yellow{ background:#FCD116; }
.red{ background:#CE1126; }

.recto-bottom{
    position:absolute;
    bottom:5mm;
    left:5mm;
    font-size:9px;
}

.qr{
    position:absolute;
    left:10mm;
    bottom:15mm;
    width:20mm;
}
</style>
</head>
<body>

<!-- RECTO -->
<div class="card">

    <div class="tricolor">
        <div class="green"></div>
        <div class="yellow"></div>
        <div class="red"></div>
    </div>

    @if($eleve->photo && is_file(public_path('storage/'.$eleve->photo)))
    <img src="{{ public_path('storage/'.$eleve->photo) }}" class="photo">
    @else
    <div class="photo" style="background:#e5e7eb; border:1px solid #cbd5e1; text-align:center; font-size:8px; color:#6b7280;">
        <div style="margin-top:10mm;">PHOTO</div>
    </div>
    @endif

    <div class="info">
        <b>Nom:</b> {{ $eleve->nom }}<br>
        <b>Prénom:</b> {{ $eleve->prenom }}<br>
        Né(e) le {{ $eleve->date_naissance?->format('d/m/Y') }}<br>
        à {{ $eleve->lieu_naissance }}<br>
        Classe: {{ $eleve->classe->nom }}@if(!empty($eleve->division)) {{ $eleve->division }}@endif<br>
        Tel parent: {{ $eleve->telephone_tuteur }}
    </div>

    <div class="recto-bottom">
    N° EducMaster: {{ $eleve->matricule_edumaster }}
    </div>

</div>

<div style="page-break-after:always;"></div>

<!-- VERSO -->
<div class="card">

<div class="tricolor">
<div class="green"></div>
<div class="yellow"></div>
<div class="red"></div>
</div>

<div style="text-align:center;margin-top:10mm;">
<h3>{{ strtoupper($eleve->ecole->nom_ecole ?? 'ECOLE') }}</h3>
ANNEE SCOLAIRE: {{ $activeYear->label ?? '' }}
</div>

@if($eleve->qr_code && is_file(public_path('storage/'.$eleve->qr_code)))<img src="{{ public_path('storage/'.$eleve->qr_code) }}" class="qr">@endif

<div style="position:absolute;bottom:5mm;width:100%;text-align:center;font-size:8px;">
Real: DONAMI-CHRIST TEL 97 22 48 87
</div>

</div>

</body>
</html>
