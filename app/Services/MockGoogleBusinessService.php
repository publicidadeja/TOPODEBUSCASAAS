<?php

namespace App\Services;

class MockGoogleBusinessService extends GoogleBusinessService 
{
    protected $business;

    public function __construct($business = null) 
    {
        $this->business = $business;
    }

    // Sobrescreve o método de importação mantendo a mesma assinatura
    public function importBusinesses($user)
    {
        // Retorna o mesmo formato que a API real retornaria
        return [
            'success' => true,
            'count' => 1,
            'message' => 'Negócio importado com sucesso'
        ];
    }

    // Simula dados de insights
    public function getBusinessInsights($businessId, $dateRange = '30daysAgo')
    {
        return [
            'views' => [
                'total' => rand(1000, 5000),
                'previous' => rand(800, 4000),
                'trend' => rand(-10, 30)
            ],
            'clicks' => [
                'total' => rand(100, 1000),
                'previous' => rand(80, 800),
                'trend' => rand(-10, 30)
            ],
            'calls' => [
                'total' => rand(10, 100),
                'previous' => rand(8, 80),
                'trend' => rand(-10, 30)
            ],
            'direction_requests' => [
                'total' => rand(50, 200),
                'previous' => rand(40, 160),
                'trend' => rand(-10, 30)
            ]
        ];
    }

    // Simula dados de concorrentes
    public function getCompetitors($business)
    {
        return [
            [
                'name' => 'Concorrente A',
                'distance' => '1.2km',
                'rating' => 4.5,
                'reviews' => 120,
                'insights' => [
                    'views' => rand(800, 4000),
                    'clicks' => rand(80, 800)
                ]
            ],
            [
                'name' => 'Concorrente B',
                'distance' => '0.8km',
                'rating' => 4.2,
                'reviews' => 85,
                'insights' => [
                    'views' => rand(800, 4000),
                    'clicks' => rand(80, 800)
                ]
            ]
        ];
    }

    // Simula análise de tendências
    public function getTrendAnalysis($business)
    {
        return [
            'trends' => [
                [
                    'keyword' => 'delivery ' . $business->segment,
                    'volume' => rand(1000, 5000),
                    'trend' => '+15%'
                ],
                [
                    'keyword' => $business->segment . ' próximo',
                    'volume' => rand(500, 2000),
                    'trend' => '+8%'
                ]
            ],
            'suggestions' => [
                [
                    'title' => 'Aumente sua presença online',
                    'description' => 'Adicione mais fotos do seu estabelecimento',
                    'impact' => 'Alto'
                ],
                [
                    'title' => 'Responda às avaliações',
                    'description' => 'Há 5 avaliações sem resposta',
                    'impact' => 'Médio'
                ]
            ]
        ];
    }

    // Simula dados de automação
    public function getAutomationMetrics($business)
    {
        return [
            'posts' => [
                'scheduled' => rand(5, 15),
                'published' => rand(20, 50),
                'engagement' => rand(100, 500)
            ],
            'responses' => [
                'automatic' => rand(10, 30),
                'average_time' => '5 minutos',
                'satisfaction' => '95%'
            ]
        ];
    }
}