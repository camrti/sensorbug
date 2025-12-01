<?php

namespace App\Providers;

use App\Models\TrackingInterestAssignment;
use App\Observers\TrackingInterestAssignmentObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        
        // Regole password: minimo 8 caratteri, almeno 1 maiuscola, 1 numero, 1 simbolo
        Password::defaults(function () {
            return Password::min(8)
                ->letters()      // Richiede lettere
                ->mixedCase()    // Richiede maiuscole
                ->numbers()      // Richiede almeno 1 numero
                ->symbols();     // Richiede almeno 1 simbolo (!@#$%^&*)
        });
    }
}
