<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { size: 85.6mm 54mm; margin: 0; }
        body { margin: 0; padding: 0; width: 85.6mm; height: 54mm; font-family: 'Arial', sans-serif; background: white; }
        
        .card { width: 85.6mm; height: 54mm; position: relative; overflow: hidden; page-break-after: always; }

        /* Bande tricolore réutilisable */
        .benin-bande { display: flex; height: 0.7mm; width: 25mm; position: absolute; }
        .v { background: #008751; flex: 1; }
        .j { background: #FCD116; flex: 1; }
        .r { background: #CE1126; flex: 1; }

        /* --- EN-TÊTE --- */
        .logo-img {
            position: absolute; top: 1mm; left: 1mm;
            width: 52mm; height: 11mm; /* Hauteur fixe pour stabiliser le reste */
            object-fit: contain; /* Évite de déformer le logo */
        }

        .school-top {
            position: absolute; top: 1mm; right: 2mm;
            width: 30mm; text-align: right;
            font-size: 4.8pt; font-weight: bold; line-height: 1.1;
        }
        .b-school { top: 6mm; right: 2mm; width: 25mm; }

        .table-area {
            position: absolute; top: 7mm; right: 2mm;
            width: 20mm; text-align: center;
        }
        .table-box { width: 100%; height: 3.2mm; border: 0.4pt solid #000; }
        .table-txt { font-size: 4.2pt; font-weight: bold; margin-top: 0.3mm; }

        /* --- TITRE (REPOSITIONNÉ POUR NE PLUS CHEVAUCHER) --- */
        .card-title {
            position: absolute; 
            top: 14mm; /* Descendu légèrement pour laisser de la place au logo MEMP */
            width: 100%;
            text-align: center; 
            color: #CE1126;
            font-size: 8.2pt; /* Taille parfaite pour une ligne */
            font-weight: bold; 
            text-transform: uppercase;
            letter-spacing: -0.1px;
        }

        /* --- CORPS --- */
        .student-photo {
            position: absolute; top: 20mm; left: 3mm;
            width: 21mm; height: 26mm;
            border: 0.3pt solid #000; object-fit: cover;
        }

        .student-info {
            position: absolute; top: 20.5mm; left: 26mm;
            font-size: 8.2pt; line-height: 1.35;
        }
        .student-info b { text-transform: uppercase; }

        /* --- SIGNATURE --- */
        .sig-container { position: absolute; bottom: 2.5mm; right: 4mm; width: 28mm; }
        .sig-label { font-size: 6.2pt; font-weight: bold; text-align: left; display: block; margin-bottom: 2mm; }
        .b-sig { top: 3.2mm; left: 0; width: 22mm; } /* Bande sous le texte signature */
        .sig-box { width: 100%; height: 5.5mm; border: 0.4pt solid #000; margin-top: 1.2mm; }

        .educmaster-footer {
            position: absolute; bottom: 1.5mm; left: 3mm;
            font-size: 7.5pt; font-weight: bold;
        }

        /* --- VERSO --- */
        .b-verso { top: 3.5mm; left: 25.3mm; width: 35mm; height: 1mm; }
        .v-school { position: absolute; top: 5.5mm; width: 100%; text-align: center; font-size: 14pt; font-weight: bold; }
        .v-year { position: absolute; top: 12.5mm; width: 100%; text-align: center; font-size: 10pt; font-weight: bold; }
        .qr-zone { position: absolute; top: 23mm; left: 6mm; width: 18mm; height: 18mm; }
        .dir-title { position: absolute; top: 22mm; width: 100%; text-align: center; font-size: 11pt; font-weight: bold; text-decoration: underline; }
        .dir-name { position: absolute; bottom: 5mm; width: 100%; text-align: center; font-size: 11pt; font-weight: bold; }
        .v-footer { position: absolute; bottom: 0.5mm; width: 100%; text-align: center; font-size: 5.5pt; color: #777; border-top: 0.1pt solid #ccc; }
    </style>
</head>
<body>

    @php
        $classeNom = strtoupper($eleve->classe->nom ?? '');
        $niveauNom = strtolower($eleve->classe->niveau ?? '');
        $classesPrimaires = ['CI', 'CP', 'CE1', 'CE2', 'CM1', 'CM2'];
        
        $isPrimaire = in_array($classeNom, $classesPrimaires) || str_contains($niveauNom, 'prim');
        $logoFileName = $isPrimaire ? 'memp.png' : 'MESTFP.png';
        $fullLogoPath = public_path('assets/card/' . $logoFileName);
        
        $qrPath = public_path('storage/eleves/qrcodes/' . $eleve->qr_code);
    @endphp

    <div class="card">
        <img src="{{ $fullLogoPath }}" class="logo-img">
        
        <div class="school-top">
            {{ strtoupper($eleve->ecole->nom_ecole ?? 'CPEG PENIEL') }}<br>
            Tél: {{ $eleve->ecole->telephone ?? '01 43 23 40 11' }}
        </div>
        <div class="benin-bande b-school"><div class="v"></div><div class="j"></div><div class="r"></div></div>

        <div class="table-area">
            <div class="table-box"></div>
            <div class="table-txt">Numéro de table</div>
        </div>

        <div class="card-title">CARTE D'IDENTITÉ SCOLAIRE : {{ $activeYear->label ?? '2025-2026' }}</div>

        <img src="{{ public_path('storage/'.$eleve->photo) }}" class="student-photo">

        <div class="student-info">
            Nom &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <b>{{ $eleve->nom }}</b><br>
            Prénoms &nbsp;&nbsp;&nbsp;&nbsp;: {{ $eleve->prenom }}<br>
            Né(e) le &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $eleve->date_naissance?->format('d/m/Y') }} à {{ $eleve->lieu_naissance }}<br>
            Classe &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <b>{{ $eleve->classe->nom }}</b><br>
            Adresse &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $eleve->telephone_tuteur }}
        </div>

        <div class="sig-container">
            <span class="sig-label">Signature de l'apprenant</span>
            <div class="benin-bande b-sig"><div class="v"></div><div class="j"></div><div class="r"></div></div>
            <div class="sig-box"></div>
        </div>

        <div class="educmaster-footer">N° EducMaster : {{ $eleve->matricule_edumaster }}</div>
    </div>

    <div class="card">
        <div class="benin-bande b-verso"><div class="v"></div><div class="j"></div><div class="r"></div></div>
        <div class="v-school">{{ strtoupper($eleve->ecole->nom_ecole ?? 'ETABLISSEMENT') }}</div>
        <div class="v-year">ANNEE SCOLAIRE {{ $activeYear->label ?? '2025-2026' }}</div>
        <img src="{{ $qrPath }}" class="qr-zone">
        <div class="dir-title">La Directrice</div>
        <div class="dir-name">IDOHOU AROKOHO Fatima</div>
        <div class="v-footer">Réal: DONAMI-CHRIST TEL: 97 22 48 87</div>
    </div>
</body>
</html>