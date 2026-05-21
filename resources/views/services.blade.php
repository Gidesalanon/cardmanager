@extends('layouts.welcome')
@section('title', 'Services')

@push('styles')
<style>
    :root {
        --gold:  #c9a84c;
        --gold2: #f0d080;
        --dark:  #0a0a0a;
        --dark2: #111111;
    }

    /* PAGE HERO */
    .page-hero {
        background: linear-gradient(135deg, #0a0a0a 0%, #1a1000 50%, #0a0a0a 100%);
        padding: 90px 20px 70px;
        text-align: center;
        position: relative;
        overflow: hidden;
        border-bottom: 1px solid rgba(201,168,76,0.2);
    }
    .page-hero::before {
        content: '';
        position: absolute; top: -150px; left: 50%; transform: translateX(-50%);
        width: 600px; height: 600px; border-radius: 50%;
        background: radial-gradient(circle, rgba(201,168,76,0.07) 0%, transparent 70%);
    }
    .page-hero-badge {
        display: inline-flex; align-items: center; gap: 8px;
        background: rgba(201,168,76,0.1);
        border: 1px solid rgba(201,168,76,0.3);
        color: #f0d080; padding: 6px 18px; border-radius: 100px;
        font-size: 0.78rem; font-weight: 700; letter-spacing: 2px;
        text-transform: uppercase; margin-bottom: 20px;
    }
    .page-hero h1 {
        font-size: 2.8rem; font-weight: 900; color: #fff;
        margin-bottom: 15px; position: relative;
    }
    .page-hero h1 span { color: var(--gold2); }
    .page-hero p { color: #8a9ab0; font-size: 1rem; max-width: 580px; margin: 0 auto; line-height: 1.7; }
    .gold-line { width: 60px; height: 3px; background: linear-gradient(90deg, var(--gold), var(--gold2)); border-radius: 2px; margin: 15px auto; }

    /* INTRO BAND */
    .intro-band {
        background: #fff;
        padding: 50px 20px;
        border-bottom: 1px solid #f0e8d0;
    }
    .intro-band-inner {
        max-width: 1200px; margin: 0 auto;
        display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px;
        text-align: center;
    }
    .intro-item { padding: 20px; }
    .intro-item-icon {
        width: 60px; height: 60px;
        background: linear-gradient(135deg, var(--gold), var(--gold2));
        border-radius: 15px; margin: 0 auto 15px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem; color: var(--dark);
        animation: pulse-gold 3s ease-in-out infinite;
    }
    .intro-item:nth-child(2) .intro-item-icon { animation-delay: 1s; }
    .intro-item:nth-child(3) .intro-item-icon { animation-delay: 2s; }
    @keyframes pulse-gold {
        0%, 100% { box-shadow: 0 0 0 0 rgba(201,168,76,0.4); }
        50% { box-shadow: 0 0 0 10px rgba(201,168,76,0); }
    }
    .intro-item h4 { font-size: 1rem; font-weight: 700; color: var(--dark); margin-bottom: 6px; }
    .intro-item p { font-size: 0.88rem; color: #64748b; line-height: 1.6; }

    /* SERVICES LIST */
    .services-full { padding: 70px 20px; background: #faf8f4; }
    .services-full-inner { max-width: 1100px; margin: 0 auto; }

    .section-label {
        display: inline-block; color: var(--gold); font-size: 0.78rem;
        font-weight: 700; text-transform: uppercase; letter-spacing: 3px; margin-bottom: 12px;
    }
    .section-title { font-size: 2rem; font-weight: 800; color: var(--dark); margin-bottom: 40px; }

    .service-row {
        display: grid; grid-template-columns: 80px 1fr;
        gap: 25px; align-items: start;
        background: #fff; border-radius: 18px; padding: 30px;
        margin-bottom: 20px;
        border: 1px solid #ede8dc;
        transition: all 0.35s;
        position: relative; overflow: hidden;
    }
    .service-row::before {
        content: '';
        position: absolute; left: 0; top: 0; bottom: 0;
        width: 4px;
        background: linear-gradient(180deg, var(--gold), var(--gold2));
        opacity: 0; transition: opacity 0.3s;
    }
    .service-row:hover { border-color: var(--gold); box-shadow: 0 12px 35px rgba(201,168,76,0.12); transform: translateX(4px); }
    .service-row:hover::before { opacity: 1; }

    .service-row-icon {
        width: 70px; height: 70px;
        background: linear-gradient(135deg, var(--gold), var(--gold2));
        border-radius: 18px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.7rem; color: var(--dark); flex-shrink: 0;
        transition: transform 0.3s;
        animation: pulse-gold 4s infinite;
    }
    .service-row:nth-child(2) .service-row-icon { animation-delay: 0.7s; }
    .service-row:nth-child(3) .service-row-icon { animation-delay: 1.4s; }
    .service-row:nth-child(4) .service-row-icon { animation-delay: 2.1s; }
    .service-row:nth-child(5) .service-row-icon { animation-delay: 2.8s; }
    .service-row:nth-child(6) .service-row-icon { animation-delay: 3.5s; }
    .service-row:hover .service-row-icon { transform: scale(1.1) rotate(-5deg); }

    .service-row-content h3 { font-size: 1.15rem; font-weight: 800; color: var(--dark); margin-bottom: 10px; }
    .service-row-content p { color: #64748b; font-size: 0.93rem; line-height: 1.75; }

    .service-tags { margin-top: 14px; display: flex; gap: 8px; flex-wrap: wrap; }
    .service-tag {
        display: inline-block;
        background: #fdf6e3; color: #92700a;
        padding: 4px 13px; border-radius: 100px;
        font-size: 0.78rem; font-weight: 600;
        border: 1px solid #e8d89a;
    }

    /* CTA */
    .cta-section {
        padding: 75px 20px;
        background: #0a0a0a;
        text-align: center; position: relative; overflow: hidden;
    }
    .cta-section::before {
        content: ''; position: absolute; top: -100px; left: 50%; transform: translateX(-50%);
        width: 500px; height: 500px; border-radius: 50%;
        background: radial-gradient(circle, rgba(201,168,76,0.08) 0%, transparent 70%);
    }
    .cta-inner { max-width: 650px; margin: 0 auto; position: relative; z-index: 1; }
    .cta-section h2 { font-size: 2.1rem; font-weight: 800; color: #fff; margin-bottom: 15px; }
    .cta-section h2 span { color: var(--gold2); }
    .cta-section p { color: #8a9ab0; font-size: 1rem; line-height: 1.7; margin-bottom: 35px; }
    .btn-gold {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 14px 28px;
        background: linear-gradient(135deg, var(--gold), var(--gold2));
        color: var(--dark); border-radius: 10px; font-weight: 700; font-size: 0.95rem;
        text-decoration: none; transition: all 0.3s;
        box-shadow: 0 4px 20px rgba(201,168,76,0.35);
    }
    .btn-gold:hover { transform: translateY(-3px); box-shadow: 0 8px 30px rgba(201,168,76,0.5); }
    .btn-outline-gold {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 14px 28px; background: transparent;
        color: var(--gold2); border: 2px solid rgba(201,168,76,0.4);
        border-radius: 10px; font-weight: 600; font-size: 0.95rem;
        text-decoration: none; transition: all 0.3s;
    }
    .btn-outline-gold:hover { border-color: var(--gold); background: rgba(201,168,76,0.08); color: var(--gold2); }

    @media (max-width: 600px) {
        .service-row { grid-template-columns: 1fr; }
        .intro-band-inner { grid-template-columns: 1fr; }
        .page-hero h1 { font-size: 2rem; }
    }
</style>
@endpush

@section('content')

<div class="page-hero">
    <div class="page-hero-badge"><i class="fa-solid fa-layer-group"></i> Ce que nous faisons</div>
    <h1>Nos <span>Services</span></h1>
    <div class="gold-line"></div>
    <p>Des solutions complètes en imprimerie et communication visuelle pour particuliers, établissements scolaires, entreprises et institutions.</p>
</div>

<div class="intro-band">
    <div class="intro-band-inner">
        <div class="intro-item">
            <div class="intro-item-icon"><i class="fa-solid fa-bolt"></i></div>
            <h4>Livraison rapide</h4>
            <p>Vos commandes traitées dans les meilleurs délais avec un suivi personnalisé.</p>
        </div>
        <div class="intro-item">
            <div class="intro-item-icon"><i class="fa-solid fa-medal"></i></div>
            <h4>Qualité premium</h4>
            <p>Matériaux et équipements professionnels pour un rendu impeccable.</p>
        </div>
        <div class="intro-item">
            <div class="intro-item-icon"><i class="fa-solid fa-headset"></i></div>
            <h4>Accompagnement</h4>
            <p>Notre équipe vous guide de la conception à la livraison finale.</p>
        </div>
    </div>
</div>

<section class="services-full">
    <div class="services-full-inner">
        <span class="section-label">Catalogue complet</span>
        <h2 class="section-title">Tout ce que nous réalisons</h2>

        <div class="service-row">
            <div class="service-row-icon"><i class="fa-solid fa-id-card"></i></div>
            <div class="service-row-content">
                <h3>Production de cartes d'identité scolaires en PVC</h3>
                <p>Nous produisons des cartes scolaires en PVC de haute qualité, personnalisées avec photo, QR code, données de l'élève, logo de l'établissement, signature et cachet du directeur. Gestion entièrement numérique via notre plateforme CardManager — import Excel, génération en masse, recto/verso automatique.</p>
                <div class="service-tags">
                    <span class="service-tag">PVC haute qualité</span>
                    <span class="service-tag">QR Code</span>
                    <span class="service-tag">Photo intégrée</span>
                    <span class="service-tag">Recto / Verso</span>
                    <span class="service-tag">Génération en masse</span>
                    <span class="service-tag">Import Excel</span>
                </div>
            </div>
        </div>

        <div class="service-row">
            <div class="service-row-icon"><i class="fa-solid fa-shirt"></i></div>
            <div class="service-row-content">
                <h3>Impression sur T-Shirts & Casquettes</h3>
                <p>Personnalisation de vêtements et accessoires pour vos événements scolaires, associatifs, sportifs ou d'entreprise. Impression haute résolution, durable et lavable, sur tous coloris et toutes tailles.</p>
                <div class="service-tags">
                    <span class="service-tag">Sérigraphie</span>
                    <span class="service-tag">Broderie</span>
                    <span class="service-tag">Tous coloris</span>
                    <span class="service-tag">Toutes tailles</span>
                </div>
            </div>
        </div>

        <div class="service-row">
            <div class="service-row-icon"><i class="fa-solid fa-image"></i></div>
            <div class="service-row-content">
                <h3>Tirage de Bâches publicitaires</h3>
                <p>Impression grand format de bâches pour vos événements, campagnes publicitaires, inaugurations, expositions ou devantures commerciales. Résistantes aux intempéries et aux UV, disponibles en tous formats.</p>
                <div class="service-tags">
                    <span class="service-tag">Grand format</span>
                    <span class="service-tag">Résistant UV</span>
                    <span class="service-tag">Tout format</span>
                    <span class="service-tag">Intérieur/Extérieur</span>
                </div>
            </div>
        </div>

        <div class="service-row">
            <div class="service-row-icon"><i class="fa-solid fa-lightbulb"></i></div>
            <div class="service-row-content">
                <h3>Production d'enseignes lumineuses</h3>
                <p>Conception et fabrication d'enseignes lumineuses modernes (LED, boîtier lumineux, lettres découpées rétroéclairées) pour valoriser votre commerce, institution ou établissement scolaire avec élégance.</p>
                <div class="service-tags">
                    <span class="service-tag">LED économique</span>
                    <span class="service-tag">Sur mesure</span>
                    <span class="service-tag">Intérieur/Extérieur</span>
                    <span class="service-tag">Pose disponible</span>
                </div>
            </div>
        </div>

        <div class="service-row">
            <div class="service-row-icon"><i class="fa-solid fa-pen-ruler"></i></div>
            <div class="service-row-content">
                <h3>Impression sur supports publicitaires</h3>
                <p>Personnalisation de stylos, éventails, mugs, sacs, porte-clés et tout gadget publicitaire pour vos campagnes de communication, cadeaux d'entreprise ou promotions événementielles.</p>
                <div class="service-tags">
                    <span class="service-tag">Stylos</span>
                    <span class="service-tag">Éventails</span>
                    <span class="service-tag">Mugs</span>
                    <span class="service-tag">Porte-clés</span>
                    <span class="service-tag">Sacs</span>
                    <span class="service-tag">Gadgets</span>
                </div>
            </div>
        </div>

        <div class="service-row">
            <div class="service-row-icon"><i class="fa-solid fa-print"></i></div>
            <div class="service-row-content">
                <h3>Travaux d'imprimerie & production graphique</h3>
                <p>Tous travaux d'impression : flyers, affiches, cartes de visite, brochures, programmes, formulaires, documents officiels. Conception graphique disponible sur demande pour un rendu 100% professionnel.</p>
                <div class="service-tags">
                    <span class="service-tag">Flyers</span>
                    <span class="service-tag">Affiches</span>
                    <span class="service-tag">Cartes de visite</span>
                    <span class="service-tag">Brochures</span>
                    <span class="service-tag">Conception graphique</span>
                </div>
            </div>
        </div>

    </div>
</section>

<section class="cta-section">
    <div class="cta-inner">
        <h2>Besoin d'un <span>devis</span> ?</h2>
        <p>Contactez-nous pour discuter de votre projet et obtenir une offre personnalisée dans les meilleurs délais.</p>
        <div style="display:flex; gap:15px; justify-content:center; flex-wrap:wrap;">
            <a href="{{ url('/contact') }}" class="btn-gold">
                <i class="fa-solid fa-envelope"></i> Nous contacter
            </a>
            <a href="{{ url('/a-propos') }}" class="btn-outline-gold">
                <i class="fa-solid fa-circle-info"></i> En savoir plus
            </a>
        </div>
    </div>
</section>

@endsection