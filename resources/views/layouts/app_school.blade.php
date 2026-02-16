<!DOCTYPE html>
<html lang="fr" class="carbon">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Ecole')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
        if (localStorage.getItem('daynight-theme') === 'carbon') {
            document.documentElement.classList.add('carbon');
        }
    </script>

    {{-- CSS --}}
    <link rel="stylesheet" href="{{ asset('assets/admin/templatemo-daynight-style.css') }}">

    {{-- ✅ ALPINE (AJOUTÉ — OBLIGATOIRE POUR TOAST & CONFIRM) --}}
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>

<body>

<div class="app-container">

    {{-- TOP NAV --}}
    @include('school.partials.topnav')

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

</body>
</html>
