<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page {
            size: 85.6mm 54mm;
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: #000;
        }

        .card {
            width: 85.6mm;
            height: 54mm;
            position: relative;
            overflow: hidden;
            background-color: white;
            page-break-after: always;
            page-break-inside: avoid;
        }

        .verso {
            width: 85.6mm;
            height: 54mm;
            position: relative;
            overflow: hidden;
            background-color: white;
            page-break-inside: avoid;
            page-break-after: avoid;
        }

        .header-logo-img {
            position: absolute;
            top: 1.5mm;
            left: 2mm;
            width: 32mm;
            height: auto;
        }

        .school-top-info {
            position: absolute;
            top: 1.2mm;
            right: 2mm;
            text-align: right;
            line-height: 1;
            width: auto;
        }

        .school-top-tel {
            font-size: 5pt;
            color: #333;
            margin-top: 2px;
        }

        .table-box {
            position: absolute;
            top: 7mm;
            right: 2mm;
            border: 0.4mm solid #000;
            padding: 0.5mm 1.5mm;
            text-align: center;
        }

        .table-label {
            min-width: 70px;
            min-height: 7px;
        }

        .sign {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            width: 100%;
        }

        .sign-box {
            border: 0.4mm solid #000;
            margin-left: 120px;
            margin-top: -6px;
            text-align: center;
            height: 20px;
            width: 70px;
        }

        .card-title {
            position: absolute;
            top: 12.5mm;
            width: 100%;
            text-align: center;
            color: #e8112d;
            font-size: 7pt;
            font-weight: 900;
        }

        .qrannee img {
            max-width: 40px;
            height: auto;
            float: right;
            margin-top: 45px;
            margin-right: 7px;
        }

        .photo {
            position: absolute;
            top: 19mm;
            left: 3.5mm;
            width: 22mm;
            height: 26mm;
            object-fit: cover;
        }

        .info-container {
            position: absolute;
            top: 19.5mm;
            left: 28mm;
            width: 55mm;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding-bottom: 0.6mm;
            vertical-align: top;
        }

        .label {
            font-weight: bold;
            width: 11mm;
            height: 14px;
            font-size: 10px;
        }

        .label-sign {
            font-weight: bold;
            width: auto;
            font-size: 9px;
        }

        .educmaster-footer {
            position: absolute;
            bottom: 2.2mm;
            left: 3.5mm;
            font-size: 7pt;
            font-weight: bold;
        }

        .flag-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
            height: 1.5mm;
            display: table;
        }

        .flag-bar div {
            display: table-cell;
            width: 33.33%;
        }

        .green  { background: #008751; }
        .yellow { background: #fcd116; }
        .red    { background: #e8112d; }
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

        $nomClasse   = $eleve->classe->nom;
        $isSecondary = preg_match('/(6ème|5ème|4ème|3ème|2nde|1ère|Tle|Terminale)/i', $nomClasse);
        $logoPath    = $isSecondary
            ? public_path('assets/card/MESTFP.png')
            : public_path('assets/card/memp.png');

        $fontNomEcoleRecto = dynamicFont($eleve->ecole->nom_ecole ?? '', [
            18  => '6.5pt',
            28  => '5.5pt',
            40  => '4.8pt',
            999 => '4pt',
        ]);

        $fontNomEcoleVerso = dynamicFont($eleve->ecole->nom_ecole ?? '', [
            12  => '9.5pt',
            20  => '8.5pt',
            30  => '7.5pt',
            45  => '6.5pt',
            999 => '5.5pt',
        ]);

        $fontNom = dynamicFont($eleve->nom ?? '', [
            15  => '10px',
            22  => '9px',
            999 => '7.5px',
        ]);

        $fontPrenom = dynamicFont($eleve->prenom ?? '', [
            20  => '10px',
            30  => '9px',
            999 => '7.5px',
        ]);

        $naissance     = ($eleve->date_naissance?->format('d/m/Y') ?? '') . ' à ' . ($eleve->lieu_naissance ?? '');
        $fontNaissance = dynamicFont($naissance, [
            22  => '10px',
            30  => '9px',
            999 => '7.5px',
        ]);

        $fontNomDirecteur = dynamicFont(
            ($eleve->ecole->directeur->nom ?? '') . ' ' . ($eleve->ecole->directeur->prenom ?? ''), [
            12  => '9.5pt',
            20  => '8.5pt',
            30  => '7.5pt',
            45  => '6.5pt',
            999 => '5.5pt',
        ]);

        $telDirecteur = $eleve->ecole->directeur->telephone ?? '';
    @endphp

    <!-- ==================== RECTO ==================== -->
    <div class="card">

        <img src="{{ $logoPath }}" class="header-logo-img">

        <div class="school-top-info">
            <div style="font-size: {{ $fontNomEcoleRecto }}; font-weight: 900; line-height: 1.2;">
                {{ strtoupper($eleve->ecole->nom_ecole ?? 'ECOLE') }}
            </div>
            <div class="school-top-tel">Tél: {{ $eleve->ecole->telephone ?? '' }}</div>
        </div>

        <div class="table-box">
            <div class="table-label"></div>
        </div>

        <div class="qrannee">
            <div class="card-title">CARTE D'IDENTITÉ SCOLAIRE : {{ $activeYear->label ?? '' }}</div>
            <div class="qr-left">
                <img src="{{ public_path('storage/' . $eleve->qr_code) }}" alt="QR">
            </div>
        </div>

        <img src="{{ public_path('storage/' . $eleve->photo) }}" class="photo">

        <div class="info-container">
            <table class="info-table">
                <tr>
                    <td class="label">Nom</td>
                    <td style="font-size: {{ $fontNom }};">: {{ strtoupper($eleve->nom) }}</td>
                </tr>
                <tr>
                    <td class="label">Prénoms</td>
                    <td style="font-size: {{ $fontPrenom }};">: {{ ucwords($eleve->prenom) }}</td>
                </tr>
                <tr>
                    <td class="label">Né(e) le</td>
                    <td style="font-size: {{ $fontNaissance }};">: {{ $eleve->date_naissance?->format('d/m/Y') }} à {{ $eleve->lieu_naissance }}</td>
                </tr>
                <tr>
                    <td class="label">Classe</td>
                    <td style="font-size: 10px;">
                        : {{ $eleve->classe->nom }}
                        @if($eleve->classe->serie)
                            {{ $eleve->classe->serie->nom }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="label">Adresse</td>
                    <td style="font-size: 10px;">: {{ $eleve->telephone_tuteur }}</td>
                </tr>
            </table>

            <div class="sign">
                <div class="label-sign">Signature de l'apprenant</div>
                <div class="sign-box"></div>
            </div>
        </div>

        <div class="educmaster-footer">N° EducMaster: {{ $eleve->matricule_edumaster }}</div>

        <div class="flag-bar">
            <div class="green"></div>
            <div class="yellow"></div>
            <div class="red"></div>
        </div>
    </div>

    <!-- ==================== VERSO ==================== -->
    <div class="verso">

        {{-- 1. Barre tricolore haut --}}
        <div style="position: absolute; top: 1.5mm; left: 0; right: 0; text-align: center;">
            <img src="{{ $tricolorePath }}"
                 style="width: 40mm; height: 3.5mm; display: block; margin: 0 auto;">
        </div>

        {{-- 2. Nom école --}}
        <div style="
            position: absolute;
            top: 6.5mm;
            left: 0; right: 0;
            text-align: center;
            font-size: {{ $fontNomEcoleVerso }};
            font-weight: 900;
            letter-spacing: 0.5px;
            padding: 0 3mm;
            line-height: 1.2;
        ">{{ strtoupper($eleve->ecole->nom_ecole ?? '') }}</div>

        {{-- 3. Téléphone directeur --}}
        <div style="
            position: absolute;
            top: 13mm;
            left: 0; right: 0;
            text-align: center;
            font-size: 6pt;
            color: #333;
        ">Tél: {{ $telDirecteur }}</div>

        {{-- 4. Carte d'identité scolaire --}}
        <div style="
            position: absolute;
            top: 16mm;
            left: 0; right: 0;
            text-align: center;
            font-size: 6.5pt;
            font-weight: 700;
        ">CARTE D'IDENTITÉ SCOLAIRE : {{ $activeYear->label ?? '' }}</div>

        {{-- 5. Cachet + Signature superposés centrés --}}
        <div style="position: absolute; top: 19mm; left: 0; right: 0; height: 16mm;">
            <img src="{{ public_path('storage/' . $eleve->ecole->directeur->cachet) }}"
                 style="
                    position: absolute;
                    left: 50%; top: 50%;
                    transform: translate(-50%, -50%);
                    width: 16mm; height: 16mm;
                    object-fit: contain;
                    opacity: 0.92;
                 ">
            <img src="{{ public_path('storage/' . $eleve->ecole->directeur->signature) }}"
                 style="
                    position: absolute;
                    left: 50%; top: 50%;
                    transform: translate(-30%, -55%);
                    width: 28mm; height: 11mm;
                    object-fit: contain;
                 ">
        </div>

        {{-- 6. Le Directeur / La Directrice --}}
        <div style="
            position: absolute;
            top: 36mm;
            left: 0; right: 0;
            text-align: center;
            font-size: 7pt;
            font-weight: 700;
        ">
            @if($eleve->ecole->directeur->sexe == 'F')
                La Directrice
            @else
                Le Directeur
            @endif
        </div>

        {{-- 7. Nom Prénom directeur --}}
        <div style="
            position: absolute;
            top: 39.5mm;
            left: 0; right: 0;
            text-align: center;
            font-size: {{ $fontNomDirecteur }};
            font-weight: 700;
            padding: 0 3mm;
            line-height: 1.2;
        ">
            {{ strtoupper($eleve->ecole->directeur->nom ?? '') }}
            {{ $eleve->ecole->directeur->prenom ?? '' }}
        </div>

        {{-- 8. Barre tricolore bas --}}
        <div style="position:absolute; top:48mm; left:0; right:0; text-align:center;">
            <img src="{{ $tricolorePath }}" style="width:40mm; height:1.5mm; display:block; margin:0 auto;">
        </div>

    </div>

</body>
</html>