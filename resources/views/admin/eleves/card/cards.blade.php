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
            margin-top: 2px
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
            min-height: 7px
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
        /* Titre Modifié : Taille réduite et nom changé */
        .card-title {
            position: absolute;
            top: 12.5mm;
            width: 100%;
            text-align: center;
            color: #e8112d;
            font-size: 7pt;
            /* Taille réduite */
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
            font-size: 10px;
            padding-bottom: 0.6mm;
            vertical-align: top;
        }
        .label { font-weight: bold; width: 11mm; height: 14px; }
        .label-sign { font-weight: bold; width: auto; font-size: 9px }
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
            font-size: 7pt;
            font-weight: bold;
        }

        /* --- ÉLÉMENTS DU VERSO --- */
        .verso-photo {
            width: 85.6mm;
            height: 54mm;
            position: relative;
            overflow: hidden;
            background-color: white;
            /* padding: 3px; */
        }

        /* Nom école */
        .school-name {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            color: #020202;
            letter-spacing: 1px;
            padding-top: 10px;
        }

        /* Année */
        .school-year {
            text-align: center;
            font-size: 8px;
            margin-top: 3px;
            color: #000000;
        }

        /* Zone centrale */
        .center-zone {
            text-align: center;
            /* Centre le contenu inline */
            width: 100%;
            margin-top: 20px;
        }

        .stamp-signature,
        .stamp-cachet {
            display: inline-block;
            vertical-align: middle;
            margin: 0 20px;        /*Espacement horizontal*/
        }

        .center-zone img {
            max-width: 100px;
            height: auto;
        }

        /* Label directrice */
        .director-label {
            font-weight: bold;
            margin-top: 5px;
            color: #444;
        }

        /* Signature */
        .signature-img {
            width: 150px;
            margin-top: -20px;
        }

        /* Nom directrice */
        .director-name {
            text-align: center;
            font-weight: 700;
            font-size: 12px;
            margin-top: 5px;
            color: #050505;
        }

        /* Footer */
        .footer-real {
            position: relative;
            text-align: center;
            font-size: 10px;
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
            <div class="table-label"></div>
        </div>
        <!-- Titre mis à jour : Carte d'identité scolaire et QR -->
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
            <div class="sign">
                <div class="label-sign">Signature de l'apprenant</div>
                <div class="sign-box">
                    
                </div>
            </div>
            
        </div>

        <div class="educmaster-footer">N° EducMaster: {{ $eleve->matricule_edumaster }}</div>
        <div class="flag-bar" style="bottom: 0;">
            <div class="green"></div>
            <div class="yellow"></div>
            <div class="red"></div>
        </div>
    </div>
    {{-- ================= Verso ================= --}}
    <div class="memp-card verso-photo">
        <!-- Nom établissement -->
        <div class="school-name">
            {{ strtoupper($eleve->ecole->nom_ecole ?? '') }}
        </div>
        <!-- Année scolaire -->
        <div class="school-year">
            ANNEE SCOLAIRE {{ $activeYear->label ?? '' }}
        </div>
        <!-- Zone centrale -->
        <div class="center-zone">
            <!-- Cachet + signature -->
            <div class="stamp-signature">
                <img src="{{ public_path('storage/' . $eleve->ecole->directeur->signature) }}" alt="Signature">
            </div>

            <div class="stamp-cachet">
                <img src="{{ public_path('storage/' . $eleve->ecole->directeur->cachet) }}" alt="Cachet">
            </div>
        </div>
        <!-- Nom Directrice -->
        <div class="director-name">
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

        <!-- Réalisation -->
        <div class="footer-real">
            Réal: DONAMI-CHRIST - TEL: 97 22 48 87
        </div>

        <!-- Bande tricolore -->
        <div class="flag-bar" style="bottom: 0;">
            <div class="green"></div><div class="yellow"></div><div class="red"></div>
        </div>
    </div>

</div>
</body>

</html>
