<!DOCTYPE html>
<html lang="fr" class="carbon">
<head>
    <meta charset="UTF-8">
    <title>Confirmation d'email</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- THEME INIT (DayNight) --}}
    <script>
        if (localStorage.getItem("daynight-theme") === "carbon") {
            document.documentElement.classList.add("carbon");
        }
    </script>

    {{-- CSS DayNight --}}
    <link rel="stylesheet" href="{{ asset('assets/admin/templatemo-daynight-style.css') }}">
    
    <style>
        /* Petit ajout pour centrer le message de confirmation proprement */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background-color: #f0f2f5;
        }
        .confirm-box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            max-width: 500px;
            text-align: center;
            color: #333;
        }
    </style>
</head>

<body>
    <div class="confirm-box">
        {{ $slot }}
    </div>

    {{-- JS DayNight --}}
    <script src="{{ asset('assets/admin/templatemo-daynight-script.js') }}"></script>
</body>
</html>