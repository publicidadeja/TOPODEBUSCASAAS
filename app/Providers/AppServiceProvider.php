<?php

namespace App\Providers;

use App\Services\GoogleAuthService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GoogleAuthService::class, function ($app) {
            return new GoogleAuthService();
        });
    }

    public function boot(): void
    {
        //
    }
}