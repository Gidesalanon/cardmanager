@extends('layouts.welcome')
@section('title', 'Contact')

@push('styles')
<style>
    :root {
        --gold:  #c9a84c;
        --gold2: #f0d080;
        --dark:  #0a0a0a;
    }

    @keyframes pulse-gold {
        0%, 100% { box-shadow: 0 0 0 0 rgba(201,168,76,0.4); }
        50% { box-shadow: 0 0 0 10px rgba(201,168,76,0); }
    }
    @keyframes iconFloat {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-5px); }
    }

    /* PAGE HERO */
    .page-hero {
        background: linear-gradient(135deg, rgba(0,0,0,0.85) 0%, rgba(26,16,0,0.75) 50%, rgba(0,0,0,0.85) 100%),
                    url('{{ asset('assets/web/images/11.jpg') }}');
        background-size: cover;
        background-position: center;
        padding: 90px 20px 70px; text-align: center;
        position: relative; overflow: hidden;
        border-bottom: 1px solid rgba(201,168,76,0.2);
    }
    .page-hero::before {
        content: ''; position: absolute; top: -150px; left: 50%; transform: translateX(-50%);
        width: 600px; height: 600px; border-radius: 50%;
        background: radial-gradient(circle, rgba(201,168,76,0.07) 0%, transparent 70%);
    }
    .page-hero-badge {
        display: inline-flex; align-items: center; gap: 8px;
        background: rgba(201,168,76,0.1); border: 1px solid rgba(201,168,76,0.3);
        color: #f0d080; padding: 6px 18px; border-radius: 100px;
        font-size: 0.78rem; font-weight: 700; letter-spacing: 2px;
        text-transform: uppercase; margin-bottom: 20px;
    }
    .page-hero h1 { font-size: 2.8rem; font-weight: 900; color: #fff; margin-bottom: 15px; }
    .page-hero h1 span { color: var(--gold2); }
    .page-hero p { color: #8a9ab0; font-size: 1rem; max-width: 580px; margin: 0 auto; line-height: 1.7; }
    .gold-line { width: 60px; height: 3px; background: linear-gradient(90deg, var(--gold), var(--gold2)); border-radius: 2px; margin: 15px auto; }

    /* CONTACT SECTION */
    .contact-section { padding: 75px 20px; background: #faf8f4; }
    .contact-inner {
        max-width: 1100px; margin: 0 auto;
        display: grid; grid-template-columns: 1fr 1.6fr;
        gap: 50px; align-items: start;
    }

    /* Infos */
    .contact-info-label {
        display: inline-block; color: var(--gold); font-size: 0.78rem;
        font-weight: 700; text-transform: uppercase; letter-spacing: 3px; margin-bottom: 12px;
    }
    .contact-info h2 { font-size: 1.8rem; font-weight: 800; color: var(--dark); margin-bottom: 10px; }
    .contact-info h2 span { color: var(--gold); }
    .contact-info > p { color: #64748b; font-size: 0.93rem; line-height: 1.7; margin-bottom: 30px; }

    .info-card {
        background: #fff; border-radius: 14px; padding: 18px 20px;
        border: 1px solid #ede8dc; margin-bottom: 14px;
        display: flex; align-items: flex-start; gap: 15px;
        transition: all 0.3s;
    }
    .info-card:hover { border-color: var(--gold); box-shadow: 0 6px 20px rgba(201,168,76,0.1); transform: translateX(4px); }
    .info-card-icon {
        width: 46px; height: 46px; flex-shrink: 0;
        background: linear-gradient(135deg, var(--gold), var(--gold2));
        border-radius: 12px; display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem; color: var(--dark);
        animation: pulse-gold 4s infinite;
    }
    .info-card:nth-child(2) .info-card-icon { animation-delay: 1s; }
    .info-card:nth-child(3) .info-card-icon { animation-delay: 2s; }
    .info-card:nth-child(4) .info-card-icon { animation-delay: 3s; }
    .info-card-text h4 { font-size: 0.88rem; font-weight: 700; color: var(--dark); margin-bottom: 4px; }
    .info-card-text p { font-size: 0.88rem; color: #64748b; line-height: 1.5; margin: 0; }
    .info-card-text a { color: var(--gold); text-decoration: none; font-weight: 600; }
    .info-card-text a:hover { color: var(--gold2); }

    /* Form */
    .contact-form-card {
        background: #fff; border-radius: 22px; padding: 40px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.07);
        border: 1px solid #ede8dc;
        position: relative; overflow: hidden;
    }
    .contact-form-card::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0;
        height: 4px; background: linear-gradient(90deg, var(--gold), var(--gold2));
    }

    .form-title {
        font-size: 1.3rem; font-weight: 800; color: var(--dark);
        margin-bottom: 25px; display: flex; align-items: center; gap: 10px;
    }
    .form-title i { color: var(--gold); }

    .alert-success {
        background: #fdf6e3; border: 1px solid #e8d89a; color: #92700a;
        padding: 14px 18px; border-radius: 10px; margin-bottom: 22px;
        font-size: 0.9rem; display: flex; align-items: center; gap: 10px;
    }
    .alert-error {
        background: #fef2f2; border: 1px solid #fecaca; color: #dc2626;
        padding: 14px 18px; border-radius: 10px; margin-bottom: 22px;
        font-size: 0.9rem; display: flex; align-items: center; gap: 10px;
    }

    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
    .form-group { margin-bottom: 18px; }
    .form-group label {
        display: block; font-size: 0.85rem; font-weight: 700;
        color: #374151; margin-bottom: 7px;
    }
    .form-group label .required { color: var(--gold); margin-left: 3px; }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%; padding: 12px 16px;
        border: 1.5px solid #e8e0d0; border-radius: 10px;
        font-size: 0.93rem; color: var(--dark);
        background: #faf8f4; transition: all 0.2s;
        outline: none; font-family: inherit;
    }
    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        border-color: var(--gold); background: #fff;
        box-shadow: 0 0 0 3px rgba(201,168,76,0.12);
    }
    .form-group textarea { resize: vertical; min-height: 130px; }
    .form-error { color: #dc2626; font-size: 0.8rem; margin-top: 4px; }

    .btn-submit {
        width: 100%; padding: 15px;
        background: linear-gradient(135deg, var(--gold), var(--gold2));
        color: var(--dark); border: none; border-radius: 12px;
        font-size: 1rem; font-weight: 700; cursor: pointer;
        display: flex; align-items: center; justify-content: center; gap: 10px;
        transition: all 0.3s; box-shadow: 0 4px 20px rgba(201,168,76,0.35);
    }
    .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(201,168,76,0.5); }
    .btn-submit i { font-size: 1rem; }

    /* Map placeholder */
    .map-section { background: #0a0a0a; padding: 60px 20px; }
    .map-inner { max-width: 1100px; margin: 0 auto; }
    .map-title { color: var(--gold2); font-size: 1.4rem; font-weight: 800; margin-bottom: 20px; text-align: center; }
    .map-placeholder {
        background: rgba(201,168,76,0.05);
        border: 1px solid rgba(201,168,76,0.2);
        border-radius: 16px; padding: 50px;
        text-align: center; color: #8a9ab0;
    }
    .map-placeholder i { font-size: 3rem; color: var(--gold); margin-bottom: 15px; display: block; animation: iconFloat 3s infinite; }
    .map-placeholder p { font-size: 0.95rem; }
    .map-placeholder strong { color: var(--gold2); }

    @media (max-width: 768px) {
        .contact-inner { grid-template-columns: 1fr; }
        .form-row { grid-template-columns: 1fr; }
        .page-hero h1 { font-size: 2rem; }
    }
</style>
@endpush

@section('content')

<div class="page-hero">
    <div class="page-hero-badge"><i class="fa-solid fa-envelope"></i> Parlons-nous</div>
    <h1><span>Contactez</span>-nous</h1>
    <div class="gold-line"></div>
    <p>Nous sommes disponibles pour répondre à toutes vos questions, établir un devis et vous accompagner dans vos projets.</p>
</div>

<section class="contact-section">
    <div class="contact-inner">

        {{-- Coordonnées --}}
        <div class="contact-info">
            <span class="contact-info-label">Nos coordonnées</span>
            <h2>Nous <span>trouver</span></h2>
            <div class="gold-line" style="margin: 0 0 20px;"></div>
            <p>N'hésitez pas à nous contacter pour un devis, une commande ou toute information complémentaire. Nous vous répondons rapidement.</p>

            <div class="info-card">
                <div class="info-card-icon"><i class="fa-solid fa-location-dot"></i></div>
                <div class="info-card-text">
                    <h4>Localisation</h4>
                    <p>Abomey-Calavi, Bidossessi<br>République du Bénin</p>
                </div>
            </div>

            <div class="info-card">
                <div class="info-card-icon"><i class="fa-solid fa-phone"></i></div>
                <div class="info-card-text">
                    <h4>Téléphone</h4>
                    <p>
                        <a href="tel:+22901664922 32">+229 01 66 44 92 32</a><br>
                        <a href="tel:+22901972248 87">+229 01 97 22 48 87</a>
                    </p>
                </div>
            </div>

            <div class="info-card">
                <div class="info-card-icon"><i class="fa-solid fa-clock"></i></div>
                <div class="info-card-text">
                    <h4>Horaires d'ouverture</h4>
                    <p>Lun – Sam : 08h00 – 18h00<br>Dimanche : Sur rendez-vous</p>
                </div>
            </div>

            <div class="info-card">
                <div class="info-card-icon"><i class="fa-brands fa-facebook"></i></div>
                <div class="info-card-text">
                    <h4>Réseaux sociaux</h4>
                    <p><a href="" target="_blank">donami sur Facebook</a></p>
                </div>
            </div>
        </div>

        {{-- Formulaire --}}
        <div class="contact-form-card">
            <div class="form-title">
                <i class="fa-solid fa-paper-plane"></i> Envoyez-nous un message
            </div>

            @if(session('success'))
                <div class="alert-success">
                    <i class="fa-solid fa-circle-check"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert-error">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('contact.send') }}">
                @csrf

                <div class="form-row">
                    <div class="form-group">
                        <label>Nom complet <span class="required">*</span></label>
                        <input type="text" name="nom" placeholder="Votre nom" required value="{{ old('nom') }}">
                        @error('nom')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label>Téléphone</label>
                        <input type="text" name="telephone" placeholder="+229 00 00 00 00" value="{{ old('telephone') }}">
                    </div>
                </div>

                <div class="form-group">
                    <label>Adresse email</label>
                    <input type="email" name="email" placeholder="votre@email.com" value="{{ old('email') }}">
                    @error('email')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label>Sujet <span class="required">*</span></label>
                    <select name="sujet" required>
                        <option value="">-- Choisir un sujet --</option>
                        <option value="Production de cartes scolaires" {{ old('sujet') == 'Production de cartes scolaires' ? 'selected' : '' }}>
                            Production de cartes scolaires
                        </option>
                        <option value="Travaux d'imprimerie" {{ old('sujet') == "Travaux d'imprimerie" ? 'selected' : '' }}>
                            Travaux d'imprimerie
                        </option>
                        <option value="T-Shirts et Casquettes" {{ old('sujet') == 'T-Shirts et Casquettes' ? 'selected' : '' }}>
                            T-Shirts & Casquettes
                        </option>
                        <option value="Tirage de bâches" {{ old('sujet') == 'Tirage de bâches' ? 'selected' : '' }}>
                            Tirage de bâches
                        </option>
                        <option value="Enseigne lumineuse" {{ old('sujet') == 'Enseigne lumineuse' ? 'selected' : '' }}>
                            Enseigne lumineuse
                        </option>
                        <option value="Supports publicitaires" {{ old('sujet') == 'Supports publicitaires' ? 'selected' : '' }}>
                            Supports publicitaires
                        </option>
                        <option value="Autre demande" {{ old('sujet') == 'Autre demande' ? 'selected' : '' }}>
                            Autre demande
                        </option>
                    </select>
                    @error('sujet')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label>Message <span class="required">*</span></label>
                    <textarea name="message" placeholder="Décrivez votre projet ou votre demande en détail..." required>{{ old('message') }}</textarea>
                    @error('message')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fa-solid fa-paper-plane"></i> Envoyer le message
                </button>
            </form>
        </div>

    </div>
</section>

{{-- Localisation --}}
<section class="map-section">
    <div class="map-inner">
        <div class="map-title">Notre localisation</div>
        <div class="map-placeholder">
            <i class="fa-solid fa-map-location-dot"></i>
            <p>
                <strong>DONAMI-CHRIST</strong><br>
                Abomey-Calavi, Bidossessi, République du Bénin<br><br>
                <a href="https://maps.google.com/?q=Abomey-Calavi,Benin" target="_blank"
                   style="color:var(--gold); font-weight:600; text-decoration:none;">
                    <i class="fa-solid fa-external-link"></i> Ouvrir dans Google Maps
                </a>
            </p>
        </div>
    </div>
</section>

@endsection