<!DOCTYPE html>
<html lang="fr" class="carbon">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Admin')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
        // Prevent flash of white in dark mode - runs before CSS/page render
        if (localStorage.getItem('daynight-theme') === 'carbon') {
            document.documentElement.classList.add('carbon');
        }
    </script>

    {{-- CSS --}}
    <link rel="stylesheet" href="{{ asset('assets/admin/templatemo-daynight-style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

    <div class="app-container">

        {{-- TOP NAV --}}
        @include('admin.partials.topnav')

        {{-- CONTENT --}}
        <main class="main-content">
            @yield('content')
        </main>

        {{-- FOOTER --}}
        <footer class="footer">
            <p>© {{ date('Y') }} {{ config('app.name') }} | Admin</p>
        </footer>

    </div>

    <script src="{{ asset('assets/admin/templatemo-daynight-script.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @stack('scripts')

</body>

</html>
