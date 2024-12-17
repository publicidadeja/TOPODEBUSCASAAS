<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\GoogleBusinessService;
use App\Services\KeywordService;
use App\Services\AIAnalysisService;

class GoogleBusinessServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(GoogleBusinessService::class, function ($app) {
            return new GoogleBusinessService(
                $app->make(KeywordService::class),
                $app->make(AIAnalysisService::class)
            );
        });
    }

    public function boot()
    {
        //
    }
}