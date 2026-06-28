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
        // បង្ខំឱ្យ Laravel ហៅគ្រប់យ៉ាងជា HTTPS ទាំងអស់នៅលើ Production
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}