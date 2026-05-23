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

/**
 * Détermine si une classe est du secondaire (6e → Tle).
 * Utilise les noms réels en base : 6e, 5e, 4e, 3e, 2nde, 1ère, Tle.
 */
function isSecondaire(string $nomClasse): bool {
    return (bool) preg_match('/^(6e|5e|4e|3e|2nde|1ère|Tle|Terminale)$/i', trim($nomClasse));
}

/**
 * Détermine si la série doit s'afficher sur la carte.
 * Uniquement pour 2nde, 1ère et Tle — pas pour 6e/5e/4e/3e.
 */
function afficherSerie(string $nomClasse): bool {
    return (bool) preg_match('/^(2nde|1ère|Tle|Terminale)$/i', trim($nomClasse));
}

$tricolorePath    = public_path('assets/card/tricolore.png');
$premiereEcole    = $eleves->first()->ecole;
$premierDirecteur = $premiereEcole->directeur;
@endphp

@foreach($eleves as $eleve)
@php
    $nomClasse   = $eleve->classe->nom;
    $isSecondary = isSecondaire($nomClasse);
    $logoPath    = $isSecondary
        ? public_path('assets/card/MESTFP.png')
        : public_path('assets/card/memp.png');

    $fontNomEcoleRecto = dynamicFont($eleve->ecole->nom_ecole ?? '', [
        18 => '6.5pt', 28 => '5.5pt', 40 => '4.8pt', 999 => '4pt',
    ]);
    $fontNom = dynamicFont($eleve->nom ?? '', [
        12 => '10px', 18 => '9px', 25 => '8px', 999 => '7px',
    ]);
    $fontPrenom = dynamicFont($eleve->prenom ?? '', [
        12 => '10px', 18 => '9px', 25 => '8px', 35 => '7px', 999 => '6px',
    ]);
    $naissance     = ($eleve->date_naissance?->format('d/m/Y') ?? '') . ' à ' . ($eleve->lieu_naissance ?? '');
    $fontNaissance = dynamicFont($naissance, [
        22 => '10px', 30 => '9px', 38 => '8px', 999 => '7px',
    ]);

    // Affichage classe : "3e" seul, "2nde A" avec série
    $classeAffichee = $nomClasse;
    if (afficherSerie($nomClasse) && $eleve->classe->serie) {
        $classeAffichee .= ' ' . $eleve->classe->serie->nom;
    }
@endphp

{{-- ==================== RECTO ==================== --}}
<div style="width:85.6mm; height:54mm; position:relative; overflow:hidden; background-color:white; page-break-after:always; page-break-inside:avoid;">

    {{-- Logo ministère --}}
    <img src="{{ $logoPath }}" style="position:absolute; top:1.5mm; left:2mm; width:32mm; height:auto;">

    {{-- Nom école + téléphone --}}
    <div style="position:absolute; top:1.2mm; right:2mm; text-align:right; line-height:1;">
        <div style="font-size:{{ $fontNomEcoleRecto }}; font-weight:900; line-height:1.2;">
            {{ strtoupper($eleve->ecole->nom_ecole ?? 'ECOLE') }}
        </div>
        <div style="font-size:5pt; color:#333; margin-top:2px;">Tél: {{ $eleve->ecole->telephone ?? '' }}</div>
    </div>

    {{-- Case Numéro de table --}}
    <div style="position:absolute; top:7mm; right:2mm; text-align:right;">
        <div style="border:0.4mm solid #000; width:24mm; height:4mm;"></div>
        <div style="font-size:5pt; font-weight:600; margin-top:0.5mm; text-align:right;">Numéro de table</div>
    </div>

    {{-- Titre carte --}}
    <div style="position:absolute; top:12.5mm; width:100%; text-align:center; color:#e8112d; font-size:7pt; font-weight:900;">
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
                <td style="font-size:{{ $fontNom }}; padding-bottom:0.5mm; vertical-align:top; word-break:break-word;">: {{ strtoupper($eleve->nom) }}</td>
            </tr>
            <tr>
                <td style="font-weight:bold; width:11mm; font-size:9px; padding-bottom:0.5mm; vertical-align:top;">Prénoms</td>
                <td style="font-size:{{ $fontPrenom }}; padding-bottom:0.5mm; vertical-align:top; word-break:break-word;">: {{ ucwords($eleve->prenom) }}</td>
            </tr>
            <tr>
                <td style="font-weight:bold; width:11mm; font-size:9px; padding-bottom:0.5mm; vertical-align:top;">Né(e) le</td>
                <td style="font-size:{{ $fontNaissance }}; padding-bottom:0.5mm; vertical-align:top;">: {{ $eleve->date_naissance?->format('d/m/Y') }} à {{ $eleve->lieu_naissance }}</td>
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
{{-- ==================== FIN RECTO ==================== --}}

