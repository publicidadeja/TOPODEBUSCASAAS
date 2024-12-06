<?php

namespace Database\Seeders;

use App\Models\BusinessAnalytics;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BusinessAnalyticsSeeder extends Seeder
{
    public function run()
    {
        $business = \App\Models\Business::first();
        
        if (!$business) {
            return;
        }

        // Adiciona métricas dos últimos 30 dias
        $startDate = Carbon::now()->subDays(30);
        
        for ($i = 0; $i <= 30; $i++) {
            BusinessAnalytics::create([
                'business_id' => $business->id,
                'date' => $startDate->copy()->addDays($i),
                'views' => rand(50, 200),
                'clicks' => rand(20, 80),
                'calls' => rand(5, 20),
                'rating' => rand(40, 50) / 10, // Gera números entre 4.0 e 5.0
            ]);
        }
    }
}