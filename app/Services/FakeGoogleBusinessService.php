<?php

namespace App\Services;

class FakeGoogleBusinessService
{
    public function getBusinessData($businessId)
    {
        // Simula dados que viriam da API do Google Meu Negócio
        return [
            'name' => 'Café Aroma Brasileiro',
            'description' => 'Cafeteria artesanal com grãos selecionados e ambiente acolhedor.',
            'address' => [
                'streetAddress' => 'Rua das Flores, 123',
                'addressLocality' => 'São Paulo',
                'addressRegion' => 'SP',
                'postalCode' => '01410-000',
            ],
            'location' => [
                'latitude' => -23.561684,
                'longitude' => -46.655866,
            ],
            'phone' => '(11) 3456-7890',
            'website' => 'https://cafearomabrasileiro.com.br',
            'businessHours' => [
                'monday' => ['09:00-18:00'],
                'tuesday' => ['09:00-18:00'],
                'wednesday' => ['09:00-18:00'],
                'thursday' => ['09:00-18:00'],
                'friday' => ['09:00-18:00'],
                'saturday' => ['10:00-16:00'],
                'sunday' => ['closed'],
            ],
            'photos' => [
                [
                    'url' => '/images/fake/cafe-exterior.jpg',
                    'type' => 'EXTERIOR',
                ],
                [
                    'url' => '/images/fake/cafe-interior.jpg',
                    'type' => 'INTERIOR',
                ],
                [
                    'url' => '/images/fake/cafe-produtos.jpg',
                    'type' => 'PRODUCT',
                ],
            ],
            'reviews' => [
                [
                    'author' => 'Maria Silva',
                    'rating' => 5,
                    'comment' => 'Melhor café da região! Atendimento impecável.',
                    'createTime' => '2024-01-15',
                ],
                [
                    'author' => 'João Santos',
                    'rating' => 4,
                    'comment' => 'Ótimo ambiente para trabalhar. WiFi excelente.',
                    'createTime' => '2024-01-10',
                ],
            ],
            'metrics' => [
                'views' => [
                    'total' => random_int(1000, 5000),
                    'lastWeek' => random_int(100, 500),
                ],
                'clicks' => [
                    'total' => random_int(500, 2000),
                    'lastWeek' => random_int(50, 200),
                ],
                'calls' => [
                    'total' => random_int(100, 500),
                    'lastWeek' => random_int(10, 50),
                ],
            ],
        ];
    }

    public function getInsights($businessId, $startDate = null, $endDate = null)
    {
        // Simula insights/métricas que viriam da API do Google
        return [
            'dailyMetrics' => $this->generateDailyMetrics($startDate, $endDate),
            'totalViews' => random_int(5000, 10000),
            'totalClicks' => random_int(2000, 5000),
            'totalCalls' => random_int(500, 1000),
            'searchKeywords' => [
                ['keyword' => 'café artesanal', 'searches' => random_int(100, 500)],
                ['keyword' => 'melhor café são paulo', 'searches' => random_int(50, 200)],
                ['keyword' => 'café gourmet jardins', 'searches' => random_int(30, 150)],
            ],
        ];
    }

    private function generateDailyMetrics($startDate = null, $endDate = null)
    {
        $startDate = $startDate ?? now()->subDays(30);
        $endDate = $endDate ?? now();
        $days = [];

        for ($date = clone $startDate; $date <= $endDate; $date->addDay()) {
            $days[] = [
                'date' => $date->format('Y-m-d'),
                'views' => random_int(50, 200),
                'clicks' => random_int(20, 80),
                'calls' => random_int(5, 20),
            ];
        }

        return $days;
    }

    public function register()
{
    $this->app->singleton('google.business', function ($app) {
        return new FakeGoogleBusinessService();
    });
}
}