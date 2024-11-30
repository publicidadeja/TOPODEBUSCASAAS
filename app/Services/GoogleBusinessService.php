<?php

namespace App\Services;

use App\Models\Business;
use App\Models\BusinessAnalytics;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GoogleBusinessService
{
    public function updateAnalytics(Business $business)
    {
        try {
            Log::info("Iniciando atualização para {$business->name}");
            
            $metrics = $this->generateMockData();
            $today = Carbon::today();

            Log::info("Dados gerados:", $metrics);

            $existingAnalytics = BusinessAnalytics::where('business_id', $business->id)
                ->where('date', $today)
                ->first();

            if ($existingAnalytics) {
                Log::info("Atualizando registro existente");
                $existingAnalytics->update([
                    'views' => $metrics['views'],
                    'clicks' => $metrics['clicks'],
                    'calls' => $metrics['calls'],
                    'search_keywords' => $metrics['keywords'],
                    'user_locations' => $metrics['locations'],
                    'devices' => $metrics['devices']
                ]);
                $analytics = $existingAnalytics;
            } else {
                Log::info("Criando novo registro");
                $analytics = new BusinessAnalytics([
                    'business_id' => $business->id,
                    'date' => $today,
                    'views' => $metrics['views'],
                    'clicks' => $metrics['clicks'],
                    'calls' => $metrics['calls'],
                    'search_keywords' => $metrics['keywords'],
                    'user_locations' => $metrics['locations'],
                    'devices' => $metrics['devices']
                ]);
                $analytics->save();
            }

            Log::info("Analytics atualizados com sucesso para {$business->name}", [
                'business_id' => $business->id,
                'date' => $today,
                'analytics_id' => $analytics->id
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Erro detalhado ao atualizar analytics para {$business->name}", [
                'business_id' => $business->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e; // Vamos deixar o erro propagar para ver a mensagem completa
        }
    }

    private function generateMockData()
    {
        return [
            'views' => rand(50, 200),
            'clicks' => rand(10, 50),
            'calls' => rand(5, 20),
            'keywords' => [
                'restaurante próximo' => rand(10, 30),
                'melhor restaurante' => rand(5, 15),
                'delivery comida' => rand(8, 25),
                'restaurante barato' => rand(3, 12)
            ],
            'locations' => [
                'São Paulo' => rand(30, 100),
                'Guarulhos' => rand(10, 30),
                'Osasco' => rand(5, 20)
            ],
            'devices' => [
                'mobile' => rand(60, 150),
                'desktop' => rand(20, 60),
                'tablet' => rand(5, 15)
            ]
        ];
    }
}