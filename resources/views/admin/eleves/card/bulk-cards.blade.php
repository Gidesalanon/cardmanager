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
        }

        .page-break {
            page-break-after: always;
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
            border: 0.3mm solid #000;
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

        /* VERSO */
        .verso-photo {
            width: 85.6mm;
            height: 54mm;
            position: relative;
            overflow: hidden;
            background-color: white;
        }

        .school-name {
            text-align: center;
            font-weight: bold;
            color: #020202;
            letter-spacing: 1px;
            padding-top: 10px;
        }

        .school-year {
            text-align: center;
            font-size: 8px;
            margin-top: 3px;
            color: #000000;
        }

        .center-zone {
            text-align: center;
            width: 100%;
            margin-top: 20px;
        }

        .stamp-signature,
        .stamp-cachet {
            display: inline-block;
            vertical-align: middle;
            margin: 0 20px;
        }

        .center-zone img {
            max-width: 100px;
            height: auto;
        }

        .director-name {
            text-align: center;
            font-weight: 700;
            margin-top: 5px;
            color: #050505;
        }

        .footer-real {
            position: relative;
            text-align: center;
            font-size: 8px;
            color: #000000;
            margin-top: 5px;
        }

        .flag-bar {
            position: absolute;
            bottom: 0px;
            left: 0;
            right: 0;
            width: 100%;
            height: 1.5mm;
            display: table;
        }

        .flag-bar div {
            display: table-cell;
            width: 33.33%;
            height: 1%;
        }

        .green  { background: #008751; }
        .yellow { background: #fcd116; }
        .red    { background: #e8112d; }
    </style>
</head>
<body>

@php
function dynamicFontBulk(string $text, array $steps): string {
    $len = mb_strlen(trim($text));
    foreach ($steps as $limit => $size) {
        if ($len <= $limit) return $size;
    }
    return end($steps);
}
@endphp

@foreach($eleves as $eleve)
@php
    $nomClasse   = $eleve->classe->nom;
    $isSecondary = preg_match('/(6ème|5ème|4ème|3ème|2nde|1ère|Tle|Terminale)/i', $nomClasse);
    $logoPath    = $isSecondary
        ? public_path('assets/card/MESTFP.png')
        : public_path('assets/card/memp.png');

    $fontNomEcoleRecto = dynamicFontBulk($eleve->ecole->nom_ecole ?? '', [
        18  => '6.5pt', 28 => '5.5pt', 40 => '4.8pt', 999 => '4pt',
    ]);
    $fontNomEcoleVerso = dynamicFontBulk($eleve->ecole->nom_ecole ?? '', [
        20  => '12px', 32 => '10px', 45 => '8.5px', 999 => '7px',
    ]);
    $fontNom = dynamicFontBulk($eleve->nom ?? '', [
        15  => '10px', 22 => '9px', 999 => '7.5px',
    ]);
    $fontPrenom = dynamicFontBulk($eleve->prenom ?? '', [
        20  => '10px', 30 => '9px', 999 => '7.5px',
    ]);
    $naissance     = ($eleve->date_naissance?->format('d/m/Y') ?? '') . ' à ' . ($eleve->lieu_naissance ?? '');
    $fontNaissance = dynamicFontBulk($naissance, [
        22  => '10px', 30 => '9px', 999 => '7.5px',
    ]);
    $nomDirecteur     = ($eleve->ecole->directeur->prenom ?? '') . ' ' . ($eleve->ecole->directeur->nom ?? '');
    $fontNomDirecteur = dynamicFontBulk($nomDirecteur, [
        20  => '12px', 30 => '10px', 999 => '8px',
    ]);
@endphp

    <!-- RECTO -->
    <div class="card page-break">
        <img src="{{ $logoPath }}" class="header-logo-img">

        <div class="school-top-info">
            <div style="font-size: {{ $fontNomEcoleRecto }}; font-weight:900; line-height:1.2;">
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
                    <td style="font-size: {{ $fontNaissance }};">
                        : {{ $eleve->date_naissance?->format('d/m/Y') }} à {{ $eleve->lieu_naissance }}
                    </td>
                </tr>
                <tr>
                    <td class="label">Classe</td>
                    <td style="font-size:10px;">
                        : {{ $eleve->classe->nom }}
                        @if($eleve->classe->serie)
                            {{ $eleve->classe->serie->nom }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="label">Adresse</td>
                    <td style="font-size:10px;">: {{ $eleve->telephone_tuteur }}</td>
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

    <!-- VERSO -->
    <div class="memp-card verso-photo page-break">

        <div class="school-name" style="font-size: {{ $fontNomEcoleVerso }};">
            {{ strtoupper($eleve->ecole->nom_ecole ?? '') }}
        </div>

        <div class="school-year">
            ANNEE SCOLAIRE {{ $activeYear->label ?? '' }}
        </div>

        <div class="center-zone">
            <div class="stamp-signature">
                <img src="{{ public_path('storage/' . $eleve->ecole->directeur->signature) }}" alt="Signature">
            </div>
            <div class="stamp-cachet">
                <img src="{{ public_path('storage/' . $eleve->ecole->directeur->cachet) }}" alt="Cachet">
            </div>
        </div>

        <div class="director-name" style="font-size: {{ $fontNomDirecteur }};">
            <span>
                @if($eleve->ecole->directeur->sexe == 'F')
                    La Directrice
                @else
                    Le Directeur
                @endif
                <br>
            </span>
            {{ $eleve->ecole->directeur->prenom }}
            {{ strtoupper($eleve->ecole->directeur->nom ?? '') }}
        </div>

        <div class="footer-real">
            Réal: DONAMI-CHRIST - TEL: 01 97 22 48 87
        </div>

        <div class="flag-bar">
            <div class="green"></div>
            <div class="yellow"></div>
            <div class="red"></div>
        </div>
    </div>

@endforeach

</body>
</html>