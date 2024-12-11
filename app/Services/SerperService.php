<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SerperService
{
    protected $apiKey;
    protected $apiEndpoint = 'https://google.serper.dev/search';

    public function __construct()
    {
        $this->apiKey = config('services.serper.key'); // Você precisará adicionar isso
    }

    public function search($query)
{
    try {
        $response = Http::withHeaders([
            'X-API-KEY' => $this->apiKey,
            'Content-Type' => 'application/json'
        ])->post($this->apiEndpoint, [
            'q' => $query,
            'gl' => 'br',
            'num' => 10,
            'type' => 'places', // Alterado para 'places' para buscar locais
            'search_type' => 'places' // Adicional para garantir busca de locais
        ]);

        if ($response->successful()) {
            return $this->formatPlacesResults($response->json());
        }

        throw new \Exception('Erro na busca: ' . $response->body());
    } catch (\Exception $e) {
        \Log::error('Erro na busca Serper: ' . $e->getMessage());
        throw $e;
    }
}

private function formatPlacesResults($data)
{
    $results = [];
    
    if (isset($data['places'])) {
        foreach ($data['places'] as $place) {
            $results[] = [
                'title' => $place['title'] ?? '',
                'location' => $place['address'] ?? 'Localização não disponível',
                'snippet' => $this->formatPlaceDescription($place),
                'rating' => $place['rating'] ?? null,
                'reviews' => $place['reviewsCount'] ?? null,
                'phone' => $place['phone'] ?? '',
                'website' => $place['website'] ?? ''
            ];
        }
    }
    
    return $results;
}

private function formatPlaceDescription($place)
{
    $description = [];
    
    if (!empty($place['address'])) {
        $description[] = $place['address'];
    }
    
    if (!empty($place['rating'])) {
        $description[] = "Avaliação: {$place['rating']}/5";
    }
    
    if (!empty($place['reviewsCount'])) {
        $description[] = "{$place['reviewsCount']} avaliações";
    }
    
    if (!empty($place['phone'])) {
        $description[] = "Tel: {$place['phone']}";
    }
    
    return implode(' • ', $description);
}

    
    private function calculateCompetitorScore($place)
    {
        $score = 0;
        
        // Pontuação baseada na avaliação
        if (isset($place['rating'])) {
            $score += ($place['rating'] * 2); // Máximo de 10 pontos
        }
        
        // Pontuação baseada no número de avaliações
        if (isset($place['reviews'])) {
            $score += min(($place['reviews'] / 100), 5); // Máximo de 5 pontos
        }
        
        // Normaliza o score para escala de 1-10
        $score = max(1, min(10, round($score)));
        
        return $score;
    }

private function formatResults($data)
{
    $results = [];
    
    if (isset($data['organic'])) {
        foreach ($data['organic'] as $item) {
            $results[] = [
                'title' => $item['title'] ?? '',
                'link' => $item['link'] ?? '',
                'snippet' => $item['snippet'] ?? '',
                'position' => $item['position'] ?? 0
            ];
        }
    }
    
    return $results;
}
}