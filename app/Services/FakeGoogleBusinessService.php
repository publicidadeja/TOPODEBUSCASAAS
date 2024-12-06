<?php

namespace App\Services;

use Carbon\Carbon;

class FakeGoogleBusinessService
{
    public function getBusinessData($businessId)
    {
        // Simula dados que viriam da API do Google Meu Negócio
        return [
            'name' => 'Café Aroma Brasileiro',
            'description' => 'Cafeteria artesanal especializada em grãos selecionados, ambiente acolhedor e experiência única. Oferecemos uma variedade de cafés especiais, bolos caseiros e um espaço perfeito para trabalho remoto.',
            'address' => [
                'streetAddress' => 'Rua das Flores, 123',
                'addressLocality' => 'São Paulo',
                'addressRegion' => 'SP',
                'postalCode' => '01410-000',
                'formatted' => 'Rua das Flores, 123 - Jardins, São Paulo - SP, 01410-000'
            ],
            'location' => [
                'latitude' => -23.561684,
                'longitude' => -46.655866,
            ],
            'contacts' => [
                'phone' => '(11) 3456-7890',
                'whatsapp' => '(11) 98765-4321',
                'email' => 'contato@cafearomabrasileiro.com.br'
            ],
            'website' => 'https://cafearomabrasileiro.com.br',
            'social_media' => [
                'facebook' => 'https://facebook.com/cafearomabrasileiro',
                'instagram' => 'https://instagram.com/cafearomabrasileiro',
                'twitter' => 'https://twitter.com/cafearomabr'
            ],
            'businessHours' => [
                'monday' => ['09:00-18:00'],
                'tuesday' => ['09:00-18:00'],
                'wednesday' => ['09:00-18:00'],
                'thursday' => ['09:00-18:00'],
                'friday' => ['09:00-18:00'],
                'saturday' => ['10:00-16:00'],
                'sunday' => ['closed'],
            ],
            'specialHours' => [
                [
                    'date' => '2024-12-24',
                    'hours' => ['09:00-14:00']
                ],
                [
                    'date' => '2024-12-25',
                    'hours' => ['closed']
                ]
            ],
            'categories' => [
                'primary' => 'Cafeteria',
                'additional' => ['Restaurante', 'Café Gourmet', 'Espaço de Trabalho']
            ],
            'attributes' => [
                'wifi' => true,
                'wheelchair_accessible' => true,
                'outdoor_seating' => true,
                'accepts_credit_cards' => true,
                'parking' => true,
                'delivery' => true,
                'takeout' => true
            ],
            'photos' => [
                [
                    'url' => '/images/fake/cafe-exterior.jpg',
                    'type' => 'EXTERIOR',
                    'caption' => 'Fachada do Café'
                ],
                [
                    'url' => '/images/fake/cafe-interior.jpg',
                    'type' => 'INTERIOR',
                    'caption' => 'Ambiente interno'
                ],
                [
                    'url' => '/images/fake/cafe-produtos.jpg',
                    'type' => 'PRODUCT',
                    'caption' => 'Nossos cafés especiais'
                ],
                [
                    'url' => '/images/fake/cafe-ambiente.jpg',
                    'type' => 'INTERIOR',
                    'caption' => 'Área de trabalho'
                ]
            ],
            'reviews' => [
                [
                    'author' => 'Maria Silva',
                    'rating' => 5,
                    'comment' => 'Melhor café da região! Atendimento impecável e ambiente perfeito para trabalhar.',
                    'createTime' => '2024-01-15',
                    'updateTime' => '2024-01-15',
                ],
                [
                    'author' => 'João Santos',
                    'rating' => 4,
                    'comment' => 'Ótimo ambiente para trabalhar. WiFi excelente e café delicioso.',
                    'createTime' => '2024-01-10',
                    'updateTime' => '2024-01-10',
                ]
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

    public function getMetrics($businessId)
    {
        // Gera métricas fictícias consistentes
        return [
            'views' => random_int(1000, 5000),
            'clicks' => random_int(500, 2000),
            'calls' => random_int(100, 500),
            'rating' => 4.7,
            'reviewCount' => 3
        ];
    }

    public function getInsights($businessId, $startDate = null, $endDate = null)
    {
        // Se não houver datas definidas, use os últimos 30 dias
        if (!$startDate) {
            $startDate = Carbon::now()->subDays(30)->format('Y-m-d');
        }
        if (!$endDate) {
            $endDate = Carbon::now()->format('Y-m-d');
        }

        return [
            'dailyMetrics' => $this->generateDailyMetrics($startDate, $endDate),
            'totalViews' => random_int(1000, 5000),
            'totalClicks' => random_int(500, 2000),
            'totalCalls' => random_int(100, 500),
            'searchKeywords' => [
                [
                    'keyword' => 'café artesanal',
                    'searches' => random_int(50, 200)
                ],
                [
                    'keyword' => 'melhor café da região',
                    'searches' => random_int(30, 150)
                ],
                [
                    'keyword' => 'café com wifi',
                    'searches' => random_int(20, 100)
                ],
                [
                    'keyword' => 'espaço para trabalhar',
                    'searches' => random_int(40, 180)
                ],
                [
                    'keyword' => 'café gourmet',
                    'searches' => random_int(25, 120)
                ]
            ],
            'performance' => [
                'rating' => 4.5,
                'totalReviews' => random_int(50, 200),
                'responseRate' => random_int(80, 100),
                'averageResponseTime' => '2 hours'
            ],
            'competitors' => [
                [
                    'name' => 'Café Concorrente 1',
                    'rating' => 4.2,
                    'reviews' => random_int(30, 150)
                ],
                [
                    'name' => 'Café Concorrente 2',
                    'rating' => 4.0,
                    'reviews' => random_int(20, 100)
                ]
            ]
        ];
    }
    protected function generateDailyMetrics($startDate, $endDate)
    {
        $metrics = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        while ($current <= $end) {
            $metrics[] = [
                'date' => $current->format('Y-m-d'),
                'views' => random_int(20, 100),
                'clicks' => random_int(5, 30),
                'calls' => random_int(1, 10),
                'direction_requests' => random_int(2, 15),
                'website_visits' => random_int(10, 50),
                'photo_views' => random_int(30, 150)
            ];

            $current->addDay();
        }

        return $metrics;
    }
}