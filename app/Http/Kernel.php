<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * Middlewares globaux.
     */
    protected $middleware = [
        \App\Http\Middleware\TrustProxies::class,
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ];

    /**
     * Groupes de middlewares.
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * ALIAS DES MIDDLEWARES (Laravel 12)
     */
    protected $middlewareAliases = [
        'auth'      => \App\Http\Middleware\Authenticate::class,
        'guest'     => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'verified'  => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'throttle'  => \Illuminate\Routing\Middleware\ThrottleRequests::class,

        // LES MIDDLEWARES CUSTOM
        'admin'     => \App\Http\Middleware\AdminMiddleware::class,
        'role'      => \App\Http\Middleware\RoleMiddleware::class,
        'ecole'     => \App\Http\Middleware\EcoleMiddleware::class,
    ];
}
