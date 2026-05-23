<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        .card { background: #fff; border-radius: 10px; max-width: 600px; margin: 0 auto; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #0a0a0a, #1a1000); padding: 30px; text-align: center; }
        .header h1 { color: #f0d080; font-size: 1.2rem; margin: 0 0 6px; }
        .header p { color: #8a9ab0; font-size: 0.82rem; margin: 0; }
        .body { padding: 30px; }
        .field { margin-bottom: 16px; }
        .field label {
            display: block; font-size: 0.75rem; font-weight: 700;
            color: #64748b; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px;
        }
        .field p {
            font-size: 0.93rem; color: #0f172a; background: #faf8f4;
            padding: 10px 15px; border-radius: 8px;
            border-left: 3px solid #c9a84c; margin: 0;
        }
        .footer {
            background: #faf8f4; padding: 20px 30px;
            text-align: center; font-size: 0.78rem; color: #94a3b8;
            border-top: 1px solid #e8e0d0;
        }
        .footer strong { color: #c9a84c; }
        .tricolor { height: 4px; display: flex; }
        .tricolor .g { flex: 1; background: #008751; }
        .tricolor .y { flex: 1; background: #fcd116; }
        .tricolor .r { flex: 1; background: #e8112d; }
        .reply-hint {
            background: rgba(201,168,76,0.08); border: 1px solid rgba(201,168,76,0.3);
            border-radius: 8px; padding: 12px 16px; margin-bottom: 20px;
            font-size: 0.82rem; color: #92700a;
        }
        .reply-hint strong { color: #c9a84c; }
    </style>
</head>
<body>
<div class="card">
    <div class="tricolor"><span class="g"></span><span class="y"></span><span class="r"></span></div>

    <div class="header">
        <h1>📩 Nouveau message de contact</h1>
        <p>Reçu depuis le formulaire du site DONAMI-CHRIST</p>
    </div>

    <div class="body">

        @if(!empty($emailClient))
        <div class="reply-hint">
            💡 Pour répondre directement à ce client, cliquez sur <strong>Répondre</strong> dans votre messagerie.
        </div>
        @endif

        <div class="field">
            <label>Nom du client</label>
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
            <p style="white-space: pre-line; line-height: 1.7;">{{ $messageClient }}</p>
        </div>

    </div>

    <div class="footer">
        <p><strong>DONAMI-CHRIST</strong></p>
        <p>Abomey-Calavi, Bidossessi, République du Bénin</p>
        <p>+229 01 66 44 92 32 &nbsp;/&nbsp; +229 01 97 22 48 87</p>
        <p style="margin-top:8px; font-size:0.72rem; color:#b0a090;">
            Ce message a été envoyé automatiquement depuis le formulaire de contact.
        </p>
    </div>

    <div class="tricolor"><span class="g"></span><span class="y"></span><span class="r"></span></div>
</div>
</body>
</html>