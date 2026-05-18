<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { size: 85.6mm 54mm; margin: 0; }
        body { margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; color: #000; }
    </style>
</head>
<body>

@php
function dynamicFont(string $text, array $steps): string {
    $len = mb_strlen(trim($text));
    foreach ($steps as $limit => $size) {
        if ($len <= $limit) return $size;
    }
    return end($steps);
}
$tricolorePath = public_path('assets/card/tricolore.png');
@endphp

@foreach($eleves as $eleve)
@php
    $nomClasse   = $eleve->classe->nom;
    $isSecondary = preg_match('/(6ème|5ème|4ème|3ème|2nde|1ère|Tle|Terminale)/i', $nomClasse);
    $logoPath    = $isSecondary
        ? public_path('assets/card/MESTFP.png')
        : public_path('assets/card/memp.png');

    $fontNomEcoleRecto = dynamicFont($eleve->ecole->nom_ecole ?? '', [
        18 => '6.5pt', 28 => '5.5pt', 40 => '4.8pt', 999 => '4pt',
    ]);
    $fontNomEcoleVerso = dynamicFont($eleve->ecole->nom_ecole ?? '', [
        12 => '9.5pt', 20 => '8.5pt', 30 => '7.5pt', 45 => '6.5pt', 999 => '5.5pt',
    ]);
    $fontNom = dynamicFont($eleve->nom ?? '', [
        15 => '10px', 22 => '9px', 999 => '7.5px',
    ]);
    $fontPrenom = dynamicFont($eleve->prenom ?? '', [
        20 => '10px', 30 => '9px', 999 => '7.5px',
    ]);
    $naissance = ($eleve->date_naissance?->format('d/m/Y') ?? '') . ' à ' . ($eleve->lieu_naissance ?? '');
    $fontNaissance = dynamicFont($naissance, [
        22 => '10px', 30 => '9px', 999 => '7.5px',
    ]);
    $fontNomDirecteur = dynamicFont(
        ($eleve->ecole->directeur->nom ?? '') . ' ' . ($eleve->ecole->directeur->prenom ?? ''), [
        12 => '9.5pt', 20 => '8.5pt', 30 => '7.5pt', 45 => '6.5pt', 999 => '5.5pt',
    ]);
    $telDirecteur = $eleve->ecole->directeur->telephone ?? '';
    $sexeDir      = $eleve->ecole->directeur->sexe ?? 'M';
@endphp

{{-- ==================== RECTO ==================== --}}
<div style="width:85.6mm; height:54mm; position:relative; overflow:hidden; background-color:white; page-break-after:always; page-break-inside:avoid;">

    <img src="{{ $logoPath }}" style="position:absolute; top:1.5mm; left:2mm; width:32mm; height:auto;">

    <div style="position:absolute; top:1.2mm; right:2mm; text-align:right; line-height:1;">
        <div style="font-size:{{ $fontNomEcoleRecto }}; font-weight:900; line-height:1.2;">{{ strtoupper($eleve->ecole->nom_ecole ?? 'ECOLE') }}</div>
        <div style="font-size:5pt; color:#333; margin-top:2px;">Tél: {{ $eleve->ecole->telephone ?? '' }}</div>
    </div>

    <div style="position:absolute; top:7mm; right:2mm; border:0.4mm solid #000; padding:0.5mm 1.5mm; text-align:center;">
        <div style="min-width:70px; min-height:7px;"></div>
    </div>

    <div style="position:absolute; top:12.5mm; width:100%; text-align:center; color:#e8112d; font-size:7pt; font-weight:900;">
        CARTE D'IDENTITÉ SCOLAIRE : {{ $activeYear->label ?? '' }}
    </div>

    <img src="{{ public_path('storage/' . $eleve->qr_code) }}"
         style="position:absolute; top:19mm; right:2mm; max-width:40px; height:auto;">

    <img src="{{ public_path('storage/' . $eleve->photo) }}"
         style="position:absolute; top:19mm; left:3.5mm; width:22mm; height:26mm; object-fit:cover;">

    <div style="position:absolute; top:19.5mm; left:28mm; width:55mm;">
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td style="font-weight:bold; width:11mm; font-size:10px; padding-bottom:0.6mm; vertical-align:top;">Nom</td>
                <td style="font-size:{{ $fontNom }}; padding-bottom:0.6mm; vertical-align:top;">: {{ strtoupper($eleve->nom) }}</td>
            </tr>
            <tr>
                <td style="font-weight:bold; width:11mm; font-size:10px; padding-bottom:0.6mm; vertical-align:top;">Prénoms</td>
                <td style="font-size:{{ $fontPrenom }}; padding-bottom:0.6mm; vertical-align:top;">: {{ ucwords($eleve->prenom) }}</td>
            </tr>
            <tr>
                <td style="font-weight:bold; width:11mm; font-size:10px; padding-bottom:0.6mm; vertical-align:top;">Né(e) le</td>
                <td style="font-size:{{ $fontNaissance }}; padding-bottom:0.6mm; vertical-align:top;">: {{ $eleve->date_naissance?->format('d/m/Y') }} à {{ $eleve->lieu_naissance }}</td>
            </tr>
            <tr>
                <td style="font-weight:bold; width:11mm; font-size:10px; padding-bottom:0.6mm; vertical-align:top;">Classe</td>
                <td style="font-size:10px; padding-bottom:0.6mm; vertical-align:top;">
                    : {{ $eleve->classe->nom }}
                    @if($eleve->classe->serie) {{ $eleve->classe->serie->nom }} @endif
                </td>
            </tr>
            <tr>
                <td style="font-weight:bold; width:11mm; font-size:10px; padding-bottom:0.6mm; vertical-align:top;">Adresse</td>
                <td style="font-size:10px; padding-bottom:0.6mm; vertical-align:top;">: {{ $eleve->telephone_tuteur }}</td>
            </tr>
        </table>
        <div style="margin-top:2mm;">
            <span style="font-weight:bold; font-size:9px;">Signature de l'apprenant</span>
            <span style="display:inline-block; border:0.4mm solid #000; width:18mm; height:5mm; margin-left:3mm;"></span>
        </div>
    </div>

    <div style="position:absolute; bottom:2.2mm; left:3.5mm; font-size:7pt; font-weight:bold;">
        N° EducMaster: {{ $eleve->matricule_edumaster }}
    </div>

    <div style="position:absolute; top:48mm; left:0; right:0; text-align:center;">
        <img src="{{ $tricolorePath }}" style="width:40mm; height:1.5mm; display:block; margin:0 auto;">
    </div>

</div>

{{-- ==================== VERSO ==================== --}}
<div style="width:85.6mm; height:54mm; position:relative; overflow:hidden; background-color:white; page-break-inside:avoid; page-break-after:avoid;">

    <div style="position:absolute; top:1.5mm; left:0; right:0; text-align:center;">
        <img src="{{ $tricolorePath }}" style="width:40mm; height:3.5mm; display:block; margin:0 auto;">
    </div>

    <div style="position:absolute; top:6.5mm; left:0; right:0; text-align:center; font-size:{{ $fontNomEcoleVerso }}; font-weight:900; letter-spacing:0.5px; padding:0 3mm; line-height:1.2;">
        {{ strtoupper($eleve->ecole->nom_ecole ?? '') }}
    </div>

    <div style="position:absolute; top:13mm; left:0; right:0; text-align:center; font-size:6pt; color:#333;">
        Tél: {{ $telDirecteur }}
    </div>

    <div style="position:absolute; top:16mm; left:0; right:0; text-align:center; font-size:6.5pt; font-weight:700;">
        CARTE D'IDENTITÉ SCOLAIRE : {{ $activeYear->label ?? '' }}
    </div>

    <div style="position:absolute; top:19mm; left:0; right:0; height:16mm;">
        <img src="{{ public_path('storage/' . $eleve->ecole->directeur->cachet) }}"
             style="position:absolute; left:50%; top:50%; transform:translate(-50%,-50%); width:16mm; height:16mm; object-fit:contain; opacity:0.92;">
        <img src="{{ public_path('storage/' . $eleve->ecole->directeur->signature) }}"
             style="position:absolute; left:50%; top:50%; transform:translate(-30%,-55%); width:28mm; height:11mm; object-fit:contain;">
    </div>

    <div style="position:absolute; top:36mm; left:0; right:0; text-align:center; font-size:7pt; font-weight:700;">
        {{ $sexeDir == 'F' ? 'La Directrice' : 'Le Directeur' }}
    </div>

    <div style="position:absolute; top:39.5mm; left:0; right:0; text-align:center; font-size:{{ $fontNomDirecteur }}; font-weight:700; padding:0 3mm; line-height:1.2;">
        {{ strtoupper($eleve->ecole->directeur->nom ?? '') }} {{ $eleve->ecole->directeur->prenom ?? '' }}
    </div>

    <div style="position:absolute; top:48mm; left:0; right:0; text-align:center;">
        <img src="{{ $tricolorePath }}" style="width:40mm; height:3.5mm; display:block; margin:0 auto;">
    </div>

</div>

@endforeach

</body>
</html>