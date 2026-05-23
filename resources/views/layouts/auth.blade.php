<!DOCTYPE html>
<html lang="fr" class="carbon">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Authentification') — {{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" type="image/png" href="{{ asset('assets/web/images/favicon.png') }}">

    <script>
        if (localStorage.getItem("daynight-theme") === "carbon") {
            document.documentElement.classList.add("carbon");
        }
    </script>

    <link rel="stylesheet" href="{{ asset('assets/admin/templatemo-daynight-style.css') }}">

    <style>
        /* ===== SURCHARGE DORÉE — AUTH PAGES ===== */

        /* Logo icon — remplace le bleu par du doré */
        .logo-icon {
            background: linear-gradient(135deg, #c9a84c, #f0d080) !important;
            border-radius: 10px !important;
        }

        /* Nom de l'app dans le logo */
        .login-logo span {
            color: #c9a84c !important;
        }

        /* Titre de la page (Connexion, Inscription...) */
        .login-title {
            color: #c9a84c !important;
        }

        /* Bouton primaire → dégradé doré */
        .btn.btn-primary,
        button.btn-primary,
        input[type="submit"].btn-primary {
            background: linear-gradient(135deg, #c9a84c, #f0d080) !important;
            color: #0a0a0a !important;
            border: none !important;
            font-weight: 700 !important;
            transition: all 0.3s !important;
        }
        .btn.btn-primary:hover,
        button.btn-primary:hover {
            background: linear-gradient(135deg, #f0d080, #c9a84c) !important;
            box-shadow: 0 6px 20px rgba(201,168,76,0.45) !important;
            transform: translateY(-1px);
            color: #0a0a0a !important;
        }

        /* Bouton secondaire (Google) */
        .btn.btn-secondary,
        a.btn-secondary {
            border-color: rgba(201,168,76,0.3) !important;
            color: #c9a84c !important;
        }
        .btn.btn-secondary:hover,
        a.btn-secondary:hover {
            background: rgba(201,168,76,0.08) !important;
            border-color: rgba(201,168,76,0.6) !important;
            color: #f0d080 !important;
        }

        /* Inputs focus */
        .form-input:focus {
            border-color: #c9a84c !important;
            box-shadow: 0 0 0 3px rgba(201,168,76,0.12) !important;
            outline: none !important;
        }

        /* Tous les liens → dorés */
        .login-footer a,
        .form-footer a,
        .login-container a:not(.btn) {
            color: #c9a84c !important;
            text-decoration: none;
        }
        .login-footer a:hover,
        .form-footer a:hover,
        .login-container a:not(.btn):hover {
            color: #f0d080 !important;
        }

        /* Divider texte */
        .login-divider span {
            color: #a0998a !important;
        }

        /* Checkbox accent */
        input[type="checkbox"] {
            accent-color: #c9a84c !important;
        }

        /* Password toggle hover */
        .password-toggle:hover svg {
            stroke: #c9a84c !important;
        }

        /* Alert success → doré */
        .alert-success {
            background: rgba(201,168,76,0.08) !important;
            border-color: rgba(201,168,76,0.4) !important;
            color: #c9a84c !important;
        }
    </style>
</head>
<body>

    @yield('content')

    <script src="{{ asset('assets/admin/templatemo-daynight-script.js') }}"></script>

</body>
</html>