<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\GoogleBusinessService;
use App\Services\MockGoogleBusinessService;

class GoogleServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(GoogleBusinessService::class, function ($app) {
            // Se não tiver token da API do Google, usa o Mock
            if (!config('services.google.api_key')) {
                return new MockGoogleBusinessService();
            }
            
            return new GoogleBusinessService();
        });
    }
}