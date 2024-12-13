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
            \Log::info('Iniciando busca Serper', ['query' => $query]);
    
            $response = Http::withHeaders([
                'X-API-KEY' => $this->apiKey,
                'Content-Type' => 'application/json'
            ])->post($this->apiEndpoint, [
                'q' => $query,
                'gl' => 'br',
                'num' => 10,
                'type' => 'places',
                'search_type' => 'places',
                'hl' => 'pt-br'
            ]);
    
            if ($response->successful()) {
                $data = $response->json();
                
                // Verifica se há resultados de places
                if (!isset($data['places']) || empty($data['places'])) {
                    \Log::warning('Nenhum resultado encontrado', ['query' => $query]);
                    return [];
                }
    
                // Formata e processa os resultados
                $competitors = [];
                foreach ($data['places'] as $place) {
                    // Verifica se tem dados mínimos necessários
                    if (empty($place['title'])) {
                        continue;
                    }
    
                    $competitor = [
                        'title' => $place['title'],
                        'name' => $place['title'],
                        'location' => $place['address'] ?? '',
                        'address' => $place['address'] ?? '',
                        'rating' => floatval($place['rating'] ?? 0),
                        'reviews' => intval($place['reviewsCount'] ?? 0),
                        'phone' => $place['phoneNumber'] ?? '',
                        'website' => $place['website'] ?? '',
                        'image_url' => $place['thumbnailUrl'] ?? '',
                        'categories' => $place['categories'] ?? [],
                        'hours' => $place['hours'] ?? [],
                        'description' => $this->generateDescription($place),
                        'metrics' => [
                            'rating_score' => $this->calculateRatingScore($place),
                            'popularity_score' => $this->calculatePopularityScore($place),
                            'online_presence_score' => $this->calculateOnlinePresenceScore($place)
                        ]
                    ];
    
                    $competitors[] = $competitor;
                }
    
                \Log::info('Busca concluída com sucesso', [
                    'total_results' => count($competitors)
                ]);
    
                return $competitors;
            }
    
            throw new \Exception('Erro na busca: ' . $response->body());
        } catch (\Exception $e) {
            \Log::error('Erro na busca Serper', [
                'erro' => $e->getMessage(),
                'query' => $query
            ]);
            throw $e;
        }
    }
    
    private function generateDescription($place)
    {
        $parts = [];
        
        if (!empty($place['address'])) {
            $parts[] = "Localizado em {$place['address']}";
        }
        
        if (!empty($place['rating'])) {
            $parts[] = "Avaliação de {$place['rating']}/5";
        }
        
        if (!empty($place['reviewsCount'])) {
            $parts[] = "com {$place['reviewsCount']} avaliações";
        }
        
        if (!empty($place['categories'])) {
            $parts[] = "Categorias: " . implode(', ', $place['categories']);
        }
        
        return implode('. ', $parts);
    }
    
    private function calculateRatingScore($place)
    {
        $rating = floatval($place['rating'] ?? 0);
        $reviews = intval($place['reviewsCount'] ?? 0);
        
        // Pontuação base pela avaliação (0-50 pontos)
        $ratingScore = ($rating / 5) * 50;
        
        // Bônus por quantidade de reviews (0-50 pontos)
        $reviewScore = min(($reviews / 100) * 50, 50);
        
        return round(($ratingScore + $reviewScore) / 2);
    }
    
    private function calculatePopularityScore($place)
    {
        $score = 0;
        
        // Pontos por reviews
        $reviews = intval($place['reviewsCount'] ?? 0);
        if ($reviews > 0) {
            $score += min(($reviews / 100) * 60, 60);
        }
        
        // Pontos por presença de fotos
        if (!empty($place['thumbnailUrl'])) {
            $score += 20;
        }
        
        // Pontos por categorias definidas
        if (!empty($place['categories'])) {
            $score += 20;
        }
        
        return min($score, 100);
    }
    
    private function calculateOnlinePresenceScore($place)
    {
        $score = 0;
        
        // Website (40 pontos)
        if (!empty($place['website'])) {
            $score += 40;
        }
        
        // Telefone (20 pontos)
        if (!empty($place['phoneNumber'])) {
            $score += 20;
        }
        
        // Horários de funcionamento (20 pontos)
        if (!empty($place['hours'])) {
            $score += 20;
        }
        
        // Foto do local (20 pontos)
        if (!empty($place['thumbnailUrl'])) {
            $score += 20;
        }
        
        return $score;
    }
    
    private function formatPlacesResults($data)
{
    $results = [];
    
    if (isset($data['places'])) {
        foreach ($data['places'] as $place) {
            $results[] = [
                'title' => $place['title'] ?? '',
                'location' => $place['address'] ?? '',
                'snippet' => $this->formatPlaceSnippet($place),
                'rating' => $place['rating'] ?? null,
                'reviews' => $place['reviewsCount'] ?? null,
                'phone' => $place['phone'] ?? '',
                'website' => $place['website'] ?? '',
                'image_url' => $place['thumbnailUrl'] ?? null, // Adiciona URL da imagem
                'coordinates' => [
                    'lat' => $place['latitude'] ?? null,
                    'lng' => $place['longitude'] ?? null
                ]
            ];
        }
    }
    
    return $results;
}
    
    private function formatPlaceSnippet($place)
    {
        $parts = [];
        
        if (!empty($place['address'])) {
            $parts[] = $place['address'];
        }
        
        if (!empty($place['rating'])) {
            $parts[] = "Avaliação: {$place['rating']}/5";
        }
        
        if (!empty($place['reviewsCount'])) {
            $parts[] = "{$place['reviewsCount']} avaliações";
        }
        
        return implode(' • ', $parts);
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

public function searchKeywords($business)
{
    try {
        // Construir query baseada nos dados do negócio
        $query = sprintf(
            '%s %s %s', 
            $business->segment,
            $business->city,
            $business->state
        );

        Log::info("Buscando palavras-chave para: " . $query);

        $response = Http::withHeaders([
            'X-API-KEY' => $this->apiKey,
            'Content-Type' => 'application/json'
        ])->post($this->apiEndpoint, [
            'q' => $query,
            'gl' => 'br',
            'hl' => 'pt-br',
            'type' => 'search'
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return $this->extractKeywords($data);
        }

        Log::error("Erro na busca de palavras-chave: " . $response->body());
        return [];

    } catch (\Exception $e) {
        Log::error("Erro ao buscar palavras-chave: " . $e->getMessage());
        return [];
    }
}

private function extractKeywords($data) 
{
    $keywords = [];
    
    // Extrair palavras-chave dos resultados orgânicos
    if (isset($data['organic'])) {
        foreach ($data['organic'] as $result) {
            if (isset($result['title'])) {
                $words = explode(' ', strtolower($result['title']));
                foreach ($words as $word) {
                    if (strlen($word) > 3) {
                        $keywords[$word] = ($keywords[$word] ?? 0) + 1;
                    }
                }
            }
        }
    }

    // Ordenar por frequência
    arsort($keywords);
    
    // Retornar os 10 termos mais frequentes
    return array_slice($keywords, 0, 10, true);
}

public function searchCompetitors($businessName, $city)
{
    try {
        \Log::info("Searching competitors for: {$businessName} in {$city}");
        
        // Construct the search query
        $query = "{$businessName} competitors {$city}";
        
        // Make the API request
        $response = Http::post($this->apiEndpoint, [
            'q' => $query,
            'api_key' => $this->apiKey,
            'search_type' => 'places'
        ]);

        if (!$response->successful()) {
            \Log::error('Serper API request failed: ' . $response->body());
            return [];
        }

        $data = $response->json();
        
        // Filter and format the results
        $competitors = [];
        if (isset($data['places'])) {
            foreach ($data['places'] as $place) {
                // Skip if it's the same business
                if (strtolower($place['title']) === strtolower($businessName)) {
                    continue;
                }

                $competitors[] = [
                    'title' => $place['title'] ?? '',
                    'snippet' => $this->generateDescription($place),
                    'rating' => $place['rating'] ?? null,
                    'reviews' => $place['reviewsCount'] ?? 0,
                    'address' => $place['address'] ?? '',
                    'website' => $place['website'] ?? '',
                    'phone' => $place['phone'] ?? '',
                    'categories' => $place['categories'] ?? [],
                ];
            }
        }

        // Sort competitors by rating (descending)
        usort($competitors, function($a, $b) {
            return ($b['rating'] ?? 0) <=> ($a['rating'] ?? 0);
        });

        // Return top 5 competitors
        return array_slice($competitors, 0, 5);

    } catch (\Exception $e) {
        \Log::error('Error searching competitors: ' . $e->getMessage());
        return [];
    }
}
}