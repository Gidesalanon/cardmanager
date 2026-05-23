<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'DONAMI-CHRIST') — Imprimerie & Communication Visuelle</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('assets/web/images/favicon.png') }}">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --gold:    #c9a84c;
            --gold2:   #f0d080;
            --dark:    #0a0a0a;
            --dark2:   #111111;
            --dark3:   #1a1a1a;
            --white:   #ffffff;
            --offwhite:#f5f0e8;
            --muted:   #a0998a;
        }
        body { font-family: 'Segoe UI', Arial, sans-serif; color: #333; background: #fff; }

        /* ===== NAVBAR ===== */
        .navbar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
            background: rgba(10,10,10,0.97);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(201,168,76,0.3);
            transition: all 0.3s;
        }
        .navbar.scrolled { border-bottom-color: var(--gold); box-shadow: 0 2px 20px rgba(201,168,76,0.15); }
        .navbar-inner {
            max-width: 1200px; margin: 0 auto; padding: 0 24px;
            display: flex; align-items: center; justify-content: space-between;
            height: 68px;
        }
        .navbar-brand { display: flex; align-items: center; gap: 12px; text-decoration: none; }
        .navbar-brand img { height: 38px; object-fit: contain; }
        .brand-text { display: flex; flex-direction: column; }
        .brand-name { font-size: 1rem; font-weight: 800; color: var(--gold); letter-spacing: 1px; line-height: 1; }
        .brand-sub { font-size: 0.65rem; color: var(--muted); letter-spacing: 2px; text-transform: uppercase; margin-top: 2px; }

        .navbar-menu { display: flex; align-items: center; gap: 4px; list-style: none; }
        .navbar-menu a {
            display: flex; align-items: center; gap: 6px;
            padding: 8px 15px; color: #ccc; text-decoration: none;
            font-size: 0.9rem; font-weight: 500; border-radius: 6px;
            transition: all 0.2s; position: relative;
        }
        .navbar-menu a i { font-size: 0.8rem; transition: transform 0.3s; }
        .navbar-menu a:hover { color: var(--gold); background: rgba(201,168,76,0.08); }
        .navbar-menu a:hover i { transform: scale(1.2); }
        .navbar-menu a.active { color: var(--gold); }
        .navbar-menu a.active::after {
            content: ''; position: absolute; bottom: 4px; left: 15px; right: 15px;
            height: 2px; background: var(--gold); border-radius: 1px;
        }

        .btn-espace {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 9px 20px;
            background: linear-gradient(135deg, var(--gold), var(--gold2));
            color: var(--dark) !important; border-radius: 8px;
            font-weight: 700; font-size: 0.88rem;
            transition: all 0.3s; box-shadow: 0 2px 10px rgba(201,168,76,0.3);
        }
        .btn-espace:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(201,168,76,0.4) !important;
            background: linear-gradient(135deg, var(--gold2), var(--gold)) !important;
        }
        .btn-espace::after { display: none !important; }

        /* Dropdown profil */
        .profile-dropdown { position: relative; }
        .profile-btn {
            display: flex; align-items: center; gap: 8px;
            padding: 7px 14px;
            background: rgba(201,168,76,0.1);
            border: 1px solid rgba(201,168,76,0.3);
            border-radius: 8px; cursor: pointer;
            color: var(--gold) !important; font-size: 0.88rem; font-weight: 600;
            transition: all 0.2s; text-decoration: none;
        }
        .profile-btn:hover { background: rgba(201,168,76,0.2) !important; }
        .profile-btn::after { display: none !important; }
        .profile-btn .chevron { transition: transform 0.2s; font-size: 0.7rem; }
        .profile-dropdown:hover .chevron { transform: rotate(180deg); }

        .dropdown-menu {
            position: absolute; top: calc(100% + 8px); right: 0;
            background: var(--dark2);
            border: 1px solid rgba(201,168,76,0.25);
            border-radius: 10px; min-width: 200px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
            opacity: 0; visibility: hidden; transform: translateY(-8px);
            transition: all 0.2s; z-index: 100;
            overflow: hidden;
        }
        .profile-dropdown:hover .dropdown-menu {
            opacity: 1; visibility: visible; transform: translateY(0);
        }

        .dropdown-header {
            padding: 12px 16px;
            border-bottom: 1px solid rgba(201,168,76,0.15);
        }
        .dropdown-header .d-name { font-size: 0.9rem; font-weight: 700; color: var(--gold); }
        .dropdown-header .d-role {
            font-size: 0.75rem; color: var(--muted);
            background: rgba(201,168,76,0.1);
            padding: 2px 8px; border-radius: 4px; margin-top: 4px;
            display: inline-block;
        }

        .dropdown-item {
            display: flex; align-items: center; gap: 10px;
            padding: 11px 16px; color: #ccc !important; font-size: 0.88rem;
            text-decoration: none; transition: all 0.15s;
        }
        .dropdown-item i { width: 16px; color: var(--muted); font-size: 0.85rem; }
        .dropdown-item:hover { background: rgba(255,255,255,0.05); color: #fff !important; }
        .dropdown-item:hover i { color: var(--gold); }
        .dropdown-item.logout { color: #f87171 !important; }
        .dropdown-item.logout i { color: #f87171; }
        .dropdown-item.logout:hover { background: rgba(248,113,113,0.1); }

        .dropdown-divider { border: none; border-top: 1px solid rgba(255,255,255,0.07); margin: 4px 0; }

        /* Hamburger */
        .navbar-toggle {
            display: none; background: none; border: 1px solid rgba(201,168,76,0.3);
            color: var(--gold); font-size: 1.2rem; cursor: pointer;
            padding: 8px 10px; border-radius: 6px;
        }
        .navbar-mobile {
            display: none; flex-direction: column;
            background: var(--dark2); padding: 12px 20px;
            border-top: 1px solid rgba(201,168,76,0.15);
        }
        .navbar-mobile.open { display: flex; }
        .navbar-mobile a {
            display: flex; align-items: center; gap: 10px;
            padding: 12px 0; color: #ccc; text-decoration: none;
            font-size: 0.95rem; border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .navbar-mobile a:last-child { border-bottom: none; }
        .navbar-mobile a:hover { color: var(--gold); }
        .navbar-mobile a i { color: var(--gold); width: 18px; }

        /* ===== PAGE CONTENT ===== */
        .page-content { padding-top: 68px; }

        /* ===== FOOTER ===== */
        .site-footer { background: var(--dark); color: #94a3b8; padding: 55px 20px 20px; }
        .footer-inner { max-width: 1200px; margin: 0 auto; }
        .footer-tricolor { height: 3px; display: flex; margin-bottom: 40px; }
        .footer-tricolor .g { flex: 1; background: #008751; }
        .footer-tricolor .y { flex: 1; background: #fcd116; }
        .footer-tricolor .r { flex: 1; background: #e8112d; }
        .footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 40px; margin-bottom: 40px; }
        .footer-brand img { height: 40px; margin-bottom: 15px; }
        .footer-brand p { font-size: 0.88rem; line-height: 1.7; color: #8a9ab0; }
        .footer-col h4 {
            color: var(--gold); font-size: 0.95rem; margin-bottom: 18px;
            padding-bottom: 8px; border-bottom: 1px solid rgba(201,168,76,0.2);
        }
        .footer-col ul { list-style: none; }
        .footer-col ul li { margin-bottom: 9px; }
        .footer-col ul li a { color: #8a9ab0; text-decoration: none; font-size: 0.88rem; transition: color 0.2s; }
        .footer-col ul li a:hover { color: var(--gold); }
        .contact-item { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 12px; font-size: 0.88rem; }
        .contact-item i { color: var(--gold); margin-top: 2px; width: 14px; }
        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.06);
            padding-top: 20px; text-align: center; font-size: 0.82rem; color: #64748b;
        }
        .footer-bottom a { color: var(--gold); text-decoration: none; }

        @media (max-width: 768px) {
            .navbar-menu { display: none; }
            .navbar-toggle { display: block; }
            .footer-grid { grid-template-columns: 1fr; gap: 25px; }
        }
    </style>
    @stack('styles')
</head>
<body>

<nav class="navbar" id="mainNav">
    <div class="navbar-inner">
        <a href="{{ route('home') }}" class="navbar-brand">
            <img src="{{ asset('assets/web/images/logo-3.png') }}" alt="Logo">
            <div class="brand-text">
                <span class="brand-name">DONAMI-CHRIST</span>
                <span class="brand-sub">Imprimerie & Visuel</span>
            </div>
        </a>

        <ul class="navbar-menu">
            <li><a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">
                <i class="fa-solid fa-house"></i> Accueil
            </a></li>
            <li><a href="{{ url('/services') }}" class="{{ request()->is('services') ? 'active' : '' }}">
                <i class="fa-solid fa-layer-group"></i> Services
            </a></li>
            <li><a href="{{ url('/a-propos') }}" class="{{ request()->is('a-propos') ? 'active' : '' }}">
                <i class="fa-solid fa-circle-info"></i> À propos
            </a></li>
            <li><a href="{{ url('/contact') }}" class="{{ request()->is('contact') ? 'active' : '' }}">
                <i class="fa-solid fa-envelope"></i> Contact
            </a></li>
            <li>
                @auth
                    <div class="profile-dropdown">
                        <a href="#" class="profile-btn">
                            <i class="fa-solid fa-circle-user"></i>
                            {{ Str::limit(auth()->user()->name, 15) }}
                            <i class="fa-solid fa-chevron-down chevron"></i>
                        </a>
                        <div class="dropdown-menu">
                            <div class="dropdown-header">
                                <div class="d-name">{{ auth()->user()->name }}</div>
                                <div class="d-role">
                                    {{ auth()->user()->role === 'admin' ? 'Administrateur' : 'École' }}
                                </div>
                            </div>
                            <a href="{{ route('dashboard') }}" class="dropdown-item">
                                <i class="fa-solid fa-gauge"></i> Mon tableau de bord
                            </a>
                            @if(auth()->user()->role === 'admin')
                                <a href="{{ route('profile.edit') }}" class="dropdown-item">
                                    <i class="fa-solid fa-user-pen"></i> Mon profil
                                </a>
                            @else
                                <a href="{{ route('profile.edit') }}" class="dropdown-item">
                                    <i class="fa-solid fa-user-pen"></i> Mon profil
                                </a>
                            @endif
                            <hr class="dropdown-divider">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item logout" style="width:100%; background:none; border:none; cursor:pointer; text-align:left;">
                                    <i class="fa-solid fa-right-from-bracket"></i> Déconnexion
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn-espace">
                        <i class="fa-solid fa-right-to-bracket"></i> Connexion
                    </a>
                @endauth
            </li>
        </ul>

        <button class="navbar-toggle" onclick="document.getElementById('mobileMenu').classList.toggle('open')">
            <i class="fa-solid fa-bars"></i>
        </button>
    </div>

    <div class="navbar-mobile" id="mobileMenu">
        <a href="{{ route('home') }}"><i class="fa-solid fa-house"></i> Accueil</a>
        <a href="{{ url('/services') }}"><i class="fa-solid fa-layer-group"></i> Services</a>
        <a href="{{ url('/a-propos') }}"><i class="fa-solid fa-circle-info"></i> À propos</a>
        <a href="{{ url('/contact') }}"><i class="fa-solid fa-envelope"></i> Contact</a>
        @auth
            <a href="{{ route('dashboard') }}" style="color:var(--gold, #c9a84c);">
                <i class="fa-solid fa-gauge"></i> Mon tableau de bord
            </a>
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('profile.edit') }}"><i class="fa-solid fa-user-pen"></i> Mon profil</a>
            @else
                <a href="{{ route('profile.edit') }}"><i class="fa-solid fa-user-pen"></i> Mon profil</a>
            @endif
            <form method="POST" action="{{ route('logout') }}" style="padding: 12px 0; border-bottom:1px solid rgba(255,255,255,0.05);">
                @csrf
                <button type="submit" style="background:none; border:none; color:#f87171; cursor:pointer; font-size:0.95rem; display:flex; align-items:center; gap:10px;">
                    <i class="fa-solid fa-right-from-bracket"></i> Déconnexion
                </button>
            </form>
        @else
            <a href="{{ route('login') }}" style="color:#c9a84c; font-weight:700;">
                <i class="fa-solid fa-right-to-bracket"></i> Connexion
            </a>
        @endauth
    </div>
</nav>

<div class="page-content">
    @yield('content')
</div>

<footer class="site-footer">
    <div class="footer-inner">
        <div class="footer-tricolor">
            <span class="g"></span><span class="y"></span><span class="r"></span>
        </div>
        <div class="footer-grid">
            <div class="footer-brand">
                <img src="{{ asset('assets/web/images/logo-3.png') }}" alt="Logo">
                <p>Depuis plus de 30 ans, nous mettons notre expertise au service des particuliers, établissements scolaires, entreprises et institutions à travers des solutions innovantes en imprimerie et communication visuelle.</p>
            </div>
            <div class="footer-col">
                <h4>Navigation</h4>
                <ul>
                    <li><a href="{{ route('home') }}">Accueil</a></li>
                    <li><a href="{{ url('/services') }}">Services</a></li>
                    <li><a href="{{ url('/a-propos') }}">À propos</a></li>
                    <li><a href="{{ url('/contact') }}">Contact</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Contact</h4>
                <div class="contact-item">
                    <i class="fa-solid fa-location-dot"></i>
                    <span>Abomey-Calavi, Bidossessi, Bénin</span>
                </div>
                <div class="contact-item">
                    <i class="fa-solid fa-phone"></i>
                    <span>+229 01 66 44 92 32</span>
                </div>
                <div class="contact-item">
                    <i class="fa-solid fa-phone"></i>
                    <span>+229 01 97 22 48 87</span>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} DONAMI-CHRIST. Tous droits réservés. |
                Conçu par <a href="https://www.facebook.com/innoserveo" target="_blank">innoServeo</a>
            </p>
        </div>
    </div>
</footer>

<script>
    // Navbar scroll effect
    window.addEventListener('scroll', () => {
        document.getElementById('mainNav').classList.toggle('scrolled', window.scrollY > 50);
    });
</script>

@stack('scripts')
</body>
</html>