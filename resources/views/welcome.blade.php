@extends('layouts.welcome')
@section('title', 'Accueil')

@push('styles')
<style>
    /* ===== HERO SLIDER ===== */
    .hero {
        height: 100vh; min-height: 600px;
        position: relative; overflow: hidden;
        background: #000;
    }

    .slide {
        position: absolute; inset: 0;
        display: flex; align-items: center;
        opacity: 0; transition: opacity 1s ease;
        pointer-events: none;
    }
    .slide.active { opacity: 1; pointer-events: all; }

    .slide-bg {
        position: absolute; inset: 0;
        background-size: cover; background-position: center;
        transform: scale(1.08);
        transition: transform 8s ease;
    }
    .slide.active .slide-bg { transform: scale(1); }

    .slide-overlay {
        position: absolute; inset: 0;
        background: linear-gradient(135deg, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.4) 100%);
    }

    .slide-content {
        position: relative; z-index: 2;
        max-width: 1200px; margin: 0 auto; padding: 0 40px;
        width: 100%;
    }

    .slide-badge {
        display: inline-flex; align-items: center; gap: 8px;
        background: rgba(201,168,76,0.2);
        border: 1px solid rgba(201,168,76,0.5);
        color: #f0d080; padding: 6px 16px; border-radius: 100px;
        font-size: 0.82rem; font-weight: 600; letter-spacing: 1px;
        text-transform: uppercase; margin-bottom: 20px;
        opacity: 0; transform: translateY(20px);
        transition: all 0.6s ease 0.3s;
    }
    .slide.active .slide-badge { opacity: 1; transform: translateY(0); }

    .slide-content h1 {
        font-size: 3.5rem; font-weight: 900; color: #fff;
        line-height: 1.15; margin-bottom: 20px;
        opacity: 0; transform: translateY(30px);
        transition: all 0.7s ease 0.5s;
    }
    .slide.active .slide-content h1 { opacity: 1; transform: translateY(0); }
    .slide-content h1 span { color: #f0d080; }

    .slide-content p {
        font-size: 1.1rem; color: rgba(255,255,255,0.8);
        line-height: 1.7; max-width: 550px; margin-bottom: 35px;
        opacity: 0; transform: translateY(20px);
        transition: all 0.7s ease 0.7s;
    }
    .slide.active .slide-content p { opacity: 1; transform: translateY(0); }

    .slide-buttons {
        display: flex; gap: 15px; flex-wrap: wrap;
        opacity: 0; transform: translateY(20px);
        transition: all 0.7s ease 0.9s;
    }
    .slide.active .slide-buttons { opacity: 1; transform: translateY(0); }

    .btn-gold {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 14px 28px;
        background: linear-gradient(135deg, #c9a84c, #f0d080);
        color: #0a0a0a; border-radius: 10px;
        font-weight: 700; font-size: 0.95rem;
        text-decoration: none; transition: all 0.3s;
        box-shadow: 0 4px 20px rgba(201,168,76,0.4);
    }
    .btn-gold:hover { transform: translateY(-3px); box-shadow: 0 8px 30px rgba(201,168,76,0.5); }

    .btn-outline-white {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 14px 28px;
        background: transparent; color: #fff;
        border: 2px solid rgba(255,255,255,0.4);
        border-radius: 10px; font-weight: 600; font-size: 0.95rem;
        text-decoration: none; transition: all 0.3s;
    }
    .btn-outline-white:hover { border-color: #f0d080; color: #f0d080; background: rgba(201,168,76,0.08); }

    /* Slider navigation */
    .slider-nav {
        position: absolute; bottom: 35px; left: 50%;
        transform: translateX(-50%);
        display: flex; gap: 10px; z-index: 10;
    }
    .slider-dot {
        width: 8px; height: 8px; border-radius: 50%;
        background: rgba(255,255,255,0.3); cursor: pointer;
        transition: all 0.3s; border: none;
    }
    .slider-dot.active { background: #f0d080; width: 28px; border-radius: 4px; }

    .slider-arrows {
        position: absolute; top: 50%; transform: translateY(-50%);
        left: 0; right: 0; z-index: 10;
        display: flex; justify-content: space-between; padding: 0 20px;
        pointer-events: none;
    }
    .slider-arrow {
        width: 46px; height: 46px;
        background: rgba(201,168,76,0.15);
        border: 1px solid rgba(201,168,76,0.4);
        border-radius: 50%; color: #f0d080;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; transition: all 0.3s;
        pointer-events: all; font-size: 1rem;
    }
    .slider-arrow:hover { background: rgba(201,168,76,0.3); }

    /* Slide backgrounds (gradients en attendant de vraies images) */
    .slide-1 .slide-bg { background-image: url('{{ asset('assets/web/images/121.png') }}'); background-size: cover; background-position: center; }
    .slide-2 .slide-bg { background-image: url('{{ asset('assets/web/images/2001.png') }}'); background-size: cover; background-position: center top; }
    .slide-3 .slide-bg { background-image: url('{{ asset('assets/web/images/nature.jpg') }}'); background-size: cover; background-position: center bottom; }

    /* Éléments décoratifs slides */
    .slide-decoration {
        position: absolute; right: 10%; top: 50%; transform: translateY(-50%);
        z-index: 2; opacity: 0; transition: all 0.8s ease 1s;
    }
    .slide.active .slide-decoration { opacity: 1; }

    .deco-card {
        background: rgba(201,168,76,0.08);
        border: 1px solid rgba(201,168,76,0.2);
        border-radius: 20px; padding: 30px;
        backdrop-filter: blur(10px);
        width: 280px;
    }

    .deco-icon-row {
        display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap;
    }

    .deco-icon {
        width: 50px; height: 50px;
        background: linear-gradient(135deg, rgba(201,168,76,0.3), rgba(240,208,128,0.15));
        border: 1px solid rgba(201,168,76,0.3);
        border-radius: 12px; display: flex; align-items: center; justify-content: center;
        color: #f0d080; font-size: 1.2rem;
        animation: iconFloat 3s ease-in-out infinite;
    }
    .deco-icon:nth-child(2) { animation-delay: 0.5s; }
    .deco-icon:nth-child(3) { animation-delay: 1s; }
    .deco-icon:nth-child(4) { animation-delay: 1.5s; }

    @keyframes iconFloat {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-6px); }
    }

    @keyframes pulse-gold {
        0%, 100% { box-shadow: 0 0 0 0 rgba(201,168,76,0.4); }
        50% { box-shadow: 0 0 0 8px rgba(201,168,76,0); }
    }

    .deco-stat {
        display: flex; align-items: center; gap: 12px;
        padding: 12px; border-radius: 10px;
        background: rgba(255,255,255,0.03); margin-top: 10px;
    }
    .deco-stat-num { font-size: 1.4rem; font-weight: 800; color: #f0d080; }
    .deco-stat-label { font-size: 0.78rem; color: rgba(255,255,255,0.6); line-height: 1.3; }

    /* ===== FEATURES BAND ===== */
    .features-band { background: #0a0a0a; padding: 40px 20px; border-bottom: 1px solid rgba(201,168,76,0.1); }
    .features-band-inner {
        max-width: 1200px; margin: 0 auto;
        display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px;
    }
    .feature-item {
        display: flex; align-items: center; gap: 14px;
        padding: 15px; border-radius: 10px;
        transition: background 0.2s;
    }
    .feature-item:hover { background: rgba(201,168,76,0.05); }
    .feature-item-icon {
        width: 44px; height: 44px; flex-shrink: 0;
        background: linear-gradient(135deg, rgba(201,168,76,0.2), rgba(201,168,76,0.05));
        border: 1px solid rgba(201,168,76,0.25);
        border-radius: 10px; display: flex; align-items: center; justify-content: center;
        color: #f0d080; font-size: 1.1rem;
        animation: pulse-gold 3s infinite;
    }
    .feature-item:nth-child(2) .feature-item-icon { animation-delay: 0.75s; }
    .feature-item:nth-child(3) .feature-item-icon { animation-delay: 1.5s; }
    .feature-item:nth-child(4) .feature-item-icon { animation-delay: 2.25s; }
    .feature-item-text h4 { font-size: 0.9rem; font-weight: 700; color: #fff; margin-bottom: 3px; }
    .feature-item-text p { font-size: 0.78rem; color: #64748b; }

    /* ===== STATS ===== */
    .stats-section { background: linear-gradient(135deg, #c9a84c 0%, #f0d080 50%, #c9a84c 100%); padding: 55px 20px; }
    .stats-inner { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(4, 1fr); gap: 30px; text-align: center; }
    .stat-item h3 { font-size: 2.8rem; font-weight: 900; color: #0a0a0a; margin-bottom: 5px; }
    .stat-item p { color: rgba(0,0,0,0.65); font-size: 0.9rem; font-weight: 500; }

    /* ===== SERVICES ===== */
    .services-section { padding: 80px 20px; background: #fff; }
    .section-header { text-align: center; max-width: 600px; margin: 0 auto 50px; }
    .section-label {
        display: inline-block; color: #c9a84c; font-size: 0.8rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: 3px; margin-bottom: 12px;
    }
    .section-header h2 { font-size: 2.2rem; font-weight: 800; color: #0a0a0a; margin-bottom: 15px; }
    .section-header p { color: #64748b; font-size: 0.95rem; line-height: 1.7; }
    .gold-line { width: 60px; height: 3px; background: linear-gradient(90deg, #c9a84c, #f0d080); border-radius: 2px; margin: 12px auto; }

    .services-grid { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(3, 1fr); gap: 25px; }

    .service-card {
        border-radius: 16px; padding: 30px;
        border: 1px solid #e8e0d0; background: #fffdf8;
        transition: all 0.35s; cursor: default;
    }
    .service-card:hover {
        transform: translateY(-6px);
        border-color: #c9a84c;
        box-shadow: 0 15px 40px rgba(201,168,76,0.15);
        background: #fff;
    }

    .service-icon {
        width: 58px; height: 58px;
        background: linear-gradient(135deg, #c9a84c, #f0d080);
        border-radius: 15px;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 20px; font-size: 1.4rem; color: #0a0a0a;
        transition: transform 0.3s;
        animation: pulse-gold 4s infinite;
    }
    .service-card:nth-child(2) .service-icon { animation-delay: 0.7s; }
    .service-card:nth-child(3) .service-icon { animation-delay: 1.4s; }
    .service-card:nth-child(4) .service-icon { animation-delay: 2.1s; }
    .service-card:nth-child(5) .service-icon { animation-delay: 2.8s; }
    .service-card:nth-child(6) .service-icon { animation-delay: 3.5s; }

    .service-card:hover .service-icon { transform: scale(1.1) rotate(-5deg); }
    .service-card h3 { font-size: 1.05rem; font-weight: 700; color: #0a0a0a; margin-bottom: 10px; }
    .service-card p { color: #64748b; font-size: 0.88rem; line-height: 1.65; }

    /* ===== CTA ===== */
    .cta-section {
        padding: 80px 20px;
        background: #0a0a0a;
        text-align: center; position: relative; overflow: hidden;
    }
    .cta-section::before {
        content: '';
        position: absolute; top: -100px; left: 50%; transform: translateX(-50%);
        width: 500px; height: 500px; border-radius: 50%;
        background: radial-gradient(circle, rgba(201,168,76,0.08) 0%, transparent 70%);
    }
    .cta-inner { max-width: 650px; margin: 0 auto; position: relative; z-index: 1; }
    .cta-section h2 { font-size: 2.2rem; font-weight: 800; color: #fff; margin-bottom: 15px; }
    .cta-section h2 span { color: #f0d080; }
    .cta-section p { color: #8a9ab0; font-size: 1rem; line-height: 1.7; margin-bottom: 35px; }

    @media (max-width: 900px) {
        .slide-decoration { display: none; }
        .slide-content h1 { font-size: 2.4rem; }
        .features-band-inner { grid-template-columns: repeat(2, 1fr); }
        .stats-inner { grid-template-columns: repeat(2, 1fr); }
        .services-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 500px) {
        .slide-content h1 { font-size: 1.9rem; }
        .features-band-inner { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')

{{-- ===== HERO SLIDER ===== --}}
<section class="hero" id="heroSlider">

    {{-- Slide 1 --}}
    <div class="slide slide-1 active">
        <div class="slide-bg"></div>
        <div class="slide-overlay"></div>
        <div class="slide-content">
            <div class="slide-badge"><i class="fa-solid fa-star"></i> +30 ans d'expertise</div>
            <h1>Cartes scolaires<br><span>en PVC haute qualité</span></h1>
            <p>Produisez les cartes d'identité scolaires de vos élèves en quelques clics. Photo, QR code, recto/verso — tout est automatisé.</p>
            <div class="slide-buttons">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-gold"><i class="fa-solid fa-gauge"></i> Mon espace</a>
                @else
                    <a href="{{ route('register') }}" class="btn-gold"><i class="fa-solid fa-rocket"></i> Commencer gratuitement</a>
                    <a href="{{ route('login') }}" class="btn-outline-white"><i class="fa-solid fa-right-to-bracket"></i> Connexion</a>
                @endauth
            </div>
        </div>
        <div class="slide-decoration">
            <div class="deco-card">
                <div class="deco-icon-row">
                    <div class="deco-icon"><i class="fa-solid fa-id-card"></i></div>
                    <div class="deco-icon"><i class="fa-solid fa-qrcode"></i></div>
                    <div class="deco-icon"><i class="fa-solid fa-camera"></i></div>
                    <div class="deco-icon"><i class="fa-solid fa-print"></i></div>
                </div>
                <div class="deco-stat">
                    <div><div class="deco-stat-num">10k+</div></div>
                    <div class="deco-stat-label">Cartes produites<br>cette année</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Slide 2 --}}
    <div class="slide slide-2">
        <div class="slide-bg"></div>
        <div class="slide-overlay"></div>
        <div class="slide-content">
            <div class="slide-badge"><i class="fa-solid fa-palette"></i> Communication visuelle</div>
            <h1>Impression sur<br><span>tous supports</span></h1>
            <p>T-shirts, casquettes, bâches, enseignes lumineuses, gadgets publicitaires... Votre image, portée par notre expertise.</p>
            <div class="slide-buttons">
                <a href="{{ url('/services') }}" class="btn-gold"><i class="fa-solid fa-layer-group"></i> Voir nos services</a>
                <a href="{{ url('/contact') }}" class="btn-outline-white"><i class="fa-solid fa-envelope"></i> Devis gratuit</a>
            </div>
        </div>
        <div class="slide-decoration">
            <div class="deco-card">
                <div class="deco-icon-row">
                    <div class="deco-icon"><i class="fa-solid fa-shirt"></i></div>
                    <div class="deco-icon"><i class="fa-solid fa-image"></i></div>
                    <div class="deco-icon"><i class="fa-solid fa-lightbulb"></i></div>
                    <div class="deco-icon"><i class="fa-solid fa-pen-ruler"></i></div>
                </div>
                <div class="deco-stat">
                    <div><div class="deco-stat-num">500+</div></div>
                    <div class="deco-stat-label">Clients satisfaits<br>chaque année</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Slide 3 --}}
    <div class="slide slide-3">
        <div class="slide-bg"></div>
        <div class="slide-overlay"></div>
        <div class="slide-content">
            <div class="slide-badge"><i class="fa-solid fa-handshake"></i> Partenaire de confiance</div>
            <h1>30 ans au service<br><span>de votre image</span></h1>
            <p>Basés à Abomey-Calavi, nous accompagnons les établissements scolaires, entreprises et institutions du Bénin avec passion et professionnalisme.</p>
            <div class="slide-buttons">
                <a href="{{ url('/a-propos') }}" class="btn-gold"><i class="fa-solid fa-circle-info"></i> Notre histoire</a>
                <a href="{{ url('/contact') }}" class="btn-outline-white"><i class="fa-solid fa-phone"></i> Nous appeler</a>
            </div>
        </div>
        <div class="slide-decoration">
            <div class="deco-card">
                <div class="deco-icon-row">
                    <div class="deco-icon"><i class="fa-solid fa-medal"></i></div>
                    <div class="deco-icon"><i class="fa-solid fa-trophy"></i></div>
                    <div class="deco-icon"><i class="fa-solid fa-star"></i></div>
                    <div class="deco-icon"><i class="fa-solid fa-heart"></i></div>
                </div>
                <div class="deco-stat">
                    <div><div class="deco-stat-num">30+</div></div>
                    <div class="deco-stat-label">Années d'expérience<br>& de passion</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Contrôles --}}
    <div class="slider-arrows">
        <button class="slider-arrow" onclick="changeSlide(-1)"><i class="fa-solid fa-chevron-left"></i></button>
        <button class="slider-arrow" onclick="changeSlide(1)"><i class="fa-solid fa-chevron-right"></i></button>
    </div>
    <div class="slider-nav">
        <button class="slider-dot active" onclick="goSlide(0)"></button>
        <button class="slider-dot" onclick="goSlide(1)"></button>
        <button class="slider-dot" onclick="goSlide(2)"></button>
    </div>
</section>

{{-- ===== FEATURES BAND ===== --}}
<div class="features-band">
    <div class="features-band-inner">
        <div class="feature-item">
            <div class="feature-item-icon"><i class="fa-solid fa-bolt"></i></div>
            <div class="feature-item-text">
                <h4>Production rapide</h4>
                <p>Génération en masse en quelques secondes</p>
            </div>
        </div>
        <div class="feature-item">
            <div class="feature-item-icon"><i class="fa-solid fa-shield-halved"></i></div>
            <div class="feature-item-text">
                <h4>Qualité garantie</h4>
                <p>PVC haute durabilité, impression nette</p>
            </div>
        </div>
        <div class="feature-item">
            <div class="feature-item-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
            <div class="feature-item-text">
                <h4>Import Excel</h4>
                <p>Importer tous vos élèves en un clic</p>
            </div>
        </div>
        <div class="feature-item">
            <div class="feature-item-icon"><i class="fa-solid fa-location-dot"></i></div>
            <div class="feature-item-text">
                <h4>Bénin local</h4>
                <p>Abomey-Calavi, service de proximité</p>
            </div>
        </div>
    </div>
</div>

{{-- ===== STATS ===== --}}
<section class="stats-section">
    <div class="stats-inner">
        <div class="stat-item">
            <h3>30+</h3>
            <p>Années d'expérience</p>
        </div>
        <div class="stat-item">
            <h3>500+</h3>
            <p>Écoles partenaires</p>
        </div>
        <div class="stat-item">
            <h3>10k+</h3>
            <p>Cartes produites</p>
        </div>
        <div class="stat-item">
            <h3>100%</h3>
            <p>Clients satisfaits</p>
        </div>
    </div>
</section>

{{-- ===== SERVICES ===== --}}
<section class="services-section">
    <div class="section-header">
        <span class="section-label">Ce que nous proposons</span>
        <h2>Nos Services</h2>
        <div class="gold-line"></div>
        <p>Des solutions complètes en imprimerie et communication visuelle pour tous vos besoins.</p>
    </div>
    <div class="services-grid">
        <div class="service-card">
            <div class="service-icon"><i class="fa-solid fa-id-card"></i></div>
            <h3>Cartes scolaires PVC</h3>
            <p>Production de cartes d'identité scolaires avec photo, QR code et données personnalisées via notre plateforme numérique.</p>
        </div>
        <div class="service-card">
            <div class="service-icon"><i class="fa-solid fa-shirt"></i></div>
            <h3>T-Shirts & Casquettes</h3>
            <p>Personnalisation de vêtements et accessoires pour vos événements, équipes ou campagnes promotionnelles.</p>
        </div>
        <div class="service-card">
            <div class="service-icon"><i class="fa-solid fa-image"></i></div>
            <h3>Tirage de Bâches</h3>
            <p>Impression grand format résistante UV pour événements, campagnes publicitaires et devantures commerciales.</p>
        </div>
        <div class="service-card">
            <div class="service-icon"><i class="fa-solid fa-lightbulb"></i></div>
            <h3>Enseignes lumineuses</h3>
            <p>Conception et fabrication d'enseignes LED modernes pour valoriser votre commerce ou institution.</p>
        </div>
        <div class="service-card">
            <div class="service-icon"><i class="fa-solid fa-pen-ruler"></i></div>
            <h3>Supports publicitaires</h3>
            <p>Impression sur stylos, éventails, mugs, porte-clés et tout gadget pour vos campagnes de communication.</p>
        </div>
        <div class="service-card">
            <div class="service-icon"><i class="fa-solid fa-print"></i></div>
            <h3>Travaux d'imprimerie</h3>
            <p>Flyers, affiches, cartes de visite, brochures, documents officiels — tous vos besoins en impression.</p>
        </div>
    </div>
</section>

{{-- ===== CTA ===== --}}
<section class="cta-section">
    <div class="cta-inner">
        <h2>Prêt à <span>démarrer</span> ?</h2>
        <p>Rejoignez des centaines d'établissements qui font confiance à notre plateforme pour la gestion et la production de leurs cartes scolaires.</p>
        <div style="display:flex; gap:15px; justify-content:center; flex-wrap:wrap;">
            @auth
                <a href="{{ route('dashboard') }}" class="btn-gold">
                    <i class="fa-solid fa-gauge"></i> Accéder à mon espace
                </a>
            @else
                <a href="{{ route('register') }}" class="btn-gold">
                    <i class="fa-solid fa-rocket"></i> Créer un compte
                </a>
                <a href="{{ url('/contact') }}" class="btn-outline-white">
                    <i class="fa-solid fa-envelope"></i> Nous contacter
                </a>
            @endauth
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
let currentSlide = 0;
const slides = document.querySelectorAll('.slide');
const dots   = document.querySelectorAll('.slider-dot');
let autoInterval;

function goSlide(n) {
    slides[currentSlide].classList.remove('active');
    dots[currentSlide].classList.remove('active');
    currentSlide = (n + slides.length) % slides.length;
    slides[currentSlide].classList.add('active');
    dots[currentSlide].classList.add('active');
}

function changeSlide(dir) {
    clearInterval(autoInterval);
    goSlide(currentSlide + dir);
    startAuto();
}

function startAuto() {
    autoInterval = setInterval(() => goSlide(currentSlide + 1), 5500);
}

startAuto();

// Swipe support mobile
let touchStartX = 0;
document.getElementById('heroSlider').addEventListener('touchstart', e => { touchStartX = e.touches[0].clientX; });
document.getElementById('heroSlider').addEventListener('touchend', e => {
    const diff = touchStartX - e.changedTouches[0].clientX;
    if (Math.abs(diff) > 50) changeSlide(diff > 0 ? 1 : -1);
});
</script>
@endpush