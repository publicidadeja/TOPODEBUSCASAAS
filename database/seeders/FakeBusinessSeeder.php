<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\User;
use Illuminate\Database\Seeder;

class FakeBusinessSeeder extends Seeder
{
    public function run()
    {
        // Encontra o usuário admin ou cria um novo se não existir
        $user = User::where('email', 'admin@example.com')->first();
        
        if (!$user) {
            $user = User::create([
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
        }

        // Cria o negócio fictício
        $business = Business::create([
            'user_id' => $user->id,
            'name' => 'Café Aroma Brasileiro',
            'segment' => 'Cafeteria',
            'address' => 'Rua das Flores, 123 - Jardins, São Paulo - SP, 01410-000',
            'phone' => '(11) 3456-7890',
            'website' => 'https://cafearomabrasileiro.com.br',
            'description' => 'Cafeteria artesanal especializada em grãos selecionados, ambiente acolhedor e experiência única.',
            'is_verified' => true,
            'status' => 'active',
            'settings' => [
                'notifications' => [
                    'views' => true,
                    'clicks' => true,
                    'calls' => true,
                    'frequency' => 'daily',
                    'variation_threshold' => 10
                ],
                'business_hours' => [
                    'monday' => ['09:00-18:00'],
                    'tuesday' => ['09:00-18:00'],
                    'wednesday' => ['09:00-18:00'],
                    'thursday' => ['09:00-18:00'],
                    'friday' => ['09:00-18:00'],
                    'saturday' => ['10:00-16:00'],
                    'sunday' => ['closed']
                ],
                'social_media' => [
                    'facebook' => 'https://facebook.com/cafearomabrasileiro',
                    'instagram' => 'https://instagram.com/cafearomabrasileiro',
                    'twitter' => 'https://twitter.com/cafearomabr'
                ],
                'location' => [
                    'latitude' => -23.561684,
                    'longitude' => -46.655866,
                ],
                'attributes' => [
                    'wifi' => true,
                    'wheelchair_accessible' => true,
                    'outdoor_seating' => true,
                    'accepts_credit_cards' => true,
                    'parking' => true,
                    'delivery' => true,
                    'takeout' => true
                ]
            ],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Adiciona algumas fotos fictícias
        $photos = [
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
            ]
        ];

        foreach ($photos as $photo) {
            $business->photos()->create($photo);
        }

        // Adiciona algumas avaliações fictícias
        $reviews = [
            [
                'author' => 'Maria Silva',
                'rating' => 5,
                'comment' => 'Melhor café da região! Atendimento impecável e ambiente perfeito para trabalhar.',
                'created_at' => now()->subDays(5),
            ],
            [
                'author' => 'João Santos',
                'rating' => 4,
                'comment' => 'Ótimo ambiente para trabalhar. WiFi excelente e café delicioso.',
                'created_at' => now()->subDays(10),
            ],
            [
                'author' => 'Ana Paula',
                'rating' => 5,
                'comment' => 'Adorei o espaço! Café maravilhoso e atendimento nota 10.',
                'created_at' => now()->subDays(15),
            ]
        ];

        foreach ($reviews as $review) {
            $business->reviews()->create($review);
        }
    }
}