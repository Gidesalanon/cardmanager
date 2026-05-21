@extends('layouts.welcome')
@section('title', 'À propos')

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
        50% { transform: translateY(-6px); }
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* PAGE HERO */
    .page-hero {
        background: linear-gradient(135deg, rgba(0,0,0,0.85) 0%, rgba(26,16,0,0.75) 50%, rgba(0,0,0,0.85) 100%),
                    url('{{ asset('assets/web/images/nature.jpg') }}');
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

    /* ABOUT MAIN */
    .about-main { padding: 75px 20px; background: #fff; }
    .about-main-inner {
        max-width: 1100px; margin: 0 auto;
        display: grid; grid-template-columns: 1fr 1fr;
        gap: 60px; align-items: center; margin-bottom: 70px;
    }

    .about-text-label {
        display: inline-block; color: var(--gold); font-size: 0.78rem;
        font-weight: 700; text-transform: uppercase; letter-spacing: 3px; margin-bottom: 12px;
    }
    .about-text h2 { font-size: 2rem; font-weight: 800; color: var(--dark); margin-bottom: 20px; }
    .about-text h2 span { color: var(--gold); }
    .about-text p { color: #4a5568; font-size: 0.95rem; line-height: 1.8; margin-bottom: 15px; }

    .about-contact-chips { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 20px; }
    .contact-chip {
        display: inline-flex; align-items: center; gap: 8px;
        background: #fdf6e3; color: #92700a;
        border: 1px solid #e8d89a; border-radius: 8px;
        padding: 8px 14px; font-size: 0.85rem; font-weight: 600;
    }
    .contact-chip i { color: var(--gold); }

    /* Visual card côté droit */
    .about-visual-card {
        background: linear-gradient(135deg, #0a0a0a 0%, #1a1000 100%);
        border-radius: 24px; padding: 40px;
        border: 1px solid rgba(201,168,76,0.25);
        box-shadow: 0 20px 50px rgba(0,0,0,0.15);
    }
    .visual-stat-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px; }
    .visual-stat {
        background: rgba(201,168,76,0.08);
        border: 1px solid rgba(201,168,76,0.2);
        border-radius: 14px; padding: 20px; text-align: center;
    }
    .visual-stat .num { font-size: 2.2rem; font-weight: 900; color: var(--gold2); line-height: 1; }
    .visual-stat .label { font-size: 0.78rem; color: #8a9ab0; margin-top: 5px; }

    .visual-icon-row { display: flex; justify-content: center; gap: 12px; flex-wrap: wrap; }
    .visual-icon {
        width: 50px; height: 50px;
        background: linear-gradient(135deg, rgba(201,168,76,0.2), rgba(201,168,76,0.05));
        border: 1px solid rgba(201,168,76,0.3); border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        color: var(--gold2); font-size: 1.2rem;
        animation: iconFloat 3s ease-in-out infinite;
    }
    .visual-icon:nth-child(2) { animation-delay: 0.5s; }
    .visual-icon:nth-child(3) { animation-delay: 1s; }
    .visual-icon:nth-child(4) { animation-delay: 1.5s; }
    .visual-icon:nth-child(5) { animation-delay: 2s; }

    /* MISSION VOCATION */
    .mv-section {
        max-width: 1100px; margin: 0 auto 70px;
        display: grid; grid-template-columns: 1fr 1fr; gap: 25px;
    }
    .mv-card {
        background: #fffdf8; border-radius: 18px; padding: 35px;
        border: 1px solid #ede8dc; transition: all 0.3s;
        position: relative; overflow: hidden;
    }
    .mv-card::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0;
        height: 4px; background: linear-gradient(90deg, var(--gold), var(--gold2));
    }
    .mv-card:hover { transform: translateY(-4px); box-shadow: 0 12px 30px rgba(201,168,76,0.12); }
    .mv-card-icon {
        width: 52px; height: 52px;
        background: linear-gradient(135deg, var(--gold), var(--gold2));
        border-radius: 13px; margin-bottom: 18px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.3rem; color: var(--dark);
        animation: pulse-gold 4s infinite;
    }
    .mv-card:nth-child(2) .mv-card-icon { animation-delay: 2s; }
    .mv-card h3 { font-size: 1.1rem; font-weight: 800; color: var(--dark); margin-bottom: 12px; }
    .mv-card p { color: #4a5568; font-size: 0.93rem; line-height: 1.75; }

    /* VALUES */
    .values-section {
        max-width: 1100px; margin: 0 auto;
        padding-bottom: 10px;
    }
    .values-header { text-align: center; margin-bottom: 40px; }
    .values-header .section-label {
        display: inline-block; color: var(--gold); font-size: 0.78rem;
        font-weight: 700; text-transform: uppercase; letter-spacing: 3px; margin-bottom: 10px;
    }
    .values-header h2 { font-size: 2rem; font-weight: 800; color: var(--dark); }

    .values-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 18px; }
    .value-card {
        background: #fff; border-radius: 16px; padding: 25px 18px;
        text-align: center; border: 1px solid #ede8dc;
        transition: all 0.3s; position: relative; overflow: hidden;
    }
    .value-card::after {
        content: ''; position: absolute; bottom: 0; left: 0; right: 0;
        height: 3px; background: linear-gradient(90deg, var(--gold), var(--gold2));
        transform: scaleX(0); transition: transform 0.3s;
    }
    .value-card:hover { transform: translateY(-5px); box-shadow: 0 12px 30px rgba(201,168,76,0.12); border-color: var(--gold); }
    .value-card:hover::after { transform: scaleX(1); }
    .value-icon {
        width: 52px; height: 52px;
        background: linear-gradient(135deg, var(--gold), var(--gold2));
        border-radius: 13px; margin: 0 auto 15px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.3rem; color: var(--dark);
        animation: pulse-gold 4s infinite;
    }
    .value-card:nth-child(2) .value-icon { animation-delay: 0.8s; }
    .value-card:nth-child(3) .value-icon { animation-delay: 1.6s; }
    .value-card:nth-child(4) .value-icon { animation-delay: 2.4s; }
    .value-card:nth-child(5) .value-icon { animation-delay: 3.2s; }
    .value-card h4 { font-size: 0.92rem; font-weight: 800; color: var(--dark); margin-bottom: 8px; }
    .value-card p { color: #64748b; font-size: 0.8rem; line-height: 1.55; }

    /* CTA */
    .cta-section {
        padding: 75px 20px; background: #0a0a0a;
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
    .btn-outline-gold:hover { border-color: var(--gold); background: rgba(201,168,76,0.08); }

    @media (max-width: 900px) {
        .about-main-inner, .mv-section { grid-template-columns: 1fr; }
        .values-grid { grid-template-columns: repeat(2, 1fr); }
        .page-hero h1 { font-size: 2rem; }
    }
    @media (max-width: 480px) {
        .values-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')

<div class="page-hero">
    <div class="page-hero-badge"><i class="fa-solid fa-building"></i> Notre entreprise</div>
    <h1>À <span>propos</span> de nous</h1>
    <div class="gold-line"></div>
    <p>Depuis plus de 30 ans, nous accompagnons nos clients avec expertise, créativité et professionnalisme dans tous leurs projets d'impression et de communication visuelle.</p>
</div>

<section class="about-main">
    <div class="about-main-inner">
        <div class="about-text">
            <span class="about-text-label">Notre histoire</span>
            <h2>Qui <span>sommes</span>-nous ?</h2>
            <div class="gold-line" style="margin: 0 0 20px;"></div>
            <p>Depuis plus de 30 ans, notre entreprise met son expertise au service des particuliers, établissements scolaires, entreprises et institutions à travers des solutions innovantes en imprimerie et communication visuelle.</p>
            <p>Spécialisés dans la production de cartes d'identité scolaires en PVC, nous intervenons également dans tous les travaux d'imprimerie et de personnalisation sur divers supports.</p>
            <p>Grâce à notre savoir-faire, notre professionnalisme et notre engagement envers la qualité, nous accompagnons nos clients dans la réalisation de leurs projets avec créativité, précision et efficacité.</p>
            <div class="about-contact-chips">
                <span class="contact-chip"><i class="fa-solid fa-location-dot"></i> Abomey-Calavi, Bidossessi</span>
                <span class="contact-chip"><i class="fa-solid fa-phone"></i> +229 01 66 44 92 32</span>
                <span class="contact-chip"><i class="fa-solid fa-phone"></i> +229 01 97 22 48 87</span>
            </div>
        </div>

        <div class="about-visual-card">
            <div class="visual-stat-grid">
                <div class="visual-stat">
                    <div class="num">30+</div>
                    <div class="label">Années d'expérience</div>
                </div>
                <div class="visual-stat">
                    <div class="num">500+</div>
                    <div class="label">Écoles partenaires</div>
                </div>
                <div class="visual-stat">
                    <div class="num">10k+</div>
                    <div class="label">Cartes produites</div>
                </div>
                <div class="visual-stat">
                    <div class="num">100%</div>
                    <div class="label">Satisfaction client</div>
                </div>
            </div>
            <div class="visual-icon-row">
                <div class="visual-icon"><i class="fa-solid fa-id-card"></i></div>
                <div class="visual-icon"><i class="fa-solid fa-shirt"></i></div>
                <div class="visual-icon"><i class="fa-solid fa-image"></i></div>
                <div class="visual-icon"><i class="fa-solid fa-lightbulb"></i></div>
                <div class="visual-icon"><i class="fa-solid fa-print"></i></div>
            </div>
        </div>
    </div>

    {{-- Mission & Vocation --}}
    <div class="mv-section">
        <div class="mv-card">
            <div class="mv-card-icon"><i class="fa-solid fa-bullseye"></i></div>
            <h3>Notre mission</h3>
            <p>Offrir des solutions d'impression et de communication visuelle de haute qualité, adaptées aux besoins de chaque client, tout en garantissant rapidité, innovation et satisfaction totale.</p>
        </div>
        <div class="mv-card">
            <div class="mv-card-icon"><i class="fa-solid fa-star"></i></div>
            <h3>Notre vocation</h3>
            <p>Être une référence incontournable dans le domaine de l'imprimerie, de la personnalisation et de la production publicitaire, en valorisant l'image et l'identité de nos clients à travers des réalisations modernes et professionnelles.</p>
        </div>
    </div>

    {{-- Valeurs --}}
    <div class="values-section">
        <div class="values-header">
            <span class="section-label">Ce qui nous guide</span>
            <h2>Nos valeurs</h2>
            <div class="gold-line"></div>
        </div>
        <div class="values-grid">
            <div class="value-card">
                <div class="value-icon"><i class="fa-solid fa-medal"></i></div>
                <h4>Excellence</h4>
                <p>Réalisations de qualité irréprochable à chaque commande, sans compromis.</p>
            </div>
            <div class="value-card">
                <div class="value-icon"><i class="fa-solid fa-briefcase"></i></div>
                <h4>Professionnalisme</h4>
                <p>Sérieux, rigueur et respect des délais pour chaque projet confié.</p>
            </div>
            <div class="value-card">
                <div class="value-icon"><i class="fa-solid fa-lightbulb"></i></div>
                <h4>Innovation</h4>
                <p>Techniques modernes et créativité pour des solutions adaptées aux tendances.</p>
            </div>
            <div class="value-card">
                <div class="value-icon"><i class="fa-solid fa-heart"></i></div>
                <h4>Satisfaction</h4>
                <p>La confiance et la fidélité de nos clients sont au cœur de tout ce que nous faisons.</p>
            </div>
            <div class="value-card">
                <div class="value-icon"><i class="fa-solid fa-handshake"></i></div>
                <h4>Engagement</h4>
                <p>Investissement total dans chaque projet pour un résultat à la hauteur des attentes.</p>
            </div>
        </div>
    </div>
</section>

<section class="cta-section">
    <div class="cta-inner">
        <h2>Travaillons <span>ensemble</span></h2>
        <p>Confiez-nous votre projet et bénéficiez de 30 ans d'expertise en imprimerie et communication visuelle au Bénin.</p>
        <div style="display:flex; gap:15px; justify-content:center; flex-wrap:wrap;">
            <a href="{{ url('/contact') }}" class="btn-gold">
                <i class="fa-solid fa-envelope"></i> Nous contacter
            </a>
            <a href="{{ url('/services') }}" class="btn-outline-gold">
                <i class="fa-solid fa-layer-group"></i> Nos services
            </a>
        </div>
    </div>
</section>

@endsection