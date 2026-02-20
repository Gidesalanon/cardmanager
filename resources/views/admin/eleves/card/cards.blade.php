<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        /* --- FIX NAVIGATION (L'ordre des éléments) --- */
        .static-view-container {
            padding: 40px;
            max-width: 1100px;
            margin: 0 auto;
        }

        .page-title-box {
            margin-bottom: 40px;
            border-left: 6px solid #e8112d;
            padding-left: 20px;
        }

        .page-title-box h1 {
            font-weight: 900;
            font-size: 26px;
            color: #1e293b;
            margin: 0;
        }

        .cards-display-zone {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .card-bundle {
            display: flex;
            flex-wrap: wrap;
            gap: 40px;
            justify-content: center;
        }

        /* --- STYLE CARTE (Dimensions ISO-CR80) --- */
        .memp-card {
            width: 480px;
            height: 300px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
            border: 1px solid #ccc;
        }

        /* Header Recto */
        .card-header {
            display: flex;
            padding: 12px 15px 5px;
        }

        .logo-benin {
            width: 42px;
            height: auto;
        }

        .memp-center {
            flex: 1;
            text-align: center;
        }

        .memp-center h1 {
            font-size: 10px;
            margin: 0;
            font-weight: 900;
            color: #222;
            line-height: 1.2;
        }

        .benin-underline {
            margin-top: 2px;
            border-top: 2px solid #e8112d;
            display: inline-block;
            padding: 0 8px;
        }

        .rep-txt {
            font-size: 8px;
            font-weight: bold;
        }

        .school-top-right {
            text-align: right;
            font-size: 8px;
            width: 110px;
            font-weight: bold;
        }

        /* Table Num */
        .table-box-num {
            position: absolute;
            top: 55px;
            right: 15px;
            border: 1px solid #000;
            padding: 3px 8px;
            text-align: center;
        }

        .t-label {
            font-size: 8px;
            font-weight: bold;
        }

        .t-dots {
            font-size: 8px;
        }

        .carte-titre-rouge {
            text-align: center;
            color: #e8112d;
            font-size: 19px;
            font-weight: 900;
            margin: 10px 0;
        }

        /* Body info */
        .card-main-body {
            display: flex;
            padding: 0 20px;
            gap: 15px;
        }

        .photo-area {
            width: 105px;
            height: 130px;
            border: 2px solid #000;
            overflow: hidden;
        }

        .photo-area img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .data-area {
            flex: 1;
            margin-top: 5px;
        }

        .d-row {
            display: flex;
            margin-bottom: 4px;
            font-size: 14.5px;
            color: #000;
        }

        .lbl {
            font-weight: bold;
            width: 70px;
        }

        /* Signatures / Cachets */
        .sig-section {
            position: absolute;
            bottom: 55px;
            right: 110px;
            text-align: center;
        }

        .sig-label {
            font-size: 10px;
            font-weight: bold;
            font-style: italic;
            color: #1e40af;
            border: 1px solid #1e40af;
            padding: 1px 6px;
        }

        .sig-img {
            width: 50px;
            opacity: 0.5;
            filter: hue-rotate(200deg);
        }

        .official-stamp-recto {
            position: absolute;
            bottom: 35px;
            right: 15px;
            width: 80px;
            height: 80px;
        }

        .stamp-r {
            width: 75px;
            opacity: 0.7;
        }

        .sign-r {
            width: 65px;
            position: absolute;
            left: 5px;
            top: 20px;
            filter: hue-rotate(200deg);
        }

        .educmaster-id {
            position: absolute;
            bottom: 12px;
            left: 20px;
            font-size: 13px;
            font-weight: 900;
        }

        /* VERSO */
        .verso-inner {
            text-align: center;
            padding: 20px;
            position: relative;
            height: 100%;
        }

        .credit-vertical {
            position: absolute;
            left: -85px;
            bottom: 120px;
            transform: rotate(-90deg);
            font-size: 8px;
            color: #555;
            width: 250px;
            text-align: left;
            font-weight: bold;
        }

        .verso-school-name {
            font-size: 24px;
            font-weight: 900;
            margin: 20px 0 5px;
            color: #222;
        }

        .verso-year {
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #444;
        }

        .qr-code-box img {
            width: 85px;
            border: 1px solid #eee;
            padding: 5px;
        }

        .director-zone {
            text-align: right;
            padding-right: 60px;
            margin-top: 10px;
            position: relative;
        }

        .dir-lbl {
            font-size: 13px;
            font-weight: bold;
            text-decoration: underline;
            color: #333;
        }

        .verso-official {
            position: relative;
            height: 80px;
            margin-top: -10px;
        }

        .v-stamp {
            width: 85px;
            opacity: 0.8;
            position: absolute;
            right: -20px;
            top: 0;
        }

        .v-sign {
            width: 90px;
            position: absolute;
            right: -10px;
            top: 15px;
            filter: hue-rotate(200deg);
        }

        /* BANDE DRAPEAU */
        .benin-stripe {
            position: absolute;
            bottom: 0;
            width: 100%;
            height: 7px;
            display: flex;
        }

        .benin-stripe div {
            flex: 1;
        }

        .g {
            background: #008751;
        }

        .y {
            background: #fcd116;
        }

        .r {
            background: #e8112d;
        }

        /* MODE SOMBRE */
        [data-theme='carbon'] .page-title-box h1 {
            color: white;
        }

        [data-theme='carbon'] .memp-card {
            border-color: #444;
        }
    </style>
</head>

<body>

    @php
        $classeNom = strtolower($eleve->classe->nom ?? '');
        $primaire = ['ci', 'cp', 'ce1', 'ce2', 'cm1', 'cm2'];
        $isPrimaire = false;

        foreach ($primaire as $p) {
            if (str_contains($classeNom, $p)) {
                $isPrimaire = true;
                break;
            }
        }

        $ministereLogo = $isPrimaire ? public_path('assets/card/memp.png') : public_path('assets/card/MESTFP.png');
    @endphp

    <!-- ================= RECTO ================= -->
    <div class="static-view-container">
        {{-- ZONE D'AFFICHAGE DES CARTES (STATIQUE) --}}
        <div class="cards-display-zone">

            <div class="card-bundle">
                {{-- FACE RECTO --}}
                <div class="memp-card recto">
                    <div class="card-header">
                        <img src="{{ $ministereLogo }}" class="logo-benin">
                        <div class="school-top-right">
                            <strong>{{ strtoupper($eleve->ecole->nom_ecole ?? '') }}</strong><br>
                            <div class="t-dots">{{ $eleve->numero_table }}</div>
                        </div>
                    </div>
                    <div class="table-box-num">
                        <div class="t-label">Numero de table</div>
                        <div class="t-dots">..............................</div>
                    </div>
                    <div class="carte-titre-rouge">
                        CARTE SCOLAIRE : {{ $activeYear->label ?? '' }}
                    </div>
                    <div class="card-main-body">
                        <div class="photo-area">
                            <img src="{{ public_path('storage/' . $eleve->photo) }}">
                        </div>
                        <div class="d-row">
                            <span class="lbl">Nom</span>
                            <span class="val">: {{ strtoupper($eleve->nom) }}</span>
                        </div>

                        <div class="d-row">
                            <span class="lbl">Prénoms</span>
                            <span class="val">: {{ $eleve->prenom }}</span>
                        </div>

                        <div class="d-row">
                            <span class="lbl">Né(e) le</span>
                            <span class="val">:
                                {{ $eleve->date_naissance?->format('d/m/Y') }}
                                à {{ $eleve->lieu_naissance }}
                            </span>
                        </div>

                        <div class="d-row">
                            <span class="lbl">Classe</span>
                            <span class="val">: {{ $eleve->classe->nom }}</span>
                        </div>

                        <div class="d-row">
                            <span class="lbl">Adresse</span>
                            <span class="val">: {{ $eleve->telephone_tuteur }}</span>
                        </div>
                    </div>
                    <div class="sig-section">
                        <div class="sig-label">Signature de l'apprenant</div>
                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/c9/Signature_of_Barack_Obama.svg/1200px-Signature_of_Barack_Obama.svg.png"
                            class="sig-img">
                    </div>
                    <div class="official-stamp-recto">
                        <img src="https://i.ibb.co/L6V2M6s/cachet-rond-rouge.png" class="stamp-r">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/f/f0/Signature_of_John_Hancock.svg/1200px-Signature_of_John_Hancock.svg.png"
                            class="sign-r">
                    </div>
                    <div class="educmaster-id">
                        N° EducMaster: {{ $eleve->matricule_edumaster }}
                    </div>
                    <div class="benin-stripe">
                        <div class="g"></div>
                        <div class="y"></div>
                        <div class="r"></div>
                    </div>
                </div>
                {{-- FACE VERSO --}}
                <div class="memp-card verso">
                    <div class="verso-inner">
                        <div class="credit-vertical">Réal: DONAMI CHRIST TEL: 97 22 48 87</div>

                        <h2 class="verso-school-name">
                            {{ strtoupper($eleve->ecole->nom_ecole ?? '') }}
                        </h2>
                        <p class="verso-year">
                            ANNEE SCOLAIRE {{ $activeYear->label ?? '' }}
                        </p>
                        <div class="qr-code-box">
                            <img src="{{ public_path('storage/' . $eleve->qr_code) }}">
                        </div>
                        <div class="director-zone">
                            <span class="dir-lbl">La Directrice</span>
                            <div class="verso-official">
                                <img src="https://i.ibb.co/L6V2M6s/cachet-rond-rouge.png" class="v-stamp">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/f/f0/Signature_of_John_Hancock.svg/1200px-Signature_of_John_Hancock.svg.png"
                                    class="v-sign">
                            </div>
                        </div>
                    </div>
                    <div class="benin-stripe">
                        <div class="g"></div>
                        <div class="y"></div>
                        <div class="r"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>
