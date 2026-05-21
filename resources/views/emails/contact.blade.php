<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        .card { background: #fff; border-radius: 10px; max-width: 600px; margin: 0 auto; overflow: hidden; }
        .header { background: linear-gradient(135deg, #0f172a, #1e3a5f); padding: 30px; text-align: center; }
        .header h1 { color: #fcd116; font-size: 1.3rem; margin: 0; }
        .header p { color: #94a3b8; font-size: 0.85rem; margin: 5px 0 0; }
        .body { padding: 30px; }
        .field { margin-bottom: 18px; }
        .field label { display: block; font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; }
        .field p { font-size: 0.95rem; color: #0f172a; background: #f8fafc; padding: 10px 15px; border-radius: 8px; border-left: 3px solid #1e40af; margin: 0; }
        .footer { background: #f8fafc; padding: 20px 30px; text-align: center; font-size: 0.8rem; color: #94a3b8; border-top: 1px solid #e2e8f0; }
        .tricolor { height: 4px; display: flex; }
        .tricolor .g { flex: 1; background: #008751; }
        .tricolor .y { flex: 1; background: #fcd116; }
        .tricolor .r { flex: 1; background: #e8112d; }
    </style>
</head>
<body>
<div class="card">
    <div class="tricolor"><span class="g"></span><span class="y"></span><span class="r"></span></div>
    <div class="header">
        <h1>📩 Nouveau message de contact</h1>
        <p>Reçu depuis le formulaire du site CardManager</p>
    </div>
    <div class="body">
        <div class="field">
            <label>Nom</label>
            <p>{{ $nomClient }}</p>
        </div>
        <div class="field">
            <label>Email</label>
            <p>{{ $emailClient ?: 'Non renseigné' }}</p>
        </div>
        <div class="field">
            <label>Téléphone</label>
            <p>{{ $telephone ?: 'Non renseigné' }}</p>
        </div>
        <div class="field">
            <label>Sujet</label>
            <p>{{ $sujet }}</p>
        </div>
        <div class="field">
            <label>Message</label>
            <p style="white-space: pre-line;">{{ $messageClient }}</p>
        </div>
    </div>
    <div class="footer">
        <p>DONAMI-CHRIST — Abomey-Calavi, Bidossessi, Bénin</p>
        <p>+229 01 66 44 92 32 / +229 01 97 22 48 87</p>
    </div>
    <div class="tricolor"><span class="g"></span><span class="y"></span><span class="r"></span></div>
</div>
</body>
</html>