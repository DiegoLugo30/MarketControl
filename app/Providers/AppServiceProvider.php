<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        // Forzar HTTPS en producción o cuando APP_URL usa HTTPS
        if ($this->app->environment('production') || str_starts_with(config('app.url'), 'https')) {
            URL::forceScheme('https');
        }

        // Cargar helpers globales
        require_once app_path('Helpers/BranchHelper.php');
    }
}
