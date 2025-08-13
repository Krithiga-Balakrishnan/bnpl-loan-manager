<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

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
        //
        Carbon::setLocale('en');
        Carbon::setToStringFormat('Y-m-d H:i');
        date_default_timezone_set('Asia/Colombo'); // or your preferred timezone
    }
}
