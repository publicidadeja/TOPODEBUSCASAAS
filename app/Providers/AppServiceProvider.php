<?php

namespace App\Providers;

use App\Services\GoogleAuthService;
use App\Services\SerperService; // Adicione este use
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GoogleAuthService::class, function ($app) {
            return new GoogleAuthService();
        });

        // Adicione este trecho para registrar o SerperService
        $this->app->singleton(SerperService::class, function ($app) {
            return new SerperService();
        });
    }

    public function boot()
    {
        Blade::component('toggle-switch', \App\View\Components\ToggleSwitch::class);
    }
}