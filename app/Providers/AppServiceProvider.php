<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

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
        RateLimiter::for('test', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->user()?->id ?: $request->ip());
        });

        // Batch requests
        RateLimiter::for('batch', function(Request $request) {
            return Limit::perHour(50);
        });

        // Individual requests of other API endpoints
        RateLimiter::for('individuals', function(Request $request) {
            return Limit::perHour(3600);
        });

        // Requests for specific user
        RateLimiter::for('updateUser', function (Request $request) {
            return Limit::perHour(40000);
        });

    }
}
