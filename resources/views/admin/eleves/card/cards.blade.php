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

function cleanText(string $text): string {
    $text = preg_replace('/_x000[Dd]_/u', ' ', $text);
    $text = preg_replace('/\r\n|\r|\n/', ' ', $text);
    return trim(preg_replace('/\s{2,}/', ' ', $text));
}

function isSecondaire(string $nomClasse): bool {
    return (bool) preg_match('/^(6e|5e|4e|3e|2nde|1ère|Tle|Terminale)$/i', trim($nomClasse));
}

function afficherSerie(string $nomClasse): bool {
    return (bool) preg_match('/^(2nde|1ère|Tle|Terminale)$/i', trim($nomClasse));
}

$tricolorePath = public_path('assets/card/tricolore.png');
$nomClasse     = $eleve->classe->nom;
$isSecondary   = isSecondaire($nomClasse);
$logoPath      = $isSecondary
    ? public_path('assets/card/MESTFP.png')
    : public_path('assets/card/memp.png');

$classeAffichee = $nomClasse;
if (afficherSerie($nomClasse) && $eleve->classe->serie) {
    $classeAffichee .= ' ' . $eleve->classe->serie->nom;
}

$nomPropre    = cleanText($eleve->nom ?? '');
$prenomPropre = cleanText($eleve->prenom ?? '');
$lieuPropre   = cleanText($eleve->lieu_naissance ?? '');

$fontNomEcoleRecto = dynamicFont($eleve->ecole->nom_ecole ?? '', [
    18 => '6.5pt', 28 => '5.5pt', 40 => '4.8pt', 999 => '4pt',
]);
$fontNomEcoleVerso = dynamicFont($eleve->ecole->nom_ecole ?? '', [
    12 => '9.5pt', 20 => '8.5pt', 30 => '7.5pt', 45 => '6.5pt', 999 => '5.5pt',
]);
$fontNom = dynamicFont($nomPropre, [
    12 => '10px', 18 => '9px', 25 => '8px', 999 => '7px',
]);
$fontPrenom = dynamicFont($prenomPropre, [
    12 => '10px', 18 => '9px', 25 => '8px', 35 => '7px', 999 => '6px',
]);
$naissance     = ($eleve->date_naissance?->format('d/m/Y') ?? '') . ' à ' . $lieuPropre;
$fontNaissance = dynamicFont($naissance, [
    22 => '10px', 30 => '9px', 38 => '8px', 999 => '7px',
]);
$fontNomDirecteur = dynamicFont(
    ($eleve->ecole->directeur->nom ?? '') . ' ' . ($eleve->ecole->directeur->prenom ?? ''), [
    12 => '9.5pt', 20 => '8.5pt', 30 => '7.5pt', 45 => '6.5pt', 999 => '5.5pt',
]);
$telDirecteur = $eleve->ecole->directeur->telephone ?? '';
$sexeDir      = $eleve->ecole->directeur->sexe ?? 'M';

// Numéro de table — 9pt fixe, letter-spacing adaptatif pour tenir dans la case
$numTable = $eleve->numero_table ?? '';
$lenNum   = mb_strlen($numTable);
if      ($lenNum === 0)  { $fontNumTable = '9pt'; $lsNum = '0px'; }
elseif  ($lenNum <= 4)   { $fontNumTable = '9pt'; $lsNum = '0.5px'; }
elseif  ($lenNum <= 6)   { $fontNumTable = '9pt'; $lsNum = '0.2px'; }
elseif  ($lenNum <= 8)   { $fontNumTable = '9pt'; $lsNum = '0px'; }
elseif  ($lenNum <= 10)  { $fontNumTable = '9pt'; $lsNum = '-0.3px'; }
elseif  ($lenNum <= 12)  { $fontNumTable = '9pt'; $lsNum = '-0.5px'; }
else                     { $fontNumTable = '9pt'; $lsNum = '-0.8px'; }
@endphp

