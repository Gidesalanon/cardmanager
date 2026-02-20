<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - {{ config('app.name') }} | Ecole</title>

    <script>
        if (localStorage.getItem('daynight-theme') === 'carbon') {
            document.documentElement.classList.add('carbon');
        }
    </script>

    <link rel="stylesheet" href="{{ asset('assets/admin/templatemo-daynight-style.css') }}">
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>

<body>

    <!-- Mobile Menu Overlay -->
    <div class="mobile-menu-overlay"></div>

    <!-- Mobile Menu -->
    <div class="mobile-menu">
        <div class="mobile-menu-header">
            <a href="" class="logo">
                <div class="logo-icon">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path
                            d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                    </svg>
                </div>
                {{ config('app.name') }}
            </a>
            <button class="mobile-menu-close" onclick="closeMobileMenu()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
            </button>
        </div>

        <!-- MENU MOBILE IDENTIQUE -->
        @include('school.partials.mobile-menu')
        {!! str_replace('index.html', route('school.dashboard'), '') !!}
    </div>

    <div class="app-container">

        <!-- TOP NAV -->
        @include('school.partials.topnav')

        <!-- MAIN CONTENT -->
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <main class="main-content">
            @yield('content')
        </main>

        <!-- FOOTER -->
        <footer class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }} | Ecole. Designed by
                <a href="https://www.facebook.com/innoserveo" target="_blank" rel="nofollow">innoServeo</a>
            </p>
        </footer>
    </div>
    <script src="{{ asset('assets/admin/templatemo-daynight-script.js') }}"></script>
    @stack('scripts')
</body>

</html>
