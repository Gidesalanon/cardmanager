<!DOCTYPE html>
<html lang="fr" class="carbon">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'École') — {{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script>
        if (localStorage.getItem('daynight-theme') === 'carbon') {
            document.documentElement.classList.add('carbon');
        }
    </script>

    <link rel="stylesheet" href="{{ asset('assets/admin/templatemo-daynight-style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" type="image/png" href="{{ asset('assets/web/images/favicon.png') }}">

    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    @stack('styles')
</head>
<body>

    <!-- Mobile Menu Overlay -->
    <div class="mobile-menu-overlay" id="mobileOverlay" onclick="closeMobileMenu()"></div>

    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenuPanel">
        <div class="mobile-menu-header">
            <a href="{{ route('school.dashboard') }}" class="logo">
                <div class="logo-icon">
                    <img src="{{ asset('assets/web/images/logo-3.png') }}" alt="Logo"
                         style="width:100%; height:100%; object-fit:contain;">
                </div>
                {{ config('app.name') }}
            </a>
            <button class="mobile-menu-close" onclick="closeMobileMenu()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        @include('school.partials.mobile-menu')
    </div>

    <div class="app-container">

        @include('school.partials.topnav')

        @if (session('success'))
            <div class="main-content" style="padding-bottom:0; padding-top:1rem;">
                <div class="alert alert-success">{{ session('success') }}</div>
            </div>
        @endif

        @if (session('error'))
            <div class="main-content" style="padding-bottom:0; padding-top:1rem;">
                <div class="alert alert-danger">{{ session('error') }}</div>
            </div>
        @endif

        <main class="main-content">
            @yield('content')
        </main>

        <footer class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }} | Conçu par
                <a href="https://www.facebook.com/innoserveo" target="_blank" rel="nofollow">innoServeo</a>
            </p>
        </footer>

    </div>

    <script src="{{ asset('assets/admin/templatemo-daynight-script.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function toggleMobileMenu() {
            document.getElementById('mobileMenuPanel').classList.toggle('active');
            document.getElementById('mobileOverlay').classList.toggle('active');
        }
        function closeMobileMenu() {
            document.getElementById('mobileMenuPanel').classList.remove('active');
            document.getElementById('mobileOverlay').classList.remove('active');
        }
    </script>

    @stack('scripts')
</body>
</html>