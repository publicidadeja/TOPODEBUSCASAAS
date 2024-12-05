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

        // Criar negócio fictício
        Business::create([
            'user_id' => $user->id,
            'name' => 'Café Aroma Brasileiro',
            'segment' => 'Cafeteria',
            'description' => 'Cafeteria artesanal com grãos selecionados e ambiente acolhedor. Oferecemos uma experiência única com café de alta qualidade, bolos caseiros e atendimento personalizado.',
            'address' => 'Rua das Flores, 123 - São Paulo, SP',
            'phone' => '(11) 3456-7890',
            'website' => 'https://cafearomabrasileiro.com.br',
            'google_business_id' => 'fake_' . uniqid(),
        ]);
    }
}