{{-- ==================== RECTO ==================== --}}
<div style="width:85.6mm; height:54mm; position:relative; overflow:hidden;
            background-color:white; page-break-after:always; page-break-inside:avoid;">

    {{-- Logo ministère --}}
    <img src="{{ $logoPath }}" style="position:absolute; top:1.5mm; left:2mm; width:32mm; height:auto;">

    {{-- Nom école + téléphone --}}
    <div style="position:absolute; top:1.2mm; right:2mm; text-align:right; line-height:1;">
        <div style="font-size:{{ $fontNomEcoleRecto }}; font-weight:900; line-height:1.2;">
            {{ strtoupper($eleve->ecole->nom_ecole ?? 'ECOLE') }}
        </div>
        <div style="font-size:5pt; color:#333; margin-top:2px;">
            Tél: {{ $eleve->ecole->telephone ?? '' }}
        </div>
    </div>

    {{--
        Case Numéro de table :
        - Hauteur fixe 4mm, overflow hidden → la case ne grandit jamais
        - Numéro centré horizontalement et verticalement
        - Police condensée simulée : letter-spacing négatif + font-weight 700
        - Intitulé "Numéro de table" aligné à droite (comme l'illustration)
    --}}
    <div style="position:absolute; top:7mm; right:2mm; width:24mm;">
        <div style="border:0.4mm solid #000; width:24mm; height:4mm;
                    padding:0; margin:0;
                    display:flex; align-items:center; justify-content:center;
                    overflow:hidden; box-sizing:border-box;">
            @if($numTable)
                <span style="font-family: Arial, sans-serif;
                             font-size:{{ $fontNumTable }};
                             font-weight:700;
                             letter-spacing:{{ $lsNum }};
                             line-height:1;
                             white-space:nowrap;
                             display:block;">
                    {{ $numTable }}
                </span>
            @endif
        </div>
        <div style="font-size:4.5pt; font-weight:600; margin-top:0.4mm;
                    text-align:right; width:24mm;">
            Numéro de table
        </div>
    </div>

    {{-- Titre carte --}}
    <div style="position:absolute; top:12.5mm; width:100%; text-align:center;
                color:#e8112d; font-size:7pt; font-weight:900;">
        CARTE D'IDENTITÉ SCOLAIRE : {{ $activeYear->label ?? '' }}
    </div>

    {{-- QR Code --}}
    <img src="{{ public_path('storage/' . $eleve->qr_code) }}"
         style="position:absolute; top:17mm; right:2mm; width:15mm; height:15mm; object-fit:contain;">

    {{-- Photo élève --}}
    <img src="{{ public_path('storage/' . $eleve->photo) }}"
         style="position:absolute; top:17mm; left:3.5mm; width:22mm; height:26mm; object-fit:cover;">

    {{-- Infos élève --}}
    <div style="position:absolute; top:17mm; left:28mm; width:40mm; height:29mm; overflow:hidden;">
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td style="font-weight:bold; width:11mm; font-size:9px; padding-bottom:0.5mm; vertical-align:top;">Nom</td>
                <td style="font-size:{{ $fontNom }}; padding-bottom:0.5mm; vertical-align:top; word-break:break-word;">: {{ strtoupper($nomPropre) }}</td>
            </tr>
            <tr>
                <td style="font-weight:bold; width:11mm; font-size:9px; padding-bottom:0.5mm; vertical-align:top;">Prénoms</td>
                <td style="font-size:{{ $fontPrenom }}; padding-bottom:0.5mm; vertical-align:top; word-break:break-word;">: {{ ucwords(strtolower($prenomPropre)) }}</td>
            </tr>
            <tr>
                <td style="font-weight:bold; width:11mm; font-size:9px; padding-bottom:0.5mm; vertical-align:top;">Né(e) le</td>
                <td style="font-size:{{ $fontNaissance }}; padding-bottom:0.5mm; vertical-align:top;">: {{ $eleve->date_naissance?->format('d/m/Y') }} à {{ $lieuPropre }}</td>
            </tr>
            <tr>
                <td style="font-weight:bold; width:11mm; font-size:9px; padding-bottom:0.5mm; vertical-align:top;">Classe</td>
                <td style="font-size:9px; padding-bottom:0.5mm; vertical-align:top;">: {{ $classeAffichee }}</td>
            </tr>
            <tr>
                <td style="font-weight:bold; width:11mm; font-size:9px; padding-bottom:0.5mm; vertical-align:top;">Adresse</td>
                <td style="font-size:9px; padding-bottom:0.5mm; vertical-align:top;">: {{ $eleve->telephone_tuteur }}</td>
            </tr>
            <tr>
                <td style="font-weight:bold; font-size:8px; vertical-align:middle; padding-top:1mm;">Signature</td>
                <td style="vertical-align:middle; padding-top:1mm;">
                    <span style="display:inline-block; border:0.4mm solid #000; width:20mm; height:4mm;"></span>
                </td>
            </tr>
        </table>
    </div>

    {{-- Barre tricolore --}}
    <div style="position:absolute; top:47mm; left:28mm;">
        <img src="{{ $tricolorePath }}" style="width:38mm; height:2mm; display:block;">
    </div>

    {{-- N° EducMaster en bas --}}
    @if($eleve->matricule_edumaster)
        <div style="position:absolute; bottom:1.5mm; left:3.5mm; font-size:7pt; font-weight:bold;">
            N° EducMaster: {{ $eleve->matricule_edumaster }}
        </div>
    @endif

</div>

{{-- ==================== VERSO ==================== --}}
<div style="width:85.6mm; height:54mm; position:relative; overflow:hidden;
            background-color:white; page-break-inside:avoid; page-break-after:avoid;">

    <div style="position:absolute; top:1.5mm; left:0; right:0; text-align:center;">
        <img src="{{ $tricolorePath }}" style="width:40mm; height:3.5mm; display:block; margin:0 auto;">
    </div>

    <div style="position:absolute; top:6.5mm; left:0; right:0; text-align:center;
                font-size:{{ $fontNomEcoleVerso }}; font-weight:900;
                letter-spacing:0.5px; padding:0 3mm; line-height:1.2;">
        {{ strtoupper($eleve->ecole->nom_ecole ?? '') }}
    </div>

    <div style="position:absolute; top:13mm; left:0; right:0; text-align:center; font-size:6pt; color:#333;">
        Tél: {{ $telDirecteur }}
    </div>

    <div style="position:absolute; top:16mm; left:0; right:0; text-align:center; font-size:6.5pt; font-weight:700;">
        CARTE D'IDENTITÉ SCOLAIRE : {{ $activeYear->label ?? '' }}
    </div>

    <div style="position:absolute; top:19mm; left:0; right:0; height:16mm;">
        @if($eleve->ecole->directeur->cachet)
            <img src="{{ public_path('storage/' . $eleve->ecole->directeur->cachet) }}"
                 style="position:absolute;
                        width:18mm; height:18mm;
                        object-fit:contain;
                        left:50%; top:50%;
                        transform:translate(-60%, -50%);
                        opacity:0.90;">
        @endif
        @if($eleve->ecole->directeur->signature)
            <img src="{{ public_path('storage/' . $eleve->ecole->directeur->signature) }}"
                 style="position:absolute;
                        width:30mm; height:12mm;
                        object-fit:contain;
                        left:50%; top:50%;
                        transform:translate(-35%, -50%);">
        @endif
    </div>

    <div style="position:absolute; top:36mm; left:0; right:0; text-align:center; font-size:7pt; font-weight:700;">
        {{ $sexeDir == 'F' ? 'La Directrice' : 'Le Directeur' }}
    </div>

    <div style="position:absolute; top:39.5mm; left:0; right:0; text-align:center;
                font-size:{{ $fontNomDirecteur }}; font-weight:700; padding:0 3mm; line-height:1.2;">
        {{ strtoupper($eleve->ecole->directeur->nom ?? '') }} {{ $eleve->ecole->directeur->prenom ?? '' }}
    </div>

    <div style="position:absolute; top:48mm; left:0; right:0; text-align:center;">
        <img src="{{ $tricolorePath }}" style="width:40mm; height:3.5mm; display:block; margin:0 auto;">
    </div>

</div>

</body>
</html>