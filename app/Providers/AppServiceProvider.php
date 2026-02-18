<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\VerifyEmail; // Import important
use Illuminate\Notifications\Messages\MailMessage; // Import important
use Illuminate\Support\Facades\Lang;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Personnalisation du mail de vérification
        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new MailMessage)
                ->subject('Vérification de votre adresse e-mail - DONAMI CHRIST') 
                ->greeting('Bonjour !') 
                ->line('Merci de vous être inscrit sur notre plateforme.')
                ->line('Pour finaliser votre inscription et activer votre compte, veuillez cliquer sur le bouton ci-dessous :')
                ->action('Vérifier mon e-mail', $url)
                ->line('Si vous n\'avez pas créé de compte, aucune action supplémentaire n\'est requise.')
                ->salutation('Cordialement, l\'équipe DONAMI CHRIST');

                
        });
    }
}