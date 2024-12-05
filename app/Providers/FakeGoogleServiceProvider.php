<?php

namespace App\Providers;

use App\Services\FakeGoogleBusinessService;
use Illuminate\Support\ServiceProvider;

class FakeGoogleServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(FakeGoogleBusinessService::class, function ($app) {
            return new FakeGoogleBusinessService();
        });
    }

    public function boot()
    {
        //
    }
}