<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\GoogleBusinessService;

class GoogleBusinessServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(GoogleBusinessService::class, function ($app) {
            return new GoogleBusinessService();
        });
    }

    public function boot()
    {
        //
    }
}