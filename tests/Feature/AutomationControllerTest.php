<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Http\Controllers\AutomationController;
use App\Services\SerperService;
use App\Services\GeminiService;
use Mockery;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AutomationControllerTest extends TestCase
{
    use RefreshDatabase;
    
    protected $automationController;
    protected $serperMock;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock do SerperService
        $this->serperMock = Mockery::mock(SerperService::class);
        $geminiMock = Mockery::mock(GeminiService::class);
        
        // Instancia o controller com os mocks
        $this->automationController = new AutomationController(
            $geminiMock,
            $this->serperMock
        );
    }

    /** @test */
    public function test_get_static_events_for_restaurant()
    {
        $events = $this->automationController->getStaticEvents('restaurante', 12);
        
        $this->assertIsArray($events);
        $this->assertCount(2, $events);
        $this->assertEquals(['Confraternizações', 'Cardápio Especial de Natal'], $events);
    }

    /** @test */
    public function test_get_dynamic_events_when_static_not_found()
    {
        // Simula resposta do Serper
        $mockResponse = [
            'organic' => [
                [
                    'title' => 'Evento Importante de Teste 2024',
                    'snippet' => 'Descrição do evento'
                ],
                [
                    'title' => 'Outro Evento Relevante',
                    'snippet' => 'Mais uma descrição'
                ]
            ]
        ];

        $this->serperMock
            ->shouldReceive('search')
            ->once()
            ->andReturn($mockResponse);

        $events = $this->automationController->getDynamicEvents('segmento_novo', 1);
        
        $this->assertIsArray($events);
        $this->assertNotEmpty($events);
    }

    /** @test */
    public function test_get_seasonal_events_combines_static_and_dynamic()
    {
        // Mock resposta do Serper para eventos dinâmicos
        $mockResponse = [
            'organic' => [
                [
                    'title' => 'Evento Dinâmico 2024',
                    'snippet' => 'Descrição'
                ]
            ]
        ];

        $this->serperMock
            ->shouldReceive('search')
            ->once()
            ->andReturn($mockResponse);

        $events = $this->automationController->getSeasonalEventsBySegment('segmento_teste', 1);
        
        $this->assertIsArray($events);
    }

    /** @test */
    public function test_handles_serper_service_error_gracefully()
    {
        $this->serperMock
            ->shouldReceive('search')
            ->once()
            ->andThrow(new \Exception('API Error'));

        $events = $this->automationController->getSeasonalEventsBySegment('segmento_teste', 1);
        
        $this->assertIsArray($events);
        $this->assertEmpty($events);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}