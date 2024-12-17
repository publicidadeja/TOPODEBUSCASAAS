<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GooglePlacesTest extends TestCase
{
    /** @test */
    public function can_search_nearby_places()
    {
        $response = $this->get('/api/places/nearby', [
            'lat' => -23.550520,
            'lng' => -46.633308,
            'radius' => 1000
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'results',
                     'status'
                 ]);
    }

    /** @test */
    public function can_get_place_details()
    {
        $response = $this->get('/api/places/details', [
            'place_id' => 'ChIJ9TmAVZhZzpQRfMZCQlzG3tQ' // Use um place_id válido
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'result',
                     'status'
                 ]);
    }

    /** @test */
    public function can_get_autocomplete_suggestions()
    {
        $response = $this->get('/api/places/autocomplete', [
            'input' => 'Avenida Paulista'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'predictions',
                     'status'
                 ]);
    }
}