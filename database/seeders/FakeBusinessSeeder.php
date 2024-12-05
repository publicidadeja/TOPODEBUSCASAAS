<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Business;
use App\Models\User;
use Carbon\Carbon;

class FakeBusinessSeeder extends Seeder
{
    public function run()
    {
        // Primeiro usuário ou cria um novo se não existir
        $user = User::first() ?? User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // Criar negócio fictício com dados completos
        Business::create([
            'user_id' => $user->id,
            'name' => 'Café Aroma Brasileiro',
            'segment' => 'Cafeteria',
            'description' => 'Cafeteria artesanal com grãos selecionados e ambiente acolhedor. Oferecemos uma experiência única com café de alta qualidade, bolos caseiros e atendimento personalizado.',
            'address' => 'Rua das Flores, 123 - Jardins',
            'city' => 'São Paulo',
            'state' => 'SP',
            'postal_code' => '01410-000',
            'phone' => '(11) 3456-7890',
            'website' => 'https://cafearomabrasileiro.com.br',
            'google_business_id' => 'fake_' . uniqid(),
            'settings' => [
                'business_hours' => [
                    'monday' => ['09:00-18:00'],
                    'tuesday' => ['09:00-18:00'],
                    'wednesday' => ['09:00-18:00'],
                    'thursday' => ['09:00-18:00'],
                    'friday' => ['09:00-18:00'],
                    'saturday' => ['10:00-16:00'],
                    'sunday' => ['closed'],
                ],
                'social_media' => [
                    'facebook' => 'https://facebook.com/cafearomabrasileiro',
                    'instagram' => 'https://instagram.com/cafearomabrasileiro',
                    'twitter' => 'https://twitter.com/cafearomabr',
                ],
                'location' => [
                    'latitude' => -23.561684,
                    'longitude' => -46.655866,
                ],
                'categories' => ['Cafeteria', 'Restaurante', 'Café Gourmet'],
                'services' => [
                    'Café Expresso',
                    'Café Coado',
                    'Bolos Caseiros',
                    'Salgados',
                    'Café da Manhã',
                    'Brunch',
                ],
                'attributes' => [
                    'wifi' => true,
                    'wheelchair_accessible' => true,
                    'outdoor_seating' => true,
                    'accepts_credit_cards' => true,
                    'parking' => true,
                ],
                'rating' => [
                    'average' => 4.7,
                    'total_reviews' => 128,
                    'reviews' => [
                        [
                            'author' => 'Maria Silva',
                            'rating' => 5,
                            'comment' => 'Melhor café da região! Atendimento impecável.',
                            'date' => '2024-01-15',
                        ],
                        [
                            'author' => 'João Santos',
                            'rating' => 4,
                            'comment' => 'Ótimo ambiente para trabalhar. WiFi excelente.',
                            'date' => '2024-01-10',
                        ],
                    ],
                ],
                'photos' => [
                    [
                        'url' => '/images/fake/cafe-exterior.jpg',
                        'type' => 'EXTERIOR',
                        'caption' => 'Fachada do Café',
                    ],
                    [
                        'url' => '/images/fake/cafe-interior.jpg',
                        'type' => 'INTERIOR',
                        'caption' => 'Ambiente interno',
                    ],
                    [
                        'url' => '/images/fake/cafe-produtos.jpg',
                        'type' => 'PRODUCT',
                        'caption' => 'Nossos cafés especiais',
                    ],
                ],
            ],
            'last_sync' => now(),
            'status' => 'active',
        ]);
    }
}