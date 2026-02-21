<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        /* CONFIGURATION PAPIER ISO-CR80 */
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

        /* Saut de page pour séparer Recto et Verso */
        .page-break {
            page-break-after: always;
        }

        /* --- ÉLÉMENTS DU RECTO --- */

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
            width: 25mm;
        }

        .school-top-name {
            font-size: 6.5pt;
            font-weight: 900;
        }

        .school-top-tel {
            font-size: 5pt;
            color: #333;
        }

        .table-box {
            position: absolute;
            top: 7mm;
            right: 2mm;
            border: 0.25mm solid #000;
            padding: 0.5mm 1.5mm;
            text-align: center;
        }

        .table-label {
            font-size: 5.5pt;
            font-weight: bold;
        }

        /* Titre Modifié : Taille réduite et nom changé */
        .card-title {
            position: absolute;
            top: 12.5mm;
            width: 100%;
            text-align: center;
            color: #e8112d;
            font-size: 8.5pt;
            /* Taille réduite */
            font-weight: 900;
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
            font-size: 8.5pt;
            padding-bottom: 0.6mm;
            vertical-align: top;
        }

        .label {
            font-weight: bold;
            width: 16mm;
        }

        .sig-apprenant-box {
            position: absolute;
            bottom: 6mm;
            right: 5mm;
            border: 0.2mm solid #1e40af;
            padding: 0.5mm 3mm;
            font-size: 5.5pt;
            font-weight: bold;
            font-style: italic;
            color: #1e40af;
        }

        .educmaster-footer {
            position: absolute;
            bottom: 2.2mm;
            left: 3.5mm;
            font-size: 8.5pt;
            font-weight: bold;
        }

        /* --- ÉLÉMENTS DU VERSO --- */
        .verso-container {
            text-align: center;
            padding-top: 8mm;
        }

        .verso-school {
            font-size: 16pt;
            font-weight: 900;
            margin-bottom: 1mm;
        }

        .verso-year {
            font-size: 10pt;
            font-weight: bold;
        }

        .qr-code {
            width: 23mm;
            height: 23mm;
            margin-top: 2mm;
        }

        .verso-signature-area {
            position: absolute;
            bottom: 7mm;
            right: 10mm;
            text-align: right;
        }

        .dir-label {
            font-size: 9pt;
            font-weight: bold;
            text-decoration: underline;
        }

        .credit-vertical {
            position: absolute;
            left: 1mm;
            bottom: 15mm;
            transform: rotate(-90deg);
            transform-origin: left bottom;
            font-size: 5pt;
            font-weight: bold;
            color: #444;
        }

        .flag-bar {
            position: absolute;
            width: 100%;
            height: 1.5mm;
            display: table;
        }

        .flag-bar div {
            display: table-cell;
            width: 33.33%;
            height: 1%;
        }

        .green {
            background: #008751;
        }

        .yellow {
            background: #fcd116;
        }

        .red {
            background: #e8112d;
        }
    </style>
</head>

<body>

    @php
        $nomClasse = $eleve->classe->nom;
        $isSecondary = preg_match('/(6ème|5ème|4ème|3ème|2nde|1ère|Tle|Terminale)/i', $nomClasse);

        if ($isSecondary) {
            $logoPath = public_path('assets/card/MESTFP.png');
        } else {
            $logoPath = public_path('assets/card/memp.png');
        }
    @endphp

    <!-- FACE RECTO -->
    <div class="card page-break">
        <img src="{{ $logoPath }}" class="header-logo-img">

        <div class="school-top-info">
            <div class="school-top-name">{{ strtoupper($eleve->ecole->nom_ecole ?? 'ECOLE') }}</div>
            <div class="school-top-tel">Tél: {{ $eleve->ecole->telephone ?? '' }}</div>
        </div>

        <div class="table-box">
            <div class="table-label">Numero de table</div>
        </div>

        <!-- Titre mis à jour : Carte d'identité scolaire -->
        <div class="card-title">CARTE D'IDENTITÉ SCOLAIRE : {{ $activeYear->nom ?? '2025-2026' }}</div>

        <img src="{{ public_path('storage/' . $eleve->photo) }}" class="photo">

        <div class="info-container">
            <table class="info-table">
                <tr>
                    <td class="label">Nom</td>
                    <td>: {{ strtoupper($eleve->nom) }}</td>
                </tr>
                <tr>
                    <td class="label">Prénoms</td>
                    <td>: {{ ucwords($eleve->prenom) }}</td>
                </tr>
                <tr>
                    <td class="label">Né(e) le</td>
                    <td>: {{ $eleve->date_naissance?->format('d/m/Y') }} à {{ $eleve->lieu_naissance }}</td>
                </tr>
                <tr>
                    <td class="label">Classe</td>
                    <td>: {{ $eleve->classe->nom }}</td>
                </tr>
                <tr>
                    <td class="label">Adresse</td>
                    <td>: {{ $eleve->telephone_tuteur }}</td>
                </tr>
            </table>
        </div>

        <div class="sig-apprenant-box">Signature de l'apprenant</div>

        <div class="educmaster-footer">N° EducMaster: {{ $eleve->matricule_edumaster }}</div>

        <div class="flag-bar" style="bottom: 0;">
            <div class="green"></div>
            <div class="yellow"></div>
            <div class="red"></div>
        </div>
    </div>

    {{-- <!-- FACE VERSO -->
    <div class="card">
        <div class="flag-bar" style="top: 0;">
            <div class="green"></div><div class="yellow"></div><div class="red"></div>
        </div>

        <div class="verso-container">
            <div class="credit-vertical">Réal: DONAMI-CHRIST TEL: 97 22 48 87</div>

            <h2 class="verso-school">{{ strtoupper($eleve->ecole->nom_ecole ?? 'ECOLE') }}</h2>
            <div class="verso-year">ANNEE SCOLAIRE {{ $activeYear->nom ?? '' }}</div>

            <img src="{{ public_path('storage/'.$eleve->qr_code) }}" class="qr-code">

            <div class="verso-signature-area">
                <span class="dir-label">La Directrice</span>
                <div style="height: 10mm;"></div>
            </div>
        </div>

        <div class="flag-bar" style="bottom: 0;">
            <div class="green"></div><div class="yellow"></div><div class="red"></div>
        </div>
    </div> --}}

</body>

</html>
