<?php

namespace App\Providers;

use App\Services\GoogleAuthService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GoogleAuthService::class, function ($app) {
            return new GoogleAuthService();
        });
    }

    public function boot()
{
    Blade::component('toggle-switch', \App\View\Components\ToggleSwitch::class);
}
}