@endforeach

{{-- ==================== VERSO UNIQUE ==================== --}}
@php
    $fontNomEcoleVerso = dynamicFont($premiereEcole->nom_ecole ?? '', [
        12 => '9.5pt', 20 => '8.5pt', 30 => '7.5pt', 45 => '6.5pt', 999 => '5.5pt',
    ]);
    $fontNomDirecteurVerso = dynamicFont(
        ($premierDirecteur->nom ?? '') . ' ' . ($premierDirecteur->prenom ?? ''), [
        12 => '9.5pt', 20 => '8.5pt', 30 => '7.5pt', 45 => '6.5pt', 999 => '5.5pt',
    ]);
    $telDirecteurVerso = $premierDirecteur->telephone ?? '';
    $sexeDirVerso      = $premierDirecteur->sexe ?? 'M';
@endphp

<div style="width:85.6mm; height:54mm; position:relative; overflow:hidden; background-color:white; page-break-inside:avoid; page-break-after:avoid;">

    {{-- 1. Barre tricolore haut --}}
    <div style="position:absolute; top:1.5mm; left:0; right:0; text-align:center;">
        <img src="{{ $tricolorePath }}" style="width:40mm; height:3.5mm; display:block; margin:0 auto;">
    </div>

    {{-- 2. Nom école --}}
    <div style="position:absolute; top:6.5mm; left:0; right:0; text-align:center; font-size:{{ $fontNomEcoleVerso }}; font-weight:900; letter-spacing:0.5px; padding:0 3mm; line-height:1.2;">
        {{ strtoupper($premiereEcole->nom_ecole ?? '') }}
    </div>

    {{-- 3. Téléphone --}}
    <div style="position:absolute; top:13mm; left:0; right:0; text-align:center; font-size:6pt; color:#333;">
        Tél: {{ $telDirecteurVerso }}
    </div>

    {{-- 4. Carte d'identité scolaire --}}
    <div style="position:absolute; top:16mm; left:0; right:0; text-align:center; font-size:6.5pt; font-weight:700;">
        CARTE D'IDENTITÉ SCOLAIRE : {{ $activeYear->label ?? '' }}
    </div>

    {{-- 5. Cachet + Signature --}}
    <div style="position:absolute; top:19mm; left:0; right:0; height:16mm;">
        @if($premierDirecteur->cachet)
            <img src="{{ public_path('storage/' . $premierDirecteur->cachet) }}"
                 style="position:absolute; left:50%; top:50%; transform:translate(-50%,-50%);
                        width:16mm; height:16mm; object-fit:contain; opacity:0.92;">
        @endif
        @if($premierDirecteur->signature)
            <img src="{{ public_path('storage/' . $premierDirecteur->signature) }}"
                 style="position:absolute; left:50%; top:50%; transform:translate(-30%,-55%);
                        width:28mm; height:11mm; object-fit:contain;">
        @endif
    </div>

    {{-- 6. Le Directeur / La Directrice --}}
    <div style="position:absolute; top:36mm; left:0; right:0; text-align:center; font-size:7pt; font-weight:700;">
        {{ $sexeDirVerso == 'F' ? 'La Directrice' : 'Le Directeur' }}
    </div>

    {{-- 7. Nom Prénom directeur --}}
    <div style="position:absolute; top:39.5mm; left:0; right:0; text-align:center; font-size:{{ $fontNomDirecteurVerso }}; font-weight:700; padding:0 3mm; line-height:1.2;">
        {{ strtoupper($premierDirecteur->nom ?? '') }} {{ $premierDirecteur->prenom ?? '' }}
    </div>

    {{-- 8. Barre tricolore bas --}}
    <div style="position:absolute; top:48mm; left:0; right:0; text-align:center;">
        <img src="{{ $tricolorePath }}" style="width:40mm; height:3.5mm; display:block; margin:0 auto;">
    </div>

</div>

</body>
</html>
