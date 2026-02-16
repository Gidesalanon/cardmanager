<!DOCTYPE html>
<html lang="fr" class="carbon">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Authentification')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- THEME INIT (DayNight) --}}
    <script>
        if (localStorage.getItem("daynight-theme") === "carbon") {
            document.documentElement.classList.add("carbon");
        }
    </script>

    {{-- CSS DayNight --}}
    <link rel="stylesheet" href="{{ asset('assets/admin/templatemo-daynight-style.css') }}">
</head>

<body>

    {{-- PAGE CONTENT --}}
    @yield('content')

    {{-- JS DayNight --}}
    <script src="{{ asset('assets/admin/templatemo-daynight-script.js') }}"></script>

</body>
</html>
