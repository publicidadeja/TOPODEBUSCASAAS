<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Business;
use App\Services\MockGoogleBusinessService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BusinessControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_shows_business_with_mock_data()
    {
        $user = User::factory()->create();
        $business = Business::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->get(route('business.index'));

        $response->assertStatus(200)
            ->assertViewHas('businesses')
            ->assertSee($business->name)
            ->assertSee('Modo de Demonstração'); // Verifica se mostra o aviso de dados simulados
    }

    public function test_show_displays_mock_business_data()
    {
        $user = User::factory()->create();
        $business = Business::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->get(route('business.show', $business));

        $response->assertStatus(200)
            ->assertViewHas(['business', 'googleData', 'insights']);
    }
}