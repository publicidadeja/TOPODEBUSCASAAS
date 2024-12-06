<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\User;
use App\Models\BusinessAnalytics;
use Carbon\Carbon;
use Illuminate\Database\Seeder;


class FakeBusinessSeeder extends Seeder
{
    public function run()
    {
        // Garante que existe um usuário
        $user = User::firstOrCreate(
            ['email' => 'teste@exemplo.com'],
            [
                'name' => 'Usuário Teste',
                'password' => bcrypt('password'),
            ]
        );

        // Cria o negócio fictício
        $business = Business::create([
            'user_id' => $user->id,
            'name' => 'Café Aroma Brasileiro',
            'segment' => 'Cafeteria',
            'description' => 'O melhor café artesanal da região, com ambiente acolhedor e wifi grátis para nossos clientes.',
            'address' => 'Rua das Flores, 123 - Centro, São Paulo - SP',
            'phone' => '(11) 98765-4321',
            'website' => 'https://cafearomabrasileiro.com.br',
            'is_verified' => true,
            'status' => 'active',
            'rating' => 4.7,
            'review_count' => 3,
            'settings' => [
                'notifications' => [
                    'views' => true,
                    'clicks' => true,
                    'calls' => true,
                    'frequency' => 'daily',
                    'variation_threshold' => 10
                ],
                'business_hours' => [
                    'monday' => ['08:00-18:00'],
                    'tuesday' => ['08:00-18:00'],
                    'wednesday' => ['08:00-18:00'],
                    'thursday' => ['08:00-18:00'],
                    'friday' => ['08:00-18:00'],
                    'saturday' => ['09:00-15:00'],
                    'sunday' => ['closed']
                ],
                'social_media' => [
                    'facebook' => 'https://facebook.com/cafearomabrasileiro',
                    'instagram' => 'https://instagram.com/cafearomabrasileiro',
                    'twitter' => 'https://twitter.com/cafearomabr'
                ],
                'location' => [
                    'latitude' => -23.550520,
                    'longitude' => -46.633308,
                ],
                'attributes' => [
                    'wifi' => true,
                    'wheelchair_accessible' => true,
                    'outdoor_seating' => true,
                    'parking' => true,
                    'delivery' => true,
                    'takeout' => true
                ]
            ]
        ]);

        // Adiciona métricas dos últimos 30 dias
        $startDate = Carbon::now()->subDays(30);
for ($i = 0; $i <= 30; $i++) {
    BusinessAnalytics::create([
        'business_id' => $business->id,
        'date' => $startDate->copy()->addDays($i),
        'views' => rand(50, 200),
        'clicks' => rand(20, 80),
        'calls' => rand(5, 20),
        'website_visits' => rand(15, 50),
        'photo_views' => rand(30, 100)
    ]);
        }

        // Adiciona fotos
        $photos = [
            [
                'url' => '/images/fake/cafe-exterior.jpg',
                'type' => 'EXTERIOR',
                'caption' => 'Fachada do Café',
                'business_id' => $business->id
            ],
            [
                'url' => '/images/fake/cafe-interior.jpg',
                'type' => 'INTERIOR',
                'caption' => 'Ambiente interno',
                'business_id' => $business->id
            ],
            [
                'url' => '/images/fake/cafe-produtos.jpg',
                'type' => 'PRODUCT',
                'caption' => 'Nossos cafés especiais',
                'business_id' => $business->id
            ]
        ];

        foreach ($photos as $photo) {
            $business->photos()->create($photo);
        }

        // Adiciona avaliações
        $reviews = [
            [
                'author' => 'Maria Silva',
                'rating' => 5,
                'comment' => 'Excelente café! Ambiente muito agradável e atendimento impecável.',
                'business_id' => $business->id
            ],
            [
                'author' => 'João Santos',
                'rating' => 4,
                'comment' => 'Ótimo lugar para trabalhar. Wifi rápido e café delicioso.',
                'business_id' => $business->id
            ],
            [
                'author' => 'Ana Oliveira',
                'rating' => 5,
                'comment' => 'Os doces são maravilhosos! Super recomendo.',
                'business_id' => $business->id
            ]
        ];

        foreach ($reviews as $review) {
            $business->reviews()->create($review);
        }
    }
}