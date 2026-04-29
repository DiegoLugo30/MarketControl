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
        // Belt-and-suspenders: if APP_URL is https, force the scheme on every
        // generated URL. This covers edge cases where the proxy headers are not
        // yet available (e.g. console commands, queue workers).
        if (str_starts_with(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        require_once app_path('Helpers/BranchHelper.php');
    }
}
