<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Aperçu Carte Scolaire - Rendu Statique</title>
    <style>
        body {
            background-color: #f0f2f5;
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 40px;
            padding: 50px 0;
            margin: 0;
        }

        /* Dimensions standards Carte ID (85.6mm x 54mm convertis) */
        .card {
            width: 450px;
            height: 280px;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            position: relative;
            overflow: hidden;
            border: 1px solid #ddd;
        }

        /* --- RECTO --- */
        .header-recto {
            display: flex;
            justify-content: space-between;
            padding: 10px 15px;
            align-items: flex-start;
        }

        .memp-info {
            text-align: center;
            flex: 1;
        }

        .memp-info h2 {
            font-size: 9px;
            margin: 0;
            color: #222;
        }

        .republique {
            font-size: 8px;
            font-weight: bold;
            display: inline-block;
            border-top: 1.5px solid #e8112d;
            padding-top: 2px;
            margin-top: 2px;
        }

        .school-info {
            text-align: right;
            font-size: 9px;
            line-height: 1.2;
        }

        .table-box {
            position: absolute;
            top: 45px;
            right: 15px;
            border: 1px solid #000;
            padding: 4px 8px;
            font-size: 9px;
            font-weight: bold;
        }

        .main-title {
            text-align: center;
            color: #e8112d;
            font-size: 16px;
            font-weight: 800;
            margin: 5px 0;
            letter-spacing: 0.5px;
        }

        .content {
            display: flex;
            padding: 0 20px;
            gap: 15px;
        }

        .photo {
            width: 90px;
            height: 110px;
            border: 1.5px solid #555;
            background-image: url('https://i.imgur.com/8K0A1Zk.jpeg'); /* Image exemple de l'enfant */
            background-size: cover;
            background-position: center;
        }

        .data-table {
            font-size: 13px;
            border-collapse: collapse;
            flex: 1;
        }

        .data-table td {
            padding: 3px 0;
            vertical-align: top;
        }

        .label {
            font-weight: bold;
            width: 80px;
        }

        .signature-apprenant {
            position: absolute;
            bottom: 40px;
            right: 20px;
            border: 1px solid #4a90e2;
            padding: 2px 10px;
            font-size: 10px;
            color: #1a4a8e;
            font-style: italic;
        }

        .educmaster-num {
            position: absolute;
            bottom: 12px;
            left: 20px;
            font-size: 11px;
            font-weight: bold;
        }

        /* Bande tricolore Bénin en bas */
        .footer-tricolore {
            position: absolute;
            bottom: 0;
            width: 100%;
            height: 6px;
            display: flex;
        }
        .green { background: #008751; flex: 1; }
        .yellow { background: #fcd116; flex: 1; }
        .red { background: #e8112d; flex: 1; }

        /* --- VERSO --- */
        .verso-content {
            text-align: center;
            padding: 20px;@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Aperçu des Cartes Scolaires</h1>
        <p class="text-muted">Année : {{ $activeYear->nom ?? '2025-2026' }}</p>
    </div>

    <div class="cards-gallery">
        @foreach($eleves as $student)
            <div class="student-card-set mb-5">
                <h4 class="student-name">{{ $student->nom }} {{ $student->prenom }}</h4>
                
                <div class="cards-row">
                    <!-- RECTO -->
                    <div class="id-card recto">
                        <div class="card-header-top">
                            <div class="memp-header">
                                <p class="memp-title">MINISTÈRE DES ENSEIGNEMENTS MATERNEL ET PRIMAIRE (MEMP)</p>
                                <span class="benin-rep">RÉPUBLIQUE DU BÉNIN</span>
                            </div>
                            <div class="school-details">
                                <strong>{{ strtoupper(auth()->user()->ecole->nom_ecole) }}</strong><br>
                                Tél: {{ auth()->user()->ecole->telephone }}
                            </div>
                        </div>

                        <div class="table-tag">Numero de table</div>
                        <div class="card-main-title">CARTE SCOLAIRE : {{ $activeYear->nom }}</div>

                        <div class="card-body-content">
                            <div class="student-photo">
                                <img src="{{ asset('storage/' . $student->photo) }}" alt="">
                            </div>
                            <div class="student-info-list">
                                <p><strong>Nom</strong> : {{ strtoupper($student->nom) }}</p>
                                <p><strong>Prénoms</strong> : {{ ucwords($student->prenom) }}</p>
                                <p><strong>Né(e) le</strong> : {{ \Carbon\Carbon::parse($student->date_naissance)->format('d/m/Y') }} à {{ $student->lieu_naissance }}</p>
                                <p><strong>Classe</strong> : {{ $student->classe->nom }}</p>
                                <p><strong>Adresse</strong> : {{ $student->telephone_tuteur }}</p>
                            </div>
                        </div>

                        <div class="signature-tag">Signature de l'apprenant</div>
                        <div class="educmaster-tag">N° EducMaster: {{ $student->matricule_edumaster }}</div>
                        <div class="flag-footer">
                            <div class="g"></div><div class="y"></div><div class="r"></div>
                        </div>
                    </div>

                    <!-- VERSO -->
                    <div class="id-card verso">
                        <div class="verso-inner">
                            <h2 class="verso-school">{{ strtoupper(auth()->user()->ecole->nom_ecole) }}</h2>
                            <p class="verso-year">ANNEE SCOLAIRE {{ $activeYear->nom }}</p>
                            
                            <div class="qr-box">
                                <img src="{{ asset('storage/' . $student->qr_code) }}" width="70">
                            </div>

                            <div class="signature-dir">
                                <span class="dir-title">La Directrice</span>
                            </div>

                            <div class="credit-vertical">
                                Réal: {{ config('app.name') }} TEL: 97 22 48 87
                            </div>
                        </div>
                        <div class="flag-footer">
                            <div class="g"></div><div class="y"></div><div class="r"></div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<style>
    .student-card-set { border-bottom: 2px dashed #ccc; padding-bottom: 30px; }
    .student-name { color: #3b82f6; margin-bottom: 15px; font-weight: bold; }
    .cards-row { display: flex; gap: 30px; flex-wrap: wrap; justify-content: center; }

    /* Style commun des cartes */
    .id-card {
        width: 450px;
        height: 280px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        position: relative;
        overflow: hidden;
        border: 1px solid #ddd;
    }

    /* Styles spécifiques au Recto (Copie exacte de ton image) */
    .header-recto { display: flex; justify-content: space-between; padding: 10px; }
    .memp-title { font-size: 8px; font-weight: bold; margin: 0; text-align: center; }
    .benin-rep { font-size: 7px; border-top: 1px solid red; display: block; text-align: center; margin-top: 2px; }
    .school-details { position: absolute; top: 8px; right: 15px; font-size: 8px; text-align: right; }
    .table-tag { position: absolute; top: 40px; right: 15px; border: 1px solid #000; font-size: 9px; padding: 2px 5px; font-weight: bold; }
    .card-main-title { text-align: center; color: red; font-size: 16px; font-weight: 800; margin: 10px 0; }
    
    .card-body-content { display: flex; padding: 0 15px; gap: 15px; }
    .student-photo { width: 90px; height: 110px; border: 1px solid #000; }
    .student-photo img { width: 100%; height: 100%; object-fit: cover; }
    .student-info-list { font-size: 13px; flex: 1; }
    .student-info-list p { margin: 4px 0; }

    .signature-tag { position: absolute; bottom: 40px; right: 20px; border: 1px solid #ccc; font-size: 10px; font-style: italic; padding: 2px 10px; }
    .educmaster-tag { position: absolute; bottom: 12px; left: 15px; font-size: 10px; font-weight: bold; }

    /* Styles spécifiques au Verso */
    .verso-inner { text-align: center; padding-top: 20px; }
    .verso-school { font-size: 20px; font-weight: 900; margin-bottom: 5px; }
    .verso-year { font-size: 12px; font-weight: bold; }
    .qr-box { margin: 15px auto; }
    .signature-dir { text-align: right; padding-right: 40px; margin-top: 10px; }
    .dir-title { font-size: 12px; text-decoration: underline; font-weight: bold; }
    .credit-vertical { position: absolute; left: -85px; bottom: 110px; transform: rotate(-90deg); font-size: 8px; color: #777; width: 250px; text-align: left; }

    /* Pied de page tricolore */
    .flag-footer { position: absolute; bottom: 0; width: 100%; height: 6px; display: flex; }
    .g { background: #008751; flex: 1; }
    .y { background: #fcd116; flex: 1; }
    .r { background: #e8112d; flex: 1; }
</style>
@endsection
        }

        .verso-title {
            font-size: 22px;
            font-weight: 900;
            color: #333;
            margin: 10px 0 5px 0;
        }

        .verso-year {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .qr-placeholder {
            width: 80px;
            height: 80px;
            margin: 0 auto;
            background: #eee;
            border: 1px solid #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            color: #666;
        }

        .directrice-area {
            text-align: right;
            padding-right: 40px;
            margin-top: 10px;
        }

        .directrice-label {
            font-size: 11px;
            text-decoration: underline;
            font-weight: bold;
        }

        .vertical-credit {
            position: absolute;
            left: -85px;
            bottom: 120px;
            transform: rotate(-90deg);
            font-size: 8px;
            color: #555;
            width: 250px;
            text-align: left;
        }
    </style>
</head>
<body>

    <!-- CARTE RECTO -->
    <div class="card">
        <div class="header-recto">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/eb/Coat_of_arms_of_Benin.svg/1200px-Coat_of_arms_of_Benin.svg.png" width="30" alt="Logo">
            <div class="memp-info">
                <h2>MINISTÈRE DES ENSEIGNEMENTS MATERNEL ET PRIMAIRE (MEMP)</h2>
                <div class="republique">RÉPUBLIQUE DU BÉNIN</div>
            </div>
            <div class="school-info">
                <strong>CPEG PENIEL</strong><br>
                Tél: +229 01 96 84 23 26<br>
                01 43 23 40 11
            </div>
        </div>

        <div class="table-box">Numero de table</div>

        <div class="main-title">CARTE SCOLAIRE : 2025 - 2026</div>

        <div class="content">
            <div class="photo"></div>
            <table class="data-table">
                <tr><td class="label">Nom</td><td>: HOUNGUE SOSSOU</td></tr>
                <tr><td class="label">Prénoms</td><td>: Sèdami Odiane Quintilla</td></tr>
                <tr><td class="label">Né(e) le</td><td>: 24/03/2016 à Abomey-Calavi</td></tr>
                <tr><td class="label">Classe</td><td>: CM2</td></tr>
                <tr><td class="label">Adresse</td><td>: 01 96 84 23 26</td></tr>
            </table>
        </div>

        <div class="signature-apprenant">Signature de l'apprenant</div>

        <div class="educmaster-num">N° EducMaster: 2160324009013</div>

        <div class="footer-tricolore">
            <div class="green"></div>
            <div class="yellow"></div>
            <div class="red"></div>
        </div>
    </div>

    <!-- CARTE VERSO -->
    <div class="card">
        <div class="verso-content">
            <div class="verso-title">CEG 2 KETOU</div>
            <div class="verso-year">ANNEE SCOLAIRE 2024-2025</div>
            
            <div class="qr-placeholder">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=2160324009013" alt="QR Code">
            </div>

            <div class="directrice-area">
                <span class="directrice-label">La Directrice</span><br>
                <!-- Simulation tampon/signature -->
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/c9/Signature_of_Barack_Obama.svg/1200px-Signature_of_Barack_Obama.svg.png" width="80" style="opacity: 0.6; margin-top: 5px;">
            </div>

            <div class="vertical-credit">
                Réal: DONAMI CHRIST TEL: 97 22 48 87
            </div>
        </div>
        
        <div class="footer-tricolore">
            <div class="green"></div>
            <div class="yellow"></div>
            <div class="red"></div>
        </div>
    </div>

</body>
</